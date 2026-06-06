<?php

namespace App\Http\Controllers\Inscription;

use App\Actions\Inscription\ProvisionSchoolUserAction;
use App\Http\Controllers\Controller;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

/**
 * إدارة شاملة لأولياء الأمور:
 *  - قائمة الأولياء مع أبنائهم وبحث وفلاتر.
 *  - إضافة/تعديل/حذف ولي وربطه بتلاميذ (بحث AJAX).
 *  - ميزة ذكية: اكتشاف الإخوة المحتملين ودمج حسابات الأولياء المؤقتة
 *    (الاستيراد ينشئ ولياً مؤقتاً لكل تلميذ، فالإخوة يتكرر أولياؤهم).
 */
class ParentController extends Controller
{
    private const SUGGESTIONS_LIMIT = 20;
    private const SUGGESTION_GROUP_MAX = 8;

    public function __construct(private ProvisionSchoolUserAction $provisionSchoolUserAction)
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $filter = (string) request('filter'); // multi | synthetic | ''

        $parents = MyParent::query()
            ->belongingToSchool($schoolId)
            ->with([
                'user:id,email',
                'students:id,parent_id,section_id,prenom,nom,national_id',
                'students.section:id,classroom_id,name_section',
                'students.section.classroom:id,grade_id,name_class',
                'students.section.classroom.schoolgrade:id,name_grade',
            ])
            ->withCount('students')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($parentQuery) use ($search) {
                    $parentQuery->where('prenomwali->ar', 'like', '%' . $search . '%')
                        ->orWhere('prenomwali->fr', 'like', '%' . $search . '%')
                        ->orWhere('nomwali->ar', 'like', '%' . $search . '%')
                        ->orWhere('nomwali->fr', 'like', '%' . $search . '%')
                        ->orWhere('numtelephonewali', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'))
                        ->orWhereHas('students', function ($studentQuery) use ($search) {
                            $studentQuery->where('prenom->ar', 'like', '%' . $search . '%')
                                ->orWhere('nom->ar', 'like', '%' . $search . '%')
                                ->orWhere('national_id', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($filter === 'multi', fn ($query) => $query->has('students', '>=', 2))
            ->when($filter === 'synthetic', function ($query) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%@import.local'));
            })
            ->orderByDesc('students_count')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => MyParent::belongingToSchool($schoolId)->count(),
            'multi' => MyParent::belongingToSchool($schoolId)->has('students', '>=', 2)->count(),
            'synthetic' => MyParent::belongingToSchool($schoolId)
                ->whereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%@import.local'))
                ->count(),
        ];

        $suggestions = request('tab') === 'suggestions'
            ? $this->siblingSuggestions($schoolId)
            : [];

        return view('admin.parents', [
            'Parents' => $parents,
            'stats' => $stats,
            'suggestions' => $suggestions,
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'أولياء الأمور'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prenomar' => ['required', 'string', 'max:100'],
            'nomar' => ['required', 'string', 'max:100'],
            'prenomfr' => ['nullable', 'string', 'max:100'],
            'nomfr' => ['nullable', 'string', 'max:100'],
            'relation' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'address' => ['nullable', 'string', 'max:200'],
            'wilaya' => ['nullable', 'string', 'max:100'],
            'dayra' => ['nullable', 'string', 'max:100'],
            'baladia' => ['nullable', 'string', 'max:100'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer'],
        ]);

        $schoolId = $this->currentSchoolId();
        $phone = preg_replace('/\D+/', '', $data['phone']);
        $email = $data['email'] ?? null;

        if (!$email) {
            // بريد توليفي للأولياء بلا بريد إلكتروني (ليس من الاستيراد فلا يُعد "مؤقتاً")
            $email = sprintf('guardian_%s_%s@manual.local', $phone, Str::lower(Str::random(4)));
        }

        // رابط تهيئة كلمة المرور فقط عند بريد حقيقي وبدون كلمة مرور يدوية
        $dispatchOnboarding = empty($data['password']) && !empty($data['email']);

        try {
            DB::transaction(function () use ($data, $schoolId, $phone, $email, $dispatchOnboarding) {
                $user = $this->provisionSchoolUserAction->execute(
                    $this->localizedName($data['prenomar'], $data['prenomfr'] ?? null),
                    $email,
                    $schoolId,
                    'guardian',
                    $data['password'] ?? null,
                    $dispatchOnboarding
                );

                $parent = MyParent::create([
                    'prenomwali' => $this->localizedName($data['prenomar'], $data['prenomfr'] ?? null),
                    'nomwali' => $this->localizedName($data['nomar'], $data['nomfr'] ?? null),
                    'relationetudiant' => $data['relation'],
                    'adressewali' => $data['address'] ?? 'غير محدد',
                    'wilayawali' => $data['wilaya'] ?? 'غير محدد',
                    'dayrawali' => $data['dayra'] ?? 'غير محدد',
                    'baladiawali' => $data['baladia'] ?? 'غير محدد',
                    'numtelephonewali' => $phone,
                    'user_id' => $user->id,
                ]);

                foreach ($data['student_ids'] ?? [] as $studentId) {
                    $this->attachStudent($parent, (int) $studentId, $schoolId);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success('تمت إضافة ولي الأمر بنجاح.');

        return redirect()->route('Parents.index');
    }

    public function update(Request $request, $id)
    {
        $schoolId = $this->currentSchoolId();

        $parent = MyParent::query()
            ->belongingToSchool($schoolId)
            ->with('user')
            ->findOrFail($id);

        $data = $request->validate([
            'prenomar' => ['required', 'string', 'max:100'],
            'nomar' => ['required', 'string', 'max:100'],
            'prenomfr' => ['nullable', 'string', 'max:100'],
            'nomfr' => ['nullable', 'string', 'max:100'],
            'relation' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email,' . $parent->user_id],
            'password' => ['nullable', 'string', 'min:6'],
            'address' => ['nullable', 'string', 'max:200'],
            'wilaya' => ['nullable', 'string', 'max:100'],
            'dayra' => ['nullable', 'string', 'max:100'],
            'baladia' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            DB::transaction(function () use ($parent, $data) {
                $parent->update([
                    'prenomwali' => $this->localizedName($data['prenomar'], $data['prenomfr'] ?? null),
                    'nomwali' => $this->localizedName($data['nomar'], $data['nomfr'] ?? null),
                    'relationetudiant' => $data['relation'],
                    'adressewali' => $data['address'] ?? $parent->adressewali,
                    'wilayawali' => $data['wilaya'] ?? $parent->wilayawali,
                    'dayrawali' => $data['dayra'] ?? $parent->dayrawali,
                    'baladiawali' => $data['baladia'] ?? $parent->baladiawali,
                    'numtelephonewali' => preg_replace('/\D+/', '', $data['phone']),
                ]);

                if ($parent->user) {
                    $userChanges = [
                        'name' => $this->localizedName($data['prenomar'], $data['prenomfr'] ?? null),
                    ];

                    if (!empty($data['email'])) {
                        $userChanges['email'] = $data['email'];
                    }

                    if (!empty($data['password'])) {
                        $userChanges['password'] = Hash::make($data['password']);
                        $userChanges['must_change_password'] = false;
                    }

                    $parent->user->update($userChanges);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success('تم تحديث بيانات ولي الأمر.');

        return redirect()->route('Parents.index');
    }

    public function destroy($id)
    {
        $schoolId = $this->currentSchoolId();

        $parent = MyParent::query()
            ->belongingToSchool($schoolId)
            ->withCount('students')
            ->with('user')
            ->findOrFail($id);

        if ($parent->students_count > 0) {
            return back()->withErrors([
                'error' => 'لا يمكن حذف ولي مرتبط بتلاميذ — انقل أبناءه إلى ولي آخر أولاً.',
            ]);
        }

        DB::transaction(function () use ($parent) {
            $user = $parent->user;
            $parent->delete();
            // حذف حساب الدخول التابع للولي (لا يملك أي أبناء)
            $user?->delete();
        });

        toastr()->error('تم حذف ولي الأمر.');

        return redirect()->route('Parents.index');
    }

    /**
     * بحث AJAX عن تلاميذ لربطهم بولي (الاسم / رقم التعريف / الهاتف).
     */
    public function searchStudents(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $search = trim((string) $request->query('q'));

        if (mb_strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $students = StudentInfo::query()
            ->forSchool($schoolId)
            ->with([
                'parent:id,prenomwali,nomwali,user_id',
                'parent.user:id,email',
                'section:id,classroom_id,name_section',
                'section.classroom:id,grade_id,name_class',
                'section.classroom.schoolgrade:id,name_grade',
            ])
            ->where(function ($query) use ($search) {
                $query->where('prenom->ar', 'like', '%' . $search . '%')
                    ->orWhere('nom->ar', 'like', '%' . $search . '%')
                    ->orWhere('prenom->fr', 'like', '%' . $search . '%')
                    ->orWhere('nom->fr', 'like', '%' . $search . '%')
                    ->orWhere('national_id', 'like', '%' . $search . '%')
                    ->orWhere('numtelephone', 'like', '%' . $search . '%');
            })
            ->limit(15)
            ->get(['id', 'parent_id', 'section_id', 'prenom', 'nom', 'national_id']);

        return response()->json([
            'results' => $students->map(fn (StudentInfo $student) => [
                'id' => $student->id,
                'name' => trim($student->prenom . ' ' . $student->nom),
                'national_id' => $student->national_id,
                'section' => implode(' / ', array_filter([
                    optional(optional(optional($student->section)->classroom)->schoolgrade)->name_grade,
                    optional(optional($student->section)->classroom)->name_class,
                    optional($student->section)->name_section,
                ])),
                'guardian' => $student->parent
                    ? trim($student->parent->prenomwali . ' ' . $student->parent->nomwali)
                    : null,
                'guardian_synthetic' => $this->isSyntheticGuardian($student->parent),
            ])->values(),
        ]);
    }

    /**
     * ربط تلميذ بولي (نقله من وليّه الحالي مع تنظيف الحسابات المؤقتة اليتيمة).
     */
    public function linkStudent(Request $request, $id)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
        ]);

        $schoolId = $this->currentSchoolId();

        $parent = MyParent::query()
            ->belongingToSchool($schoolId)->findOrFail($id);

        try {
            DB::transaction(function () use ($parent, $data, $schoolId) {
                $this->attachStudent($parent, (int) $data['student_id'], $schoolId);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success('تم ربط التلميذ بولي الأمر.');

        return redirect()->route('Parents.index', array_filter([
            'q' => $request->input('return_q'),
        ]));
    }

    /**
     * دمج أولياء مكررين: نقل التلاميذ المحددين إلى الولي المستهدف
     * ثم حذف حسابات الأولياء المؤقتة التي أصبحت بلا أبناء.
     */
    public function merge(Request $request)
    {
        $data = $request->validate([
            'target_parent_id' => ['required', 'integer'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer'],
        ]);

        $schoolId = $this->currentSchoolId();

        $target = MyParent::query()
            ->belongingToSchool($schoolId)->findOrFail((int) $data['target_parent_id']);

        $moved = 0;

        try {
            DB::transaction(function () use ($target, $data, $schoolId, &$moved) {
                foreach ($data['student_ids'] as $studentId) {
                    $moved += $this->attachStudent($target, (int) $studentId, $schoolId) ? 1 : 0;
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(sprintf('تم الدمج: %d تلميذ أصبحوا تحت ولي واحد، وحُذفت الحسابات المؤقتة الفارغة.', $moved));

        return redirect()->route('Parents.index', ['tab' => 'suggestions']);
    }

    /**
     * نقل تلميذ إلى ولي، وحذف الولي السابق إن كان حساباً مؤقتاً بلا أبناء.
     *
     * @return bool هل حدث نقل فعلي
     */
    private function attachStudent(MyParent $parent, int $studentId, ?int $schoolId): bool
    {
        $student = StudentInfo::query()
            ->forSchool($schoolId)
            ->with('parent.user')
            ->findOrFail($studentId);

        if ((int) $student->parent_id === (int) $parent->id) {
            return false;
        }

        $previous = $student->parent;

        $student->update(['parent_id' => $parent->id]);

        if ($previous
            && $this->isSyntheticGuardian($previous)
            && $previous->students()->count() === 0
        ) {
            $previousUser = $previous->user;
            $previous->delete();
            $previousUser?->delete();
        }

        return true;
    }

    /**
     * اقتراحات الإخوة المحتملين: تلاميذ يتشاركون اللقب العربي نفسه
     * لكن أولياؤهم مختلفون وبينهم حساب مؤقت واحد على الأقل من الاستيراد.
     *
     * @return array<int, array{family: string, students: array}>
     */
    private function siblingSuggestions(?int $schoolId): array
    {
        $students = StudentInfo::query()
            ->forSchool($schoolId)
            ->with([
                'parent:id,prenomwali,nomwali,relationetudiant,numtelephonewali,user_id',
                'parent.user:id,email',
                'section:id,classroom_id,name_section',
                'section.classroom:id,grade_id,name_class',
                'section.classroom.schoolgrade:id,name_grade',
            ])
            ->get(['id', 'parent_id', 'section_id', 'prenom', 'nom', 'national_id', 'lieunaissance']);

        $groups = [];
        foreach ($students as $student) {
            $lastName = $student->getTranslation('nom', 'ar', false) ?: (string) $student->nom;
            $key = $this->normalizeArabic($lastName);
            if ($key === '') {
                continue;
            }
            $groups[$key][] = $student;
        }

        $suggestions = [];

        foreach ($groups as $members) {
            $count = count($members);
            if ($count < 2 || $count > self::SUGGESTION_GROUP_MAX) {
                continue;
            }

            $parentIds = collect($members)->pluck('parent_id')->filter()->unique();
            if ($parentIds->count() < 2) {
                continue; // مرتبطون بولي واحد أصلاً
            }

            $hasSynthetic = collect($members)
                ->contains(fn (StudentInfo $student) => $this->isSyntheticGuardian($student->parent));
            if (!$hasSynthetic) {
                continue;
            }

            $suggestions[] = [
                'family' => $members[0]->getTranslation('nom', 'ar', false) ?: (string) $members[0]->nom,
                'students' => $members,
            ];

            if (count($suggestions) >= self::SUGGESTIONS_LIMIT) {
                break;
            }
        }

        // الأصغر حجماً أولاً (الأكثر احتمالاً أن يكونوا إخوة فعلاً)
        usort($suggestions, fn ($a, $b) => count($a['students']) <=> count($b['students']));

        return $suggestions;
    }

    private function isSyntheticGuardian(?MyParent $parent): bool
    {
        $email = (string) optional(optional($parent)->user)->email;

        return $email !== '' && Str::endsWith($email, '@import.local');
    }

    private function localizedName(string $arabic, ?string $latin): array
    {
        $latin = trim((string) $latin) ?: $arabic;

        return ['ar' => $arabic, 'fr' => $latin, 'en' => $latin];
    }

    private function normalizeArabic(string $value): string
    {
        $normalized = Str::lower(trim($value));
        $normalized = (string) preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $normalized);
        $normalized = (string) preg_replace('/[أإآ]/u', 'ا', $normalized);
        $normalized = (string) preg_replace('/ة$/u', 'ه', $normalized);
        $normalized = (string) preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized);

        return $normalized;
    }
}
