<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountsController extends Controller
{
    /** الأدوار القابلة للإسناد من صندوق الحسابات مع تسمياتها. */
    private const ASSIGNABLE_ROLES = [
        'admin' => 'مدير',
        'supervisor' => 'ناظر',
        'teacher' => 'أستاذ',
        'accountant' => 'محاسب',
        'employee' => 'موظف',
        'guardian' => 'ولي أمر',
        'student' => 'تلميذ',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $search = trim((string) $request->query('q'));
        $roleFilter = trim((string) $request->query('role'));

        $users = User::query()
            ->with(['roles:id,name', 'school:id,name_school'])
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name->ar', 'like', '%' . $search . '%')
                    ->orWhere('name->fr', 'like', '%' . $search . '%');
            }))
            ->when($roleFilter !== '', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $roleFilter)))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.accounts.index', [
            'users' => $users,
            'roles' => self::ASSIGNABLE_ROLES,
            'currentUserId' => Auth::id(),
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => trans('accounts.title')],
            ],
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->assertSameSchool($user);

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => true,
        ]);

        toastr()->success(trans('accounts.password_reset_done'));

        return redirect()->route('accounts.index');
    }

    public function updateRoles(Request $request, User $user)
    {
        $this->assertSameSchool($user);

        $validated = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'in:' . implode(',', array_keys(self::ASSIGNABLE_ROLES))],
        ]);

        $roleNames = $validated['roles'] ?? [];
        foreach ($roleNames as $name) {
            Role::firstOrCreate(['name' => $name]);
        }

        $user->syncRoles($roleNames);

        toastr()->success(trans('accounts.roles_updated'));

        return redirect()->route('accounts.index');
    }

    public function destroy(User $user)
    {
        $this->assertSameSchool($user);

        if ((int) $user->id === (int) Auth::id()) {
            return back()->withErrors(['error' => trans('accounts.cannot_delete_self')]);
        }

        $user->delete();

        toastr()->error(trans('accounts.account_deleted'));

        return redirect()->route('accounts.index');
    }

    /**
     * المسؤول المرتبط بمدرسة لا يدير إلا حسابات مدرسته.
     */
    private function assertSameSchool(User $user): void
    {
        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $user->school_id !== (int) $schoolId) {
            abort(404);
        }
    }
}
