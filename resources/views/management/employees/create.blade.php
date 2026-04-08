@extends('layouts.app')

@section('title', 'Add Employee')

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
                <h1 class="text-3xl font-extrabold tracking-tight">Add New Employee</h1>
                <p class="text-sm text-white/75">Create a new employee record, assign department details, and provision system access in guided steps.</p>
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

    <form method="POST" action="{{ route('employees.store') }}" class="space-y-6" enctype="multipart/form-data" id="addEmployeeForm">
        @csrf

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
            <p class="mt-3 text-xs font-medium uppercase tracking-[0.14em] text-gray-500">Complete the fields below to save the employee profile.</p>
        </div>

        {{-- Step 1: Personal Information --}}
        <div class="form-step" data-step="1">
            <section class="rounded-2xl border border-emerald-950/8 bg-white p-6 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.45)] space-y-6 sm:p-7">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-[#1a3a2d]">Personal Details</h2>
                    <p class="mt-1 text-sm text-gray-500">Capture legal identity and basic profile data.</p>
                </div>
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Profile Picture Uploader --}}
                    <div class="md:col-span-1">
                        <label class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Profile Picture</label>
                        <div class="mt-1">
                            <input id="profile_img" name="profile_img" type="file" accept="image/*" class="hidden" />
                            <label for="profile_img" class="group cursor-pointer flex flex-col items-center justify-center w-full h-52 border-2 border-dashed border-gray-300 rounded-xl bg-[#f7faf8] hover:border-emerald-500 hover:bg-emerald-50 transition">
                                <img id="image_preview" src="" alt="Image Preview" class="hidden w-full h-full object-cover rounded-xl"/>
                                <div id="upload_prompt" class="flex flex-col items-center justify-center text-gray-500">
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
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="e.g. Juan" autocomplete="given-name" />
                            @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="middle_name" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Middle Name</label>
                            <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="e.g. Santos" autocomplete="additional-name" />
                            @error('middle_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                         <div>
                            <label for="last_name" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Last Name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="e.g. Dela Cruz" autocomplete="family-name" />
                            @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="suffix" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Suffix</label>
                            <input id="suffix" name="suffix" type="text" value="{{ old('suffix') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="e.g. Jr., III" />
                            @error('suffix')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                         <div>
                            <label for="date_of_birth" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Date of Birth</label>
                            <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" max="{{ now()->toDateString() }}" autocomplete="bday" />
                            @error('date_of_birth')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                 <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                     <div>
                        <label for="employee_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Employee ID</label>
                        <input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-gray-100 px-4 text-gray-500 cursor-not-allowed shadow-sm focus:border-gray-200 focus:ring-0" placeholder="Auto-generated" readonly aria-readonly="true" />
                        <p class="mt-1 text-xs text-gray-500">Generated by the system after saving.</p>
                        @error('employee_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="gender" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Gender</label>
                        <select id="gender" name="gender" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                            <option value="">Select gender</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ ucfirst($gender) }}</option>
                            @endforeach
                        </select>
                        @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="marital_status" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Marital Status</label>
                        <select id="marital_status" name="marital_status" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                            <option value="">Select status</option>
                            @foreach ($maritalStatuses as $status)
                                <option value="{{ $status }}" @selected(old('marital_status') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('marital_status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="signature" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Signature (PNG only)</label>
                        <input id="signature" name="signature" type="file" accept=".png,image/png" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-[#f8faf9] px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" />
                        <p class="mt-1 text-xs text-gray-500">Accepted format: PNG only (max 2MB).</p>
                        @error('signature')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-4 inline-flex items-center text-sm font-semibold text-gray-500">+63</span>
                            <input id="contact_no" name="contact_no" type="text" value="{{ old('contact_no') }}" class="h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] pl-14 pr-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="9XXXXXXXXX" inputmode="numeric" maxlength="10" autocomplete="tel-national" aria-describedby="contact_no_helper" />
                        </div>
                        <p id="contact_no_helper" class="mt-1 text-xs text-gray-500">Enter 10-digit PH mobile number (example: 9171234567).</p>
                        @error('contact_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="name@example.com" />
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                     <div>
                        <label for="division_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Division</label>
                        <select id="division_id" name="division_id" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
                            <option value="">Unassigned</option>
                            @php $oldDivision = old('division_id', $selectedDivision); @endphp
                            @foreach ($divisions as $division)
                                <option value="{{ $division->division_id }}" @selected($oldDivision == $division->division_id)>{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                        @error('division_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                     <div>
                        <label for="section_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Section</label>
                        <select id="section_id" name="section_id" data-selected="{{ old('section_id', $selectedSection ?? '') }}" class="mt-1 h-12 w-full rounded-xl border-gray-200 bg-[#f8faf9] px-4 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 shadow-sm">
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
                            <option value="{{ $position->position_id }}" @selected(old('position_id') == $position->position_id)>{{ $position->position_title }}</option>
                        @endforeach
                    </select>
                    @error('position_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                
                {{-- System Access Section (Now part of Step 2) --}}
                <div class="pt-6 border-t border-gray-200">
                    <div class="rounded-2xl border border-emerald-100/80 bg-gradient-to-br from-emerald-50/90 via-white to-slate-50 p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold tracking-tight text-[#1a3a2d]">System Access</h2>
                                <p class="mt-1 text-sm text-gray-600">Configure login access for this employee.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.08em] text-amber-700">
                                Unassigned
                            </span>
                        </div>

                        <input type="hidden" id="account_id" name="account_id" value="">
                        <p class="mt-4 rounded-xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-gray-600 backdrop-blur">
                            No linked account yet. Enable account creation below to generate credentials in this step.
                        </p>
                        @error('account_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

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
                            <input id="new_account_username" name="new_account_username" type="text" value="{{ old('new_account_username') }}" class="mt-1 h-12 w-full rounded-xl border-gray-300 bg-white px-4 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="letters, numbers, ., _, -" autocomplete="username" />
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
                            <input id="new_account_password" name="new_account_password" type="password" class="mt-1 h-12 w-full rounded-xl border-gray-300 bg-white px-4 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 shadow-sm" placeholder="At least 6 characters (optional)" autocomplete="new-password" />
                            <p class="mt-1 text-xs text-gray-500">Default is "password". The user will be prompted to change it on first login.</p>
                            @error('new_account_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Form Actions/Navigation --}}
        <div class="sticky bottom-0 z-10 -mx-2 rounded-t-2xl border-t border-gray-200 bg-white/95 px-2 py-4 backdrop-blur sm:-mx-0 sm:rounded-2xl sm:border sm:border-gray-200 sm:px-4 sm:shadow-[0_-10px_30px_-20px_rgba(15,23,42,0.35)]">
            <div class="flex items-center justify-end gap-3">
            <button type="button" id="prevBtn" class="hidden rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Previous</button>
            <button type="button" id="nextBtn" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg">Next Step <i class="fas fa-arrow-right"></i></button>
            <button type="submit" id="submitBtn" data-loading-text="Saving employee..." class="hidden items-center gap-2 rounded-xl bg-[#1a3a2d] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg">
                <i class="fas fa-save mr-2"></i>Save Employee
            </button>
            </div>
        </div>
    </form>

    <div id="createEmployeeToastStack" class="pointer-events-none fixed right-4 top-6 z-[130] w-[min(24rem,calc(100vw-2rem))] space-y-2"></div>
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
        const showAccountFields = () => newAccountFields.classList.remove('hidden');
        const hideAccountFields = () => newAccountFields.classList.add('hidden');

        if (createAccountChk) {
            const toggleFields = () => {
                if (createAccountChk.checked) {
                    showAccountFields();
                } else {
                    hideAccountFields();
                    ['new_account_username', 'new_account_role', 'new_account_password'].forEach((fieldId) => {
                        const field = document.getElementById(fieldId);
                        if (!field) {
                            return;
                        }
                        field.classList.remove('border-red-400', 'ring-2', 'ring-red-100', 'focus:border-red-500', 'focus:ring-red-100');
                        field.classList.add('border-gray-300');
                        field.removeAttribute('aria-invalid');
                        const inlineError = document.querySelector(`[data-inline-error-for="${fieldId}"]`);
                        if (inlineError) {
                            inlineError.textContent = '';
                            inlineError.classList.add('hidden');
                        }
                    });
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
        const form = document.getElementById('addEmployeeForm');
        const toastStackEl = document.getElementById('createEmployeeToastStack');
        let currentStep = 1;
        const activeToastKeys = new Set();

        if (toastStackEl && toastStackEl.parentElement !== document.body) {
            document.body.appendChild(toastStackEl);
        }

        const sanitizePhoneLocal = (value = '') => {
            let digits = String(value || '').replace(/\D/g, '');

            if (digits.startsWith('63')) {
                digits = digits.slice(2);
            }

            if (digits.startsWith('0')) {
                digits = digits.slice(1);
            }

            return digits.slice(0, 10);
        };

        const fieldValidationRules = {
            first_name: {
                step: 1,
                validate: (value) => {
                    const v = String(value || '').trim();
                    if (!v) return 'First Name is required.';
                    if (!/^[A-Za-z .'-]+$/.test(v)) return 'First Name may only contain letters, spaces, apostrophe, period, and hyphen.';
                    return '';
                },
            },
            middle_name: {
                step: 1,
                validate: (value) => {
                    const v = String(value || '').trim();
                    if (!v) return '';
                    if (!/^[A-Za-z .'-]+$/.test(v)) return 'Middle Name may only contain letters, spaces, apostrophe, period, and hyphen.';
                    return '';
                },
            },
            last_name: {
                step: 1,
                validate: (value) => {
                    const v = String(value || '').trim();
                    if (!v) return 'Last Name is required.';
                    if (!/^[A-Za-z .'-]+$/.test(v)) return 'Last Name may only contain letters, spaces, apostrophe, period, and hyphen.';
                    return '';
                },
            },
            suffix: {
                step: 1,
                validate: (value) => {
                    const v = String(value || '').trim();
                    if (!v) return '';
                    if (!/^[A-Za-z0-9.,\- ]+$/.test(v)) return 'Suffix may only contain letters, numbers, comma, period, spaces, and hyphen.';
                    if (v.length > 10) return 'Suffix must not exceed 10 characters.';
                    return '';
                },
            },
            date_of_birth: {
                step: 1,
                validate: (value) => {
                    const v = String(value || '').trim();
                    if (!v) return 'Date of Birth is required.';
                    const selected = new Date(v);
                    if (Number.isNaN(selected.getTime())) return 'Please provide a valid Date of Birth.';
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (selected > today) return 'Date of Birth cannot be in the future.';
                    return '';
                },
            },
            gender: {
                step: 1,
                validate: (value) => String(value || '').trim() ? '' : 'Gender is required.',
            },
            marital_status: {
                step: 1,
                validate: (value) => String(value || '').trim() ? '' : 'Marital Status is required.',
            },
            contact_no: {
                step: 2,
                validate: (value) => {
                    const localDigits = sanitizePhoneLocal(value);
                    if (!localDigits) return 'Contact Number is required.';
                    if (!/^9\d{9}$/.test(localDigits)) return 'Enter a valid PH mobile number (example: +639171234567).';
                    return '';
                },
            },
            email: {
                step: 2,
                validate: (value, field) => {
                    const v = String(value || '').trim();
                    if (!v) return 'Email Address is required.';
                    if (field && typeof field.checkValidity === 'function' && !field.checkValidity()) {
                        return 'Enter a valid email address (example: name@example.com).';
                    }
                    return '';
                },
            },
            division_id: {
                step: 2,
                validate: (value) => String(value || '').trim() ? '' : 'Division is required.',
            },
            section_id: {
                step: 2,
                validate: (value) => String(value || '').trim() ? '' : 'Section is required.',
            },
            position_id: {
                step: 2,
                validate: (value) => String(value || '').trim() ? '' : 'Position is required.',
            },
            new_account_username: {
                step: 2,
                when: () => Boolean(createAccountChk?.checked),
                validate: (value) => {
                    const v = String(value || '').trim();
                    if (!v) return 'Username is required when account creation is enabled.';
                    if (!/^[A-Za-z0-9._-]{3,255}$/.test(v)) return 'Username must be 3-255 characters and may only contain letters, numbers, dot, underscore, or hyphen.';
                    return '';
                },
            },
            new_account_role: {
                step: 2,
                when: () => Boolean(createAccountChk?.checked),
                validate: (value) => String(value || '').trim() ? '' : 'Role is required when account creation is enabled.',
            },
            new_account_password: {
                step: 2,
                when: () => Boolean(createAccountChk?.checked),
                validate: (value) => {
                    const v = String(value || '');
                    if (!v) return '';
                    return v.length >= 6 ? '' : 'Password must be at least 6 characters.';
                },
            },
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

        const getInlineErrorNode = (field) => {
            let node = form.querySelector(`[data-inline-error-for="${field.id}"]`);
            if (node) {
                return node;
            }

            node = document.createElement('p');
            node.dataset.inlineErrorFor = field.id;
            node.className = 'mt-1 text-xs font-medium text-red-600 hidden';
            node.setAttribute('aria-live', 'polite');

            const parent = field.parentElement;
            const host = parent && parent.classList.contains('relative')
                ? parent.parentElement
                : parent;

            host?.appendChild(node);
            return node;
        };

        const markFieldInvalid = (field, message) => {
            const errorNode = getInlineErrorNode(field);
            field.classList.add('border-red-400', 'ring-2', 'ring-red-100', 'focus:border-red-500', 'focus:ring-red-100');
            field.classList.remove('border-gray-200', 'border-gray-300');
            field.setAttribute('aria-invalid', 'true');
            if (errorNode) {
                errorNode.textContent = message;
                errorNode.classList.remove('hidden');
            }
        };

        const clearFieldInvalid = (field) => {
            const errorNode = getInlineErrorNode(field);
            const defaultBorderClass = field.dataset.defaultBorderClass || 'border-gray-200';
            field.classList.remove('border-red-400', 'ring-2', 'ring-red-100', 'focus:border-red-500', 'focus:ring-red-100');
            field.classList.remove('border-gray-200', 'border-gray-300');
            field.classList.add(defaultBorderClass);
            field.removeAttribute('aria-invalid');
            if (errorNode) {
                errorNode.textContent = '';
                errorNode.classList.add('hidden');
            }
        };

        const validateField = (fieldId) => {
            const field = document.getElementById(fieldId);
            const rule = fieldValidationRules[fieldId];

            if (!field || !rule) {
                return true;
            }

            if (typeof rule.when === 'function' && !rule.when()) {
                clearFieldInvalid(field);
                return true;
            }

            const message = rule.validate(field.value, field);
            if (message) {
                markFieldInvalid(field, message);
                return false;
            }

            clearFieldInvalid(field);
            return true;
        };

        Object.keys(fieldValidationRules).forEach((fieldId) => {
            const field = document.getElementById(fieldId);
            if (!field) {
                return;
            }

            field.dataset.defaultBorderClass = field.classList.contains('border-gray-300')
                ? 'border-gray-300'
                : 'border-gray-200';

            const eventName = field.tagName === 'SELECT' || field.type === 'file' ? 'change' : 'input';
            field.addEventListener(eventName, () => {
                if (fieldId === 'contact_no') {
                    const normalizedDigits = sanitizePhoneLocal(field.value);
                    field.value = normalizedDigits;
                }
                validateField(fieldId);
            });

            field.addEventListener('blur', () => {
                if (fieldId === 'contact_no') {
                    const normalizedDigits = sanitizePhoneLocal(field.value);
                    field.value = normalizedDigits;
                }
                validateField(fieldId);
            });
        });

        const contactField = document.getElementById('contact_no');
        if (contactField) {
            contactField.value = sanitizePhoneLocal(contactField.value);
        }

        const validateStep = (stepNumber) => {
            const fieldsForStep = Object.entries(fieldValidationRules)
                .filter(([, rule]) => rule.step === stepNumber)
                .map(([fieldId]) => fieldId);

            let firstInvalidField = null;

            fieldsForStep.forEach((fieldId) => {
                if (!validateField(fieldId) && !firstInvalidField) {
                    firstInvalidField = document.getElementById(fieldId);
                }
            });

            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                showFormToast('Please correct the highlighted fields before continuing.', 'error');
                return false;
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
            const submitContactField = document.getElementById('contact_no');
            if (submitContactField) {
                const normalizedDigits = sanitizePhoneLocal(submitContactField.value);
                submitContactField.value = normalizedDigits ? `+63${normalizedDigits}` : '';
            }

            for (let step = 1; step <= steps.length; step++) {
                if (!validateStep(step)) {
                    event.preventDefault();
                    if (submitContactField) {
                        submitContactField.value = sanitizePhoneLocal(submitContactField.value);
                    }
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