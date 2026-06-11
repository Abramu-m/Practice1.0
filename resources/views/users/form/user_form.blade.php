@php
    $isRegistration = request()->route()->getName() === 'register';
    $formAction = $isRegistration 
        ? route('register.store') 
        : (isset($user) ? route('users.update', $user->id) : route('users.store'));
@endphp

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="col-sm-12">
    @csrf
    @if(isset($user)) @method('PUT') @endif
    <div class="row mb-3">
        <div class="col-sm-4">
            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name ?? '') }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Middle Name</label>
                <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $user->middle_name ?? '') }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name ?? '') }}">
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-4">
            <div class="mb-3">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', isset($user) && $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Gender</label>
                <select name="gender" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="male" {{ (old('gender', $user->gender ?? '') == 'male') ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ (old('gender', $user->gender ?? '') == 'female') ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ (old('gender', $user->gender ?? '') == 'other') ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $user->address ?? '') }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}">
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="{{ isset($user) ? 'Leave blank to keep current password' : 'Enter Password' }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="{{ isset($user) ? 'Leave blank to keep current password' : 'Confirm Password' }}">
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="">Select Role</option>
                    <option value="user" {{ (old('role', $user->role ?? '') == 'user') ? 'selected' : '' }}>User</option>
                    <option value="doctor" {{ (old('role', $user->role ?? '') == 'doctor') ? 'selected' : '' }}>Doctor</option>
                    <option value="nurse" {{ (old('role', $user->role ?? '') == 'nurse') ? 'selected' : '' }}>Nurse</option>
                    <option value="receptionist" {{ (old('role', $user->role ?? '') == 'receptionist') ? 'selected' : '' }}>Receptionist</option>
                    <option value="cashier" {{ (old('role', $user->role ?? '') == 'cashier') ? 'selected' : '' }}>Cashier</option>
                    <option value="pharmacist" {{ (old('role', $user->role ?? '') == 'pharmacist') ? 'selected' : '' }}>Pharmacist</option>
                    <option value="lab_technician" {{ (old('role', $user->role ?? '') == 'lab_technician') ? 'selected' : '' }}>Lab Technician</option>
                    <option value="radiologist" {{ (old('role', $user->role ?? '') == 'radiologist') ? 'selected' : '' }}>Radiologist</option>
                    @if(isset($user) && in_array($user->role, ['admin', 'super_admin']))
                        <option value="{{ $user->role }}" selected>{{ $user->role === 'super_admin' ? 'Super Admin (legacy)' : 'Admin (legacy)' }}</option>
                    @endif
                </select>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Status</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ (old('is_active', $user->is_active ?? 1) == 1) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ (old('is_active', $user->is_active ?? '') == 0) ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control-file">
                @if(isset($user) && $user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" class="img-thumbnail mt-2" width="100">
                @endif
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-4">
            <div class="form-check mt-2">
                <input type="checkbox" name="is_admin" value="1" class="form-check-input" id="is_admin_check"
                    {{ old('is_admin', $user->is_admin ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_admin_check">
                    Admin Access <small class="text-muted">(full admin navigation &amp; settings)</small>
                </label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 p-3">
            <button class="btn btn-success">{{ isset($user) ? 'Update' : 'Create' }}</button>
        </div>
    </div>
</form>
