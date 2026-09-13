<?php

namespace App\Http\Controllers\HR;

use App\Actions\Inscription\ProvisionSchoolUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\HR\Employee;
use App\Models\Role;
use App\Models\School\School;
use Illuminate\Support\Facades\DB;
use Throwable;

class EmployeeController extends Controller
{
    public function __construct(private ProvisionSchoolUserAction $provisionSchoolUserAction)
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $this->authorize('viewAny', Employee::class);

        $branchId = $this->branchFilterId();
        $search = trim((string) request('q'));

        $data['Employees'] = Employee::query()
            ->forSchool($branchId)
            ->with(['user:id,email', 'school:id,name_school'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('job_title', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $data['schools'] = $this->branchOptions();
        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('hr.employees')],
        ];

        return view('admin.hr.employees.index', $data);
    }

    public function printList()
    {
        $this->authorize('viewAny', Employee::class);

        $branchId = $this->branchFilterId();
        $search = trim((string) request('q'));

        $employees = Employee::query()
            ->forSchool($branchId)
            ->with('school:id,name_school')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('job_title', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->limit(3000)
            ->get();

        $schoolName = $branchId
            ? (string) (optional(School::find($branchId))->name_school ?: trans('print.system_name'))
            : trans('print.system_name');

        $data = ['employees' => $employees, 'schoolName' => $schoolName];

        if (request('format') === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $data['isPdf'] = true;
            $data['pdfUrl'] = null;

            return \Barryvdh\DomPDF\Facade\Pdf::setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true])
                ->loadView('admin.hr.employees.print_list', $data)
                ->setPaper('a4', 'portrait')
                ->download('employees.pdf');
        }

        $data['isPdf'] = false;
        $data['pdfUrl'] = route('employees.print', array_merge(
            request()->only('branch_id', 'q'),
            ['format' => 'pdf']
        ));

        return view('admin.hr.employees.print_list', $data);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $this->authorize('create', Employee::class);
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId() ?: ($validated['school_id'] ?? null);
        if (!$schoolId) {
            return back()->withErrors(['school_id' => trans('hr.school_required')])->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $schoolId, $request) {
                $userId = null;

                if ($request->boolean('create_account') && !empty($validated['email'])) {
                    Role::firstOrCreate(['name' => 'employee']);
                    $user = $this->provisionSchoolUserAction->execute(
                        ['ar' => $validated['name'], 'fr' => $validated['name'], 'en' => $validated['name']],
                        $validated['email'],
                        (int) $schoolId,
                        'employee'
                    );
                    $userId = $user->id;
                }

                Employee::query()->create([
                    'school_id' => (int) $schoolId,
                    'user_id' => $userId,
                    'name' => $validated['name'],
                    'job_title' => $validated['job_title'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'joining_date' => $validated['joining_date'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'is_active' => $request->boolean('is_active', true),
                ]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('employees.index');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->authorize('update', $employee);
        $validated = $request->validated();

        $employee->update([
            'name' => $validated['name'],
            'job_title' => $validated['job_title'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        toastr()->success(trans('messages.Update'));

        return redirect()->route('employees.index');
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        toastr()->error(trans('messages.delete'));

        return redirect()->route('employees.index');
    }
}
