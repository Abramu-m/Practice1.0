@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Pending User Verification' }}
 @endsection

@section('main_content')
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to All Users
            </a>
        </div>
        <div class="col-md-6 text-end">
            @if($users->count() > 0)
                <button type="button" class="btn btn-success" onclick="selectAll()">
                    <i class="fas fa-check-square"></i> Select All
                </button>
                <button type="button" class="btn btn-warning" onclick="deselectAll()">
                    <i class="fas fa-square"></i> Deselect All
                </button>
                <button type="button" class="btn btn-primary" onclick="bulkVerify()">
                    <i class="fas fa-user-check"></i> Verify Selected
                </button>
            @endif
        </div>
    </div>

    <div class="card">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-clock"></i> Pending Verification 
                <span class="badge bg-warning">{{ $users->total() }}</span>
            </h5>
        </div>

        <div class="card-body">
            @if($users->count() > 0)
                <form id="bulkVerifyForm" action="{{ route('users.bulk-verify') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll()">
                                    </th>
                                    <th>S/N</th>
                                    <th>Names</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox">
                                    </td>
                                    <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($user->profile_picture)
                                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                     alt="Profile" class="rounded-circle me-2" width="30" height="30">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 30px; height: 30px; font-size: 12px; color: white;">
                                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-info text-black">{{ ucfirst($user->role) }}</span>
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('users.show', $user->id) }}" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" title="Verify User"
                                                    onclick="verifyUser({{ $user->id }})">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Hidden form for individual user verification -->
                <form id="individualVerifyForm" action="" method="POST" style="display: none;" 
                      data-verify-url="{{ route('users.verify', 'USER_ID') }}">
                    @csrf
                    @method('PATCH')
                </form>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                    <h5>No Pending Verifications</h5>
                    <p class="text-muted">All users are currently verified.</p>
                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                        <i class="fas fa-users"></i> View All Users
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
function selectAll() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
}

function toggleAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function bulkVerify() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one user to verify.');
        return;
    }
    
    if (confirm(`Are you sure you want to verify ${selectedCheckboxes.length} user(s)?`)) {
        document.getElementById('bulkVerifyForm').submit();
    }
}

function verifyUser(userId) {
    if (confirm('Are you sure you want to verify this user?')) {
        const form = document.getElementById('individualVerifyForm');
        const verifyUrl = form.dataset.verifyUrl.replace('USER_ID', userId);
        form.action = verifyUrl;
        form.submit();
    }
}

// Update select all checkbox based on individual selections
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const totalCheckboxes = checkboxes.length;
            const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked').length;
            
            selectAllCheckbox.checked = totalCheckboxes === checkedCheckboxes;
            selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
        });
    });
});
</script>
@endsection
