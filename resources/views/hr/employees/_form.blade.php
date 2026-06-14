@php
    $employee = $employee ?? null;
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror"
                   value="{{ old('first_name', $employee?->first_name) }}" required>
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="middle_name" class="form-label">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name" class="form-control @error('middle_name') is-invalid @enderror"
                   value="{{ old('middle_name', $employee?->middle_name) }}">
            @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror"
                   value="{{ old('last_name', $employee?->last_name) }}" required>
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                <option value="">-- Select --</option>
                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" {{ old('gender', $employee?->gender) == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                   value="{{ old('date_of_birth', $employee?->date_of_birth?->format('Y-m-d')) }}">
            @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="user_id" class="form-label">Linked User Account</label>
            <select name="user_id" id="user_id" class="form-select select2 @error('user_id') is-invalid @enderror">
                <option value="">-- None --</option>
                @foreach($linkableUsers as $user)
                    <option value="{{ $user->id }}" {{ old('user_id', $employee?->user_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ ucwords(str_replace('_', ' ', $user->role)) }})
                    </option>
                @endforeach
            </select>
            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text text-muted">Link to a system login, if this staff member has one.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $employee?->phone) }}">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $employee?->email) }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                   value="{{ old('address', $employee?->address) }}">
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="job_title" class="form-label">Job Title</label>
            <input type="text" name="job_title" id="job_title" class="form-control @error('job_title') is-invalid @enderror"
                   value="{{ old('job_title', $employee?->job_title) }}">
            @error('job_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="department" class="form-label">Department</label>
            <input type="text" name="department" id="department" class="form-control @error('department') is-invalid @enderror"
                   value="{{ old('department', $employee?->department) }}">
            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="employment_type" class="form-label">Employment Type <span class="text-danger">*</span></label>
            <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                @foreach(['permanent' => 'Permanent', 'contract' => 'Contract', 'casual' => 'Casual', 'volunteer' => 'Volunteer'] as $value => $label)
                    <option value="{{ $value }}" {{ old('employment_type', $employee?->employment_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="date_joined" class="form-label">Date Joined</label>
            <input type="date" name="date_joined" id="date_joined" class="form-control @error('date_joined') is-invalid @enderror"
                   value="{{ old('date_joined', $employee?->date_joined?->format('Y-m-d')) }}">
            @error('date_joined')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="basic_salary" class="form-label">Basic Salary <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">Tsh</span>
                <input type="number" name="basic_salary" id="basic_salary" step="0.01" min="0"
                       class="form-control @error('basic_salary') is-invalid @enderror"
                       value="{{ old('basic_salary', $employee?->basic_salary) }}" required>
            </div>
            @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
            <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money', 'cheque' => 'Cheque'] as $value => $label)
                    <option value="{{ $value }}" {{ old('payment_method', $employee?->payment_method ?? 'cash') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'terminated' => 'Terminated'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $employee?->status ?? 'active') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="bank_name" class="form-label">Bank Name</label>
            <input type="text" name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                   value="{{ old('bank_name', $employee?->bank_name) }}">
            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="bank_account_number" class="form-label">Bank Account Number</label>
            <input type="text" name="bank_account_number" id="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror"
                   value="{{ old('bank_account_number', $employee?->bank_account_number) }}">
            @error('bank_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="mb-3">
            <label for="tin_number" class="form-label">TIN Number</label>
            <input type="text" name="tin_number" id="tin_number" class="form-control @error('tin_number') is-invalid @enderror"
                   value="{{ old('tin_number', $employee?->tin_number) }}">
            @error('tin_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="mb-3">
            <label for="nssf_number" class="form-label">NSSF Number</label>
            <input type="text" name="nssf_number" id="nssf_number" class="form-control @error('nssf_number') is-invalid @enderror"
                   value="{{ old('nssf_number', $employee?->nssf_number) }}">
            @error('nssf_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $employee?->notes) }}</textarea>
    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
