<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    private const DEFAULT_PROFILE_IMG = 'images/default-avatar.png';
    private const MARITAL_STATUSES = ['single', 'married', 'widowed', 'divorced', 'separated'];
    private const GENDERS = ['male', 'female', 'other'];
    private const ACCOUNT_ROLES = ['employee', 'custodian', 'iac', 'division_head', 'bac'];
    private const ACCOUNT_ROLE_LABELS = [
        'employee' => 'Employee',
        'custodian' => 'Custodian',
        'iac' => 'IAC',
        'division_head' => 'Division Head',
        'bac' => 'BAC Officer',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $divisionFilter = $request->input('division');
        $sectionFilter = $request->input('section');
        $assignmentFilter = $request->input('assignment');

        $query = Employee::query()->with(['account', 'section.division', 'position']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");
            });
        }

        if ($divisionFilter) {
            $query->whereHas('section', function ($sectionQuery) use ($divisionFilter) {
                $sectionQuery->where('division_id', $divisionFilter);
            });
        }

        if ($sectionFilter) {
            $query->where('section_id', $sectionFilter);
        }

        if ($assignmentFilter === 'with') {
            $query->whereNotNull('account_id');
        } elseif ($assignmentFilter === 'without') {
            $query->whereNull('account_id');
        }

        $employees = $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(5)
            ->withQueryString();

        $divisions = Division::query()
            ->with(['sections:section_id,division_id,section_name'])
            ->orderBy('division_name')
            ->get();

        $sections = Section::query()
            ->when($divisionFilter, function ($builder) use ($divisionFilter) {
                $builder->where('division_id', $divisionFilter);
            })
            ->orderBy('section_name')
            ->get();

        $divisionEmployeeCounts = Employee::query()
            ->leftJoin('sections', 'employees.section_id', '=', 'sections.section_id')
            ->selectRaw('sections.division_id as division_id, count(*) as total')
            ->groupBy('sections.division_id')
            ->pluck('total', 'division_id');

        $stats = [
            'total' => Employee::count(),
            'withAccount' => Employee::whereNotNull('account_id')->count(),
            'withoutAccount' => Employee::whereNull('account_id')->count(),
        ];

        $recentEmployees = Employee::query()
            ->with(['section', 'account', 'position'])
            ->orderByDesc('employee_id')
            ->limit(5)
            ->get();

        $positions = Position::query()
            ->orderBy('position_title')
            ->get(['position_id', 'position_title']);

        $accounts = Account::query()
            ->with('employee:employee_id,account_id')
            ->orderBy('username')
            ->get(['account_id', 'username', 'role']);

        if ($request->ajax()) {
            $html = view('management.employees._table', [
                'employees' => $employees,
            ])->render();

            $pagination = $employees->hasPages() ? $employees->onEachSide(1)->links('vendor.pagination.procurement')->toHtml() : '';

            return response()->json([
                'html' => $html,
                'total' => $employees->total(),
                'pagination' => $pagination,
            ]);
        }

        return view('management.employees.index', [
            'employees' => $employees,
            'divisions' => $divisions,
            'sections' => $sections,
            'positions' => $positions,
            'accounts' => $accounts,
            'maritalStatuses' => self::MARITAL_STATUSES,
            'genders' => self::GENDERS,
            'stats' => $stats,
            'search' => $search,
            'divisionFilter' => $divisionFilter,
            'sectionFilter' => $sectionFilter,
            'assignmentFilter' => $assignmentFilter,
            'recentEmployees' => $recentEmployees,
            'divisionEmployeeCounts' => $divisionEmployeeCounts,
        ]);
    }

    public function printPdf(Request $request): \Illuminate\View\View
    {
        $search = trim((string) $request->input('search'));
        $divisionFilter = $request->input('division');
        $sectionFilter = $request->input('section');
        $assignmentFilter = $request->input('assignment');

        $query = Employee::query()->with(['account', 'section.division', 'position']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");
            });
        }

        if ($divisionFilter) {
            $query->whereHas('section', function ($sectionQuery) use ($divisionFilter) {
                $sectionQuery->where('division_id', $divisionFilter);
            });
        }

        if ($sectionFilter) {
            $query->where('section_id', $sectionFilter);
        }

        if ($assignmentFilter === 'with') {
            $query->whereNotNull('account_id');
        } elseif ($assignmentFilter === 'without') {
            $query->whereNull('account_id');
        }

        $employees = $query->orderBy('last_name')->orderBy('first_name')->get();

        return view('management.employees.print', [
            'employees' => $employees,
            'search' => $search,
            'divisionFilter' => $divisionFilter,
            'sectionFilter' => $sectionFilter,
            'assignmentFilter' => $assignmentFilter,
        ]);
    }

    public function exportExcel(Request $request): \Illuminate\Http\Response
    {
        $search = trim((string) $request->input('search'));
        $divisionFilter = $request->input('division');
        $sectionFilter = $request->input('section');
        $assignmentFilter = $request->input('assignment');

        $query = Employee::query()->with(['account', 'section.division', 'position']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");
            });
        }

        if ($divisionFilter) {
            $query->whereHas('section', function ($sectionQuery) use ($divisionFilter) {
                $sectionQuery->where('division_id', $divisionFilter);
            });
        }

        if ($sectionFilter) {
            $query->where('section_id', $sectionFilter);
        }

        if ($assignmentFilter === 'with') {
            $query->whereNotNull('account_id');
        } elseif ($assignmentFilter === 'without') {
            $query->whereNull('account_id');
        }

        $employees = $query->orderBy('last_name')->orderBy('first_name')->get();

        $html = view('management.employees.excel', ['employees' => $employees])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Employees-' . date('Y-m-d') . '.xls"');
    }

    public function create()
    {
        return view('management.employees.create', $this->formViewData());
    }

    public function store(Request $request)
    {
        $this->normalizeNullableFields($request);

        $validated = $request->validate($this->validationRules());
        $this->assertSectionMatchesDivision($validated['section_id'] ?? null, $validated['division_id'] ?? null);

        $payload = $this->preparePayload($validated);

        // If no employee_id supplied, generate one now (within transaction to reduce collision window)
        if (empty($payload['employee_id'])) {
            $payload['employee_id'] = $this->generateEmployeeId();
        }

    $createAccount = (bool) $request->input('create_account');
        $newAccountData = null;

        if ($createAccount) {
            // validate account inputs separately
            $accountValidator = Validator::make($request->all(), [
                'new_account_username' => ['required', 'string', 'max:255', Rule::unique('accounts', 'username')],
                'new_account_role' => ['required', Rule::in(self::ACCOUNT_ROLES)],
                'new_account_password' => ['nullable', 'string', 'min:6'],
            ]);

            $accountValidator->validate();

            $newAccountData = $accountValidator->validated();
        }

        // handle profile image upload (store in public disk => storage/app/public/profile_images)
        if ($request->hasFile('profile_img')) {
            $path = $request->file('profile_img')->store('profile_images', 'public');
            // store path used by views via asset('storage/....')
            $payload['profile_img'] = 'storage/' . $path;
        }

        if ($request->hasFile('signature')) {
            $payload['signature'] = $this->readSignatureFile($request->file('signature'));
        }

        DB::transaction(function () use ($payload, $createAccount, $newAccountData) {
            if ($createAccount && $newAccountData) {
                $accountId = $this->generateAccountId();
                $password = $newAccountData['new_account_password'] ?: 'password';
                $acc = Account::create([
                    'account_id' => $accountId,
                    'username' => $newAccountData['new_account_username'],
                    'password' => Hash::make($password),
                    'role' => $newAccountData['new_account_role'],
                ]);

                $payload['account_id'] = $acc->account_id;
            }

            Employee::create($payload);
        });

        return redirect()
            ->route('employees.index')
            ->with('status', 'Employee record created successfully.');
    }

    public function edit(Employee $employee)
    {
        return view('management.employees.edit', $this->formViewData($employee));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->normalizeNullableFields($request);

        $validated = $request->validate($this->validationRules($employee));
        $this->assertSectionMatchesDivision($validated['section_id'] ?? null, $validated['division_id'] ?? null);

        $payload = $this->preparePayload($validated, $employee);

        $createAccount = (bool) $request->input('create_account');
        $newAccountData = null;

        if ($createAccount) {
            $accountValidator = Validator::make($request->all(), [
                'new_account_username' => ['required', 'string', 'max:255', Rule::unique('accounts', 'username')],
                'new_account_role' => ['required', Rule::in(self::ACCOUNT_ROLES)],
                'new_account_password' => ['nullable', 'string', 'min:6'],
            ]);

            $accountValidator->validate();

            $newAccountData = $accountValidator->validated();
        }

        // handle profile image upload for update
        if ($request->hasFile('profile_img')) {
            $path = $request->file('profile_img')->store('profile_images', 'public');
            $payload['profile_img'] = 'storage/' . $path;
        }

        if ($request->hasFile('signature')) {
            $payload['signature'] = $this->readSignatureFile($request->file('signature'));
        }

        DB::transaction(function () use ($employee, $payload, $createAccount, $newAccountData) {
            // If requested and the employee currently has no account, create one and attach it.
            if ($createAccount && $newAccountData && !$employee->account_id) {
                $accountId = $this->generateAccountId();
                $password = $newAccountData['new_account_password'] ?: 'password';
                $acc = Account::create([
                    'account_id' => $accountId,
                    'username' => $newAccountData['new_account_username'],
                    'password' => Hash::make($password),
                    'role' => $newAccountData['new_account_role'],
                ]);

                $payload['account_id'] = $acc->account_id;
            }

            $employee->update($payload);
        });

        return redirect()
            ->route('employees.index')
            ->with('status', 'Employee record updated successfully.');
    }

    public function updateModal(Request $request, Employee $employee)
    {
        $this->normalizeNullableFields($request);

        $validated = $request->validate($this->validationRules($employee));
        $this->assertSectionMatchesDivision($validated['section_id'] ?? null, $validated['division_id'] ?? null);

        $payload = $this->preparePayload($validated, $employee);

        $createAccount = (bool) $request->input('create_account');
        $newAccountData = null;

        if ($createAccount) {
            if ($employee->account_id) {
                throw ValidationException::withMessages([
                    'create_account' => 'This employee already has a linked account.',
                ]);
            }

            if (!empty($validated['account_id'])) {
                throw ValidationException::withMessages([
                    'account_id' => 'Choose either linking an existing account or creating a new account, not both.',
                ]);
            }

            $newAccountData = Validator::make($request->all(), [
                'new_account_username' => ['required', 'string', 'max:255', Rule::unique('accounts', 'username')],
                'new_account_role' => ['required', Rule::in(self::ACCOUNT_ROLES)],
                'new_account_password' => ['nullable', 'string', 'min:6'],
            ])->validate();
        }

        if ($request->hasFile('profile_img')) {
            $path = $request->file('profile_img')->store('profile_images', 'public');
            $payload['profile_img'] = 'storage/' . $path;
        }

        if ($request->hasFile('signature')) {
            $payload['signature'] = $this->readSignatureFile($request->file('signature'));
        }

        DB::transaction(function () use ($employee, &$payload, $createAccount, $newAccountData) {
            if ($createAccount && $newAccountData && !$employee->account_id) {
                $accountId = $this->generateAccountId();
                $password = $newAccountData['new_account_password'] ?: 'password';

                $account = Account::create([
                    'account_id' => $accountId,
                    'username' => $newAccountData['new_account_username'],
                    'password' => Hash::make($password),
                    'role' => $newAccountData['new_account_role'],
                ]);

                $payload['account_id'] = $account->account_id;
            }

            $employee->update($payload);
        });

        $employee->load(['section', 'account']);

        $fullName = trim((string) $employee->last_name);
        $nameTail = trim(implode(' ', array_filter([
            $employee->first_name,
            $employee->middle_name,
            $employee->suffix,
        ])));
        if ($nameTail !== '') {
            $fullName = $fullName !== '' ? ($fullName . ', ' . $nameTail) : $nameTail;
        }

        return response()->json([
            'message' => 'Employee updated successfully.',
            'employee' => [
                'id' => $employee->id,
                'full_name' => $fullName,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'suffix' => $employee->suffix,
                'employee_id' => $employee->employee_id,
                'date_of_birth' => $employee->date_of_birth,
                'gender' => $employee->gender,
                'marital_status' => $employee->marital_status,
                'position_id' => $employee->position_id,
                'section_id' => $employee->section_id,
                'division_id' => $employee->section?->division_id,
                'account_id' => $employee->account_id,
                'email' => $employee->email,
                'contact_no' => $employee->contact_no,
                'profile_img' => $employee->profile_img,
            ],
        ]);
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            $employee->delete();
        });

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Employee record removed.',
            ]);
        }

        return redirect()
            ->route('employees.index')
            ->with('status', 'Employee record removed.');
    }

    private function formViewData(?Employee $employee = null): array
    {
        $divisions = Division::query()
            ->with(['sections:section_id,division_id,section_name'])
            ->orderBy('division_name')
            ->get();

        $positions = Position::query()
            ->orderBy('position_title')
            ->get();

        $accounts = $this->availableAccounts($employee);

        return [
            'employee' => $employee,
            'divisions' => $divisions,
            'positions' => $positions,
            'accounts' => $accounts,
            'accountRoles' => $this->resolveAccountRoleOptions(),
            'maritalStatuses' => self::MARITAL_STATUSES,
            'genders' => self::GENDERS,
            'selectedDivision' => $employee?->section?->division_id,
        ];
    }

    private function resolveAccountRoleOptions(): array
    {
        $dbRoles = Account::query()
            ->select('role')
            ->distinct()
            ->pluck('role')
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->filter();

        $roles = collect(self::ACCOUNT_ROLES)
            ->merge($dbRoles)
            ->unique()
            ->values();

        return $roles->map(function (string $role): array {
            return [
                'value' => $role,
                'label' => self::ACCOUNT_ROLE_LABELS[$role] ?? ucwords(str_replace('_', ' ', $role)),
            ];
        })->all();
    }

    private function availableAccounts(?Employee $employee = null)
    {
        $query = Account::query()->orderBy('username');

        if ($employee) {
            $query->where(function ($builder) use ($employee) {
                $builder->whereDoesntHave('employee')
                    ->orWhereHas('employee', function ($employeeQuery) use ($employee) {
                        $employeeQuery->where('employee_id', $employee->employee_id);
                    });
            });
        } else {
            $query->whereDoesntHave('employee');
        }

        return $query->get();
    }

    private function validationRules(?Employee $employee = null): array
    {
        $employeeIdRule = Rule::unique('employees', 'employee_id');
        $emailRule = Rule::unique('employees', 'email');
        $accountRule = Rule::unique('employees', 'account_id')
            ->where(function ($query) {
                $query->whereNotNull('account_id');
            });

        if ($employee) {
            $employeeIdRule->ignore($employee->employee_id, 'employee_id');
            $emailRule->ignore($employee->employee_id, 'employee_id');
            $accountRule->ignore($employee->employee_id, 'employee_id');
        }

        $employeeIdRules = [
            'string',
            'max:255',
            $employeeIdRule,
        ];

        // When creating (no $employee) allow employee_id to be omitted so we can auto-generate it.
        if ($employee === null) {
            array_unshift($employeeIdRules, 'nullable');
        } else {
            array_unshift($employeeIdRules, 'required');
        }

        return [
            'employee_id' => $employeeIdRules,
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'marital_status' => ['required', Rule::in(self::MARITAL_STATUSES)],
            'gender' => ['required', Rule::in(self::GENDERS)],
            'contact_no' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                $emailRule,
            ],
            'account_id' => [
                'nullable',
                'integer',
                'exists:accounts,account_id',
                $accountRule,
            ],
            'position_id' => ['nullable', 'integer', 'exists:positions,position_id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,section_id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,division_id'],
            'profile_img' => ['nullable', 'image', 'max:2048'],
            'signature' => ['nullable', 'file', 'mimes:png', 'max:2048'],
        ];
    }

    private function readSignatureFile(UploadedFile $file): string
    {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw ValidationException::withMessages([
                'signature' => 'Unable to read the uploaded signature file. Please try again.',
            ]);
        }

        return $contents;
    }

    private function preparePayload(array $validated, ?Employee $employee = null): array
    {
        $payload = $validated;

        unset($payload['division_id']);

        foreach (['employee_id', 'first_name', 'middle_name', 'last_name', 'suffix', 'contact_no', 'email'] as $field) {
            if (isset($payload[$field]) && is_string($payload[$field])) {
                $payload[$field] = trim($payload[$field]);
            }
        }

        $payload['account_id'] = $payload['account_id'] ?? null;
        $payload['position_id'] = $payload['position_id'] ?? null;
        $payload['section_id'] = $payload['section_id'] ?? null;
        $payload['contact_no'] = $payload['contact_no'] ?: null;
        $payload['email'] = $payload['email'] ?: null;
        $payload['middle_name'] = $payload['middle_name'] ?: null;
        $payload['suffix'] = $payload['suffix'] ?: null;

        if (!isset($payload['profile_img']) || !$payload['profile_img']) {
            $payload['profile_img'] = $employee?->profile_img ?? self::DEFAULT_PROFILE_IMG;
        }

        // Ensure employee_id exists for creation (should be generated before create() when needed)
        if (empty($payload['employee_id']) && $employee === null) {
            $payload['employee_id'] = $this->generateEmployeeId();
        }

        return $payload;
    }

    /**
     * Generate the next employee id using existing employee_id suffix numeric part.
     * Format: EMP-<number> (continues numeric sequence)
     */
    private function generateEmployeeId(): string
    {
        // Find maximum numeric suffix from existing employee_id values like 'EMP-1001'
        $max = (int) DB::table('employees')
            ->where('employee_id', 'like', 'EMP-%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(employee_id, '-', -1) AS UNSIGNED)) as max")
            ->value('max');

        $next = $max > 0 ? $max + 1 : 1001;

        return 'EMP-' . $next;
    }

    /**
     * Generate next account id (simple max+1 strategy).
     */
    private function generateAccountId(): int
    {
        $max = (int) DB::table('accounts')
            ->selectRaw('MAX(account_id) as max')
            ->value('max');

        return $max > 0 ? $max + 1 : 1001;
    }

    private function normalizeNullableFields(Request $request): void
    {
        $request->merge([
            'account_id' => $this->toNullableInt($request->input('account_id')),
            'position_id' => $this->toNullableInt($request->input('position_id')),
            'section_id' => $this->toNullableInt($request->input('section_id')),
            'division_id' => $this->toNullableInt($request->input('division_id')),
        ]);
    }

    private function toNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function assertSectionMatchesDivision(?int $sectionId, ?int $divisionId): void
    {
        if ($sectionId === null || $divisionId === null) {
            return;
        }

        $match = Section::query()
            ->where('section_id', $sectionId)
            ->where('division_id', $divisionId)
            ->exists();

        if (!$match) {
            throw ValidationException::withMessages([
                'section_id' => 'Selected section does not belong to the chosen division.',
            ]);
        }
    }
}
