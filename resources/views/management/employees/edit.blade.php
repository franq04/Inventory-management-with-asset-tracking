@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="space-y-6 animate-card">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-extrabold text-[#1a3a2d]">Edit Employee</h1>
            <p class="mt-1 text-sm text-gray-500">Update employee details, assignments, or linked account.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left"></i>
            Back to Employee List
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm">
            <p class="font-bold mb-2 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Please fix the errors below and try again:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-8" enctype="multipart/form-data" id="editEmployeeForm">
        @csrf
        @method('PUT')

        {{-- Step Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button" data-step="1" class="step-tab group inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-semibold border-[#1a3a2d] text-[#1a3a2d]">
                    <span class="step-indicator rounded-full w-6 h-6 flex items-center justify-center bg-[#1a3a2d] text-white">1</span>
                    Personal Information
                </button>
                <button type="button" data-step="2" class="step-tab group inline-flex items-center gap-2 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700">
                     <span class="step-indicator rounded-full w-6 h-6 flex items-center justify-center bg-gray-200 text-gray-600">2</span>
                    Contact, Assignment & Access
                </button>
            </nav>
        </div>

        {{-- Step 1: Personal Information --}}
        <div class="form-step" data-step="1">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
                <h2 class="text-lg font-semibold text-gray-800">Personal Details</h2>
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Profile Picture Uploader --}}
                    <div class="md:col-span-1">
                        <label class="text-sm font-semibold text-gray-600">Profile Picture</label>
                        <div class="mt-1">
                            <input id="profile_img" name="profile_img" type="file" accept="image/*" class="hidden" />
                            <label for="profile_img" class="group cursor-pointer flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-xl hover:border-emerald-500 hover:bg-emerald-50 transition">
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
                            <label for="first_name" class="text-sm font-semibold text-gray-600">First Name <span class="text-red-500">*</span></label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $employee->first_name) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" />
                            @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="middle_name" class="text-sm font-semibold text-gray-600">Middle Name</label>
                            <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $employee->middle_name) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" />
                            @error('middle_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                         <div>
                            <label for="last_name" class="text-sm font-semibold text-gray-600">Last Name <span class="text-red-500">*</span></label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $employee->last_name) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" />
                            @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="suffix" class="text-sm font-semibold text-gray-600">Suffix</label>
                            <input id="suffix" name="suffix" type="text" value="{{ old('suffix', $employee->suffix) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="e.g. Jr., III" />
                            @error('suffix')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                         <div>
                            <label for="date_of_birth" class="text-sm font-semibold text-gray-600">Date of Birth <span class="text-red-500">*</span></label>
                            <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $employee->date_of_birth) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" />
                            @error('date_of_birth')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                 <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                     <div>
                        <label for="employee_id" class="text-sm font-semibold text-gray-600">Employee ID</label>
                        <input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id', $employee->employee_id) }}" class="mt-1 w-full rounded-xl border-gray-200 bg-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" readonly />
                        @error('employee_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="gender" class="text-sm font-semibold text-gray-600">Gender <span class="text-red-500">*</span></label>
                        <select id="gender" name="gender" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            <option value="">Select gender</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender }}" @selected(old('gender', $employee->gender) === $gender)>{{ ucfirst($gender) }}</option>
                            @endforeach
                        </select>
                        @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="marital_status" class="text-sm font-semibold text-gray-600">Marital Status <span class="text-red-500">*</span></label>
                        <select id="marital_status" name="marital_status" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
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
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
                <h2 class="text-lg font-semibold text-gray-800">Contact & Assignment</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="contact_no" class="text-sm font-semibold text-gray-600">Contact Number</label>
                        <input id="contact_no" name="contact_no" type="text" value="{{ old('contact_no', $employee->contact_no) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="e.g. 09171234567" />
                        @error('contact_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="text-sm font-semibold text-gray-600">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $employee->email) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="name@example.com" />
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                     <div>
                        <label for="division_id" class="text-sm font-semibold text-gray-600">Division</label>
                        @php $oldDivision = old('division_id', $employee->division_id); @endphp
                        <select id="division_id" name="division_id" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            <option value="">Unassigned</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->division_id }}" @selected($oldDivision == $division->division_id)>{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                        @error('division_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                     <div>
                        <label for="section_id" class="text-sm font-semibold text-gray-600">Section</label>
                        <select id="section_id" name="section_id" data-selected="{{ old('section_id', $employee->section_id) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            <option value="">Unassigned</option>
                        </select>
                        @error('section_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="position_id" class="text-sm font-semibold text-gray-600">Position</label>
                    <select id="position_id" name="position_id" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                        <option value="">Unassigned</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->position_id }}" @selected(old('position_id', $employee->position_id) == $position->position_id)>{{ $position->position_title }}</option>
                        @endforeach
                    </select>
                    @error('position_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                
                <div class="pt-6 border-t">
                    <h2 class="text-lg font-semibold text-gray-800">System Access</h2>
                     <div>
                        <label for="account_id" class="text-sm font-semibold text-gray-600">Linked Account</label>
                        <select id="account_id" name="account_id" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            <option value="">Unassigned</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->account_id }}" @selected(old('account_id', $employee->account_id) == $account->account_id)>{{ $account->username }} ({{ ucfirst(str_replace('_', ' ', $account->role)) }})</option>
                            @endforeach
                        </select>
                        @error('account_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @if(!$employee->account_id)
                    <div class="relative flex items-start pt-4 mt-4 border-t">
                        <div class="flex h-6 items-center">
                            <input id="create_account" name="create_account" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600" @checked(old('create_account'))>
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="create_account" class="font-semibold text-gray-800">Create a New Account for this Employee</label>
                            <p class="text-xs text-gray-500">This will generate a new user login linked to this employee record.</p>
                        </div>
                    </div>
                    <div id="new_account_fields" class="mt-4 space-y-4 rounded-lg bg-gray-50 border border-gray-200 p-4 hidden">
                        <div>
                            <label for="new_account_username" class="text-sm font-semibold text-gray-600">Username <span class="text-red-500">*</span></label>
                            <input id="new_account_username" name="new_account_username" type="text" value="{{ old('new_account_username') }}" class="mt-1 w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" />
                            @error('new_account_username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new_account_role" class="text-sm font-semibold text-gray-600">Role <span class="text-red-500">*</span></label>
                            <select id="new_account_role" name="new_account_role" class="mt-1 w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <option value="employee" @selected(old('new_account_role', 'employee') === 'employee')>Employee</option>
                                <option value="custodian" @selected(old('new_account_role') === 'custodian')>Custodian</option>
                                <option value="iac" @selected(old('new_account_role') === 'iac')>IAC</option>
                                <option value="division_head" @selected(old('new_account_role') === 'division_head')>Division Head</option>
                            </select>
                            @error('new_account_role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new_account_password" class="text-sm font-semibold text-gray-600">Password</label>
                            <input id="new_account_password" name="new_account_password" type="password" class="mt-1 w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="Leave blank for default password" />
                            <p class="mt-1 text-xs text-gray-500">Default is "password". The user will be prompted to change it on first login.</p>
                            @error('new_account_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @else
                        <p class="mt-4 text-sm text-gray-600 pt-4 border-t">This employee is already linked to the account: <strong class="font-semibold text-emerald-700">{{ $employee->account->username }}</strong>.</p>
                    @endif
                </div>
            </section>
        </div>

        {{-- Form Actions/Navigation --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <button type="button" id="prevBtn" class="hidden rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-200">Previous</button>
            <button type="button" id="nextBtn" class="bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center gap-2">Next Step <i class="fas fa-arrow-right"></i></button>
            <button type="submit" id="submitBtn" class="bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center gap-2">
                <i class="fas fa-save mr-2"></i>Update Employee
            </button>
        </div>
    </form>
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
        const linkAccountSelect = document.getElementById('account_id');
        if (createAccountChk) {
            const toggleFields = () => {
                if (createAccountChk.checked) {
                    newAccountFields.classList.remove('hidden');
                    if(linkAccountSelect) linkAccountSelect.disabled = true;
                } else {
                    newAccountFields.classList.add('hidden');
                    if(linkAccountSelect) linkAccountSelect.disabled = false;
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
        let currentStep = 1;

        const updateButtons = () => {
            prevBtn.classList.toggle('hidden', currentStep === 1);
            nextBtn.classList.toggle('hidden', currentStep === steps.length);
            submitBtn.classList.toggle('hidden', currentStep !== steps.length);
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
                goToStep(parseInt(tab.dataset.step));
            });
        });
        
        // Initial state
        updateButtons();
    })();
</script>
@endpush