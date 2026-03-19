@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-6 animate-card">
    <section class="relative overflow-hidden rounded-[26px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="pointer-events-none absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Employee Registry
                </span>
                <h1 class="text-3xl font-extrabold tracking-tight">Edit Employee</h1>
                <p class="text-sm text-white/75">Update employee details, assignment records, and system access configuration with guided steps.</p>
            </div>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                <i class="fas fa-arrow-left"></i>
                Back to Employee List
            </a>
        </div>
    </section>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm">
            <p class="font-bold mb-2 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Please fix the errors below and try again:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-6" enctype="multipart/form-data" id="editEmployeeForm">
        @csrf
        @method('PUT')

        {{-- Step Navigation --}}
        <div class="rounded-2xl border border-emerald-950/8 bg-white/95 p-4 shadow-[0_16px_35px_-25px_rgba(15,23,42,0.45)]">
            <nav class="-mb-px flex flex-wrap gap-4 border-b border-gray-200" aria-label="Tabs">
                <button type="button" data-step="1" class="step-tab group inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold border-[#1a3a2d] text-[#1a3a2d]">
                    <span class="step-indicator rounded-full w-6 h-6 flex items-center justify-center bg-[#1a3a2d] text-white">1</span>
                    Personal Information
                </button>
                <button type="button" data-step="2" class="step-tab group inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700">
                     <span class="step-indicator rounded-full w-6 h-6 flex items-center justify-center bg-gray-200 text-gray-600">2</span>
                    Contact, Assignment & Access
                </button>
            </nav>
            <p class="mt-3 text-xs font-medium uppercase tracking-[0.14em] text-gray-500">Update the fields below to save employee changes.</p>
        </div>

        {{-- Step 1: Personal Information --}}
        <div class="form-step" data-step="1">
            <section class="rounded-2xl border border-emerald-950/8 bg-white p-6 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.45)] space-y-6 sm:p-7">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-[#1a3a2d]">Personal Details</h2>
                    <p class="mt-1 text-sm text-gray-500">Review and update legal identity and profile information.</p>
                </div>
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Profile Picture Uploader --}}
                    <div class="md:col-span-1">
                        <label class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Profile Picture</label>
                        <div class="mt-1">
                            <input id="profile_img" name="profile_img" type="file" accept="image/*" class="hidden" />
                            <label for="profile_img" class="group cursor-pointer flex flex-col items-center justify-center w-full h-52 border-2 border-dashed border-gray-300 rounded-xl bg-[#f7faf8] hover:border-emerald-500 hover:bg-emerald-50 transition">
                                <img id="image_preview" src="{{ asset($employee->profile_img ?? '') }}" alt="Image Preview" class="{{ $employee->profile_img ? '' : 'hidden' }} w-full h-full object-cover rounded-xl"/>
                                <div id="upload_prompt" class="{{ $employee->profile_img ? 'hidden' : '' }} flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-camera text-4xl text-gray-400 group-hover:text-emerald-600"></i>
                                    <p class="mt-2 text-sm">Click to upload</p>
                                    <p class="text-xs">PNG, JPG up to 2MB</p>
                                </div>
                            </label>
                        </div>
                        @error('profile_img')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    {{-- Name Fields --}}
                    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="first_name" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">First Name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $employee->first_name) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" />
                            @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="middle_name" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Middle Name</label>
                            <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $employee->middle_name) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" />
                            @error('middle_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                         <div>
                            <label for="last_name" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Last Name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $employee->last_name) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" />
                            @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="suffix" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Suffix</label>
                            <input id="suffix" name="suffix" type="text" value="{{ old('suffix', $employee->suffix) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="e.g. Jr., III" />
                            @error('suffix')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                         <div>
                            <label for="date_of_birth" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Date of Birth</label>
                            <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $employee->date_of_birth) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" />
                            @error('date_of_birth')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                 <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                     <div>
                        <label for="employee_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Employee ID</label>
                                <input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id', $employee->employee_id) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-gray-100 px-4 text-gray-500 cursor-not-allowed shadow-sm focus:border-gray-200 focus:ring-0" readonly aria-readonly="true" />
                        @error('employee_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="gender" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Gender</label>
                        <select id="gender" name="gender" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                            <option value="">Select gender</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender }}" @selected(old('gender', $employee->gender) === $gender)>{{ ucfirst($gender) }}</option>
                            @endforeach
                        </select>
                        @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="marital_status" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Marital Status</label>
                        <select id="marital_status" name="marital_status" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                            <option value="">Select status</option>
                            @foreach ($maritalStatuses as $status)
                                <option value="{{ $status }}" @selected(old('marital_status', $employee->marital_status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('marital_status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
        </div>

        {{-- Step 2: Contact, Assignment & Access --}}
        <div class="form-step hidden" data-step="2">
            <section class="rounded-2xl border border-emerald-950/8 bg-white p-6 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.45)] space-y-6 sm:p-7">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-[#1a3a2d]">Contact, Assignment & Access</h2>
                    <p class="mt-1 text-sm text-gray-500">Set communication, organizational assignment, and account linkage.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="contact_no" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Contact Number</label>
                        <input id="contact_no" name="contact_no" type="text" value="{{ old('contact_no', $employee->contact_no) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="e.g. 09171234567" />
                        @error('contact_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $employee->email) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="name@example.com" />
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                     <div>
                        <label for="division_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Division</label>
                        @php $oldDivision = old('division_id', $employee->division_id); @endphp
                        <select id="division_id" name="division_id" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                            <option value="">Unassigned</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->division_id }}" @selected($oldDivision == $division->division_id)>{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                        @error('division_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                     <div>
                        <label for="section_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Section</label>
                        <select id="section_id" name="section_id" data-selected="{{ old('section_id', $employee->section_id) }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                            <option value="">Unassigned</option>
                        </select>
                        @error('section_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="position_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Position</label>
                    <select id="position_id" name="position_id" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                        <option value="">Unassigned</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->position_id }}" @selected(old('position_id', $employee->position_id) == $position->position_id)>{{ $position->position_title }}</option>
                        @endforeach
                    </select>
                    @error('position_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                
                <div class="pt-6 border-t border-gray-200">
                    <div class="rounded-2xl border border-emerald-100/80 bg-gradient-to-br from-emerald-50/90 via-white to-slate-50 p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold tracking-tight text-[#1a3a2d]">System Access</h2>
                                <p class="mt-1 text-sm text-gray-600">Review and manage this employee's login access.</p>
                            </div>
                            @if($employee->account_id)
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.08em] text-emerald-700">
                                    Linked
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.08em] text-amber-700">
                                    Unassigned
                                </span>
                            @endif
                        </div>

                        @if($employee->account_id)
                            <input type="hidden" id="account_id" name="account_id" value="{{ $employee->account_id }}">
                            <p class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800">
                                Linked account: <strong class="font-semibold">{{ $employee->account->username }}</strong>
                            </p>
                        @else
                            <input type="hidden" id="account_id" name="account_id" value="">
                            <p class="mt-4 rounded-xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-gray-600 backdrop-blur">
                                No linked account yet. Enable account creation below to generate and attach credentials.
                            </p>
                        @endif

                        @error('account_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

                        @if(!$employee->account_id)
                        <div class="relative mt-4 rounded-xl border border-emerald-100 bg-white/75 p-4">
                            <label for="create_account" class="flex items-start gap-3">
                                <input id="create_account" name="create_account" type="checkbox" value="1" class="mt-1 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600" @checked(old('create_account'))>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Create a New Account for this Employee</span>
                                    <span class="block text-xs text-gray-500">A system user will be created and automatically linked to this employee profile.</span>
                                </span>
                            </label>
                        </div>

                        <div id="new_account_fields" class="mt-4 space-y-4 rounded-xl border border-emerald-100 bg-white p-4 hidden shadow-sm">
                        <div>
                            <label for="new_account_username" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Username</label>
                            <input id="new_account_username" name="new_account_username" type="text" value="{{ old('new_account_username') }}" class="mt-1 h-12 w-full rounded-xl border-gray-300 bg-white px-4 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 shadow-sm" />
                            @error('new_account_username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new_account_role" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Role</label>
                            <select id="new_account_role" name="new_account_role" class="mt-1 h-12 w-full rounded-xl border-gray-300 bg-white px-4 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                                @foreach(($accountRoles ?? []) as $roleOption)
                                    <option value="{{ $roleOption['value'] }}" @selected(old('new_account_role', 'employee') === $roleOption['value'])>{{ $roleOption['label'] }}</option>
                                @endforeach
                            </select>
                            @error('new_account_role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new_account_password" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Password</label>
                            <input id="new_account_password" name="new_account_password" type="password" class="mt-1 h-12 w-full rounded-xl border-gray-300 bg-white px-4 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="Leave blank for default password" />
                            <p class="mt-1 text-xs text-gray-500">Default is "password". The user will be prompted to change it on first login.</p>
                            @error('new_account_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        {{-- Form Actions/Navigation --}}
        <div class="sticky bottom-0 z-10 -mx-2 rounded-t-2xl border-t border-gray-200 bg-white/95 px-2 py-4 backdrop-blur sm:-mx-0 sm:rounded-2xl sm:border sm:border-gray-200 sm:px-4 sm:shadow-[0_-10px_30px_-20px_rgba(15,23,42,0.35)]">
            <div class="flex items-center justify-end gap-3">
            <button type="button" id="prevBtn" class="hidden rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Previous</button>
            <button type="button" id="nextBtn" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg">Next Step <i class="fas fa-arrow-right"></i></button>
            <button type="submit" id="submitBtn" data-loading-text="Updating employee..." class="hidden items-center gap-2 rounded-xl bg-[#1a3a2d] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg">
                <i class="fas fa-save mr-2"></i>Update Employee
            </button>
            </div>
        </div>
    </form>

    <div id="editEmployeeToastStack" class="pointer-events-none fixed right-4 top-6 z-[130] w-[min(24rem,calc(100vw-2rem))] space-y-2"></div>
</div>
@endsection

@push('scripts')
@php
    $sectionsByDivision = [];
    foreach ($divisions as $division) {
        $sectionsByDivision[$division->division_id] = $division->sections->map(fn ($section) => ['id' => $section->section_id, 'name' => $section->section_name])->values()->toArray();
    }
@endphp
<script>
    (function () {
        // Your existing section population script
        const sectionsByDivision = @json($sectionsByDivision);
        const allSections = Object.values(sectionsByDivision).flat();
        const divisionSelect = document.getElementById('division_id');
        const sectionSelect = document.getElementById('section_id');
        const initialSection = sectionSelect.getAttribute('data-selected');
        const renderSections = (divisionId, selectedValue) => {
            const rows = (divisionId && sectionsByDivision[divisionId]) ? sectionsByDivision[divisionId] : allSections;
            sectionSelect.innerHTML = '<option value="">Unassigned</option>';
            rows.forEach(section => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = section.name;
                if (selectedValue && selectedValue === String(section.id)) {
                    option.selected = true;
                }
                sectionSelect.appendChild(option);
            });
        };
        divisionSelect.addEventListener('change', () => {
            sectionSelect.setAttribute('data-selected', '');
            renderSections(divisionSelect.value, null);
        });
        renderSections(divisionSelect.value, initialSection);

        // Account creation toggle
        const createAccountChk = document.getElementById('create_account');
        const newAccountFields = document.getElementById('new_account_fields');
        if (createAccountChk) {
            const toggleFields = () => {
                if (createAccountChk.checked) {
                    newAccountFields.classList.remove('hidden');
                } else {
                    newAccountFields.classList.add('hidden');
                }
            };
            createAccountChk.addEventListener('change', toggleFields);
            if ('{{ old('new_account_username') }}' || createAccountChk.checked) {
                createAccountChk.checked = true;
            }
            toggleFields();
        }

        // Image Preview
        const profileInput = document.getElementById('profile_img');
        const imagePreview = document.getElementById('image_preview');
        const uploadPrompt = document.getElementById('upload_prompt');
        profileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                    uploadPrompt.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Form Wizard Logic
        const steps = Array.from(document.querySelectorAll('.form-step'));
        const tabs = Array.from(document.querySelectorAll('.step-tab'));
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('editEmployeeForm');
        const toastStackEl = document.getElementById('editEmployeeToastStack');
        let currentStep = 1;
        const activeToastKeys = new Set();

        if (toastStackEl && toastStackEl.parentElement !== document.body) {
            document.body.appendChild(toastStackEl);
        }

        const stepRequiredFields = {
            1: [
                { id: 'first_name', label: 'First Name' },
                { id: 'last_name', label: 'Last Name' },
                { id: 'date_of_birth', label: 'Date of Birth' },
                { id: 'gender', label: 'Gender' },
                { id: 'marital_status', label: 'Marital Status' },
            ],
            2: [
                { id: 'contact_no', label: 'Contact Number' },
                { id: 'email', label: 'Email Address' },
                { id: 'division_id', label: 'Division' },
                { id: 'section_id', label: 'Section' },
                { id: 'position_id', label: 'Position' },
            ],
        };

        const getStepElement = (stepNumber) => document.querySelector(`.form-step[data-step="${stepNumber}"]`);

        const showFormToast = (message, type = 'error') => {
            if (!toastStackEl || !message) return;

            const key = `${type}:${message}`;
            if (activeToastKeys.has(key)) return;
            activeToastKeys.add(key);

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto rounded-xl border px-5 py-3 text-sm font-semibold text-white shadow-2xl opacity-0 translate-y-2 transition-all duration-200 ease-out';
            toast.textContent = message;

            if (type === 'error') {
                toast.classList.add('border-red-800', 'bg-red-700');
            } else {
                toast.classList.add('border-emerald-800', 'bg-[#1a3a2d]');
            }

            toastStackEl.appendChild(toast);

            while (toastStackEl.children.length > 3) {
                const first = toastStackEl.firstElementChild;
                if (first) first.remove();
            }

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
            });

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    toast.remove();
                    activeToastKeys.delete(key);
                }, 220);
            }, 3200);
        };

        const markFieldInvalid = (field) => {
            field.classList.add('border-red-300', 'ring-2', 'ring-red-100', 'focus:border-red-400', 'focus:ring-red-100');
            field.setAttribute('aria-invalid', 'true');
        };

        const clearFieldInvalid = (field) => {
            field.classList.remove('border-red-300', 'ring-2', 'ring-red-100', 'focus:border-red-400', 'focus:ring-red-100');
            field.removeAttribute('aria-invalid');
        };

        const wireFieldCleanup = (field) => {
            const clear = () => clearFieldInvalid(field);
            field.addEventListener('input', clear);
            field.addEventListener('change', clear);
        };

        Object.values(stepRequiredFields)
            .flat()
            .forEach(({ id }) => {
                const field = document.getElementById(id);
                if (field) wireFieldCleanup(field);
            });

        const validateStep = (stepNumber) => {
            const fields = [...(stepRequiredFields[stepNumber] || [])];

            if (stepNumber === 2 && createAccountChk?.checked) {
                fields.push(
                    { id: 'new_account_username', label: 'Username' },
                    { id: 'new_account_role', label: 'Role' },
                );
            }

            for (const rule of fields) {
                const field = document.getElementById(rule.id);
                if (!field) {
                    continue;
                }

                const value = String(field.value ?? '').trim();
                if (value === '') {
                    markFieldInvalid(field);
                    field.focus();
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    showFormToast(`${rule.label} is required before continuing.`, 'error');
                    return false;
                }

                clearFieldInvalid(field);
            }

            return true;
        };

        const updateButtons = () => {
            prevBtn.classList.toggle('hidden', currentStep === 1);
            if (currentStep === steps.length) {
                nextBtn.classList.add('hidden');
                nextBtn.classList.remove('inline-flex');
                submitBtn.classList.remove('hidden');
                submitBtn.classList.add('inline-flex');
            } else {
                nextBtn.classList.remove('hidden');
                nextBtn.classList.add('inline-flex');
                submitBtn.classList.add('hidden');
                submitBtn.classList.remove('inline-flex');
            }
        };
        
        const goToStep = (stepNumber) => {
            currentStep = stepNumber;
            steps.forEach(step => {
                step.classList.toggle('hidden', parseInt(step.dataset.step) !== currentStep);
            });
            tabs.forEach(tab => {
                const isCurrent = parseInt(tab.dataset.step) === currentStep;
                const indicator = tab.querySelector('.step-indicator');
                tab.classList.toggle('border-[#1a3a2d]', isCurrent);
                tab.classList.toggle('text-[#1a3a2d]', isCurrent);
                tab.classList.toggle('border-transparent', !isCurrent);
                tab.classList.toggle('text-gray-500', !isCurrent);
                indicator.classList.toggle('bg-[#1a3a2d]', isCurrent);
                indicator.classList.toggle('text-white', isCurrent);
                indicator.classList.toggle('bg-gray-200', !isCurrent);
                indicator.classList.toggle('text-gray-600', !isCurrent);
            });
            updateButtons();
        };

        nextBtn.addEventListener('click', () => {
            if (!validateStep(currentStep)) {
                return;
            }
            if (currentStep < steps.length) {
                goToStep(currentStep + 1);
            }
        });
        prevBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        });
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetStep = parseInt(tab.dataset.step);
                if (targetStep > currentStep && !validateStep(currentStep)) {
                    return;
                }
                goToStep(targetStep);
            });
        });

        form?.addEventListener('submit', (event) => {
            for (let step = 1; step <= steps.length; step++) {
                if (!validateStep(step)) {
                    event.preventDefault();
                    goToStep(step);
                    return;
                }
            }
        });
        
        // Initial state
        updateButtons();
    })();
</script>
@endpush