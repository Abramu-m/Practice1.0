@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Users' }}
 @endsection

@section('Content_Description')
    {{ 'List of Users.' }}
@endsection

@section('main_content')
    <div class="row mb-3">
        <div class="col-md-8">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add User
            </a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.pending-verification') }}" class="btn btn-warning">
                        <i class="fas fa-user-clock"></i> Pending Verification
                        @if(\App\Models\User::where('is_verified', false)->where('role', '!=', 'super_admin')->count() > 0)
                            <span class="badge badge-light">{{ \App\Models\User::where('is_verified', false)->where('role', '!=', 'super_admin')->count() }}</span>
                        @endif
                    </a>
                @endif
            @endauth
        </div>
        <div class="col-md-4">
            <form method="GET" action="{{ route('users.index') }}" class="form-inline justify-content-end">
                <div class="input-group">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Search users...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif  
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>S/N</th>
                        <th>Names</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verification</th>
                        <th>Profile Picture</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                         alt="Profile" class="rounded-circle mr-2" width="30" height="30">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mr-2" 
                                         style="width: 30px; height: 30px; font-size: 12px; color: white;">
                                        {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</strong>
                                    <br><small class="text-muted">{{ $user->gender }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            {{ ucfirst($user->role) }}
                        </td>
                        <td>
                            @if($user->is_active)
                                Active
                            @else
                                Inactive
                            @endif
                        </td>
                        <td>
                            @if($user->is_verified)
                                <i class="fas fa-check-circle"></i> Verified
                                @if($user->verified_at)
                                    <br><small class="text-muted">{{ $user->verified_at->format('d/m/Y') }}</small>
                                @endif
                            @else
                                <i class="fas fa-clock"></i> Pending
                            @endif
                        </td>
                        <td>
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" class="img-thumbnail" width="40">
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        @if($user->is_verified)
                                            @if($user->role !== 'super_admin')
                                                <form action="{{ route('users.unverify', $user->id) }}" method="POST" 
                                                      style="display: inline;" onsubmit="return confirm('Are you sure you want to revoke verification for this user?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Revoke Verification">
                                                        <i class="fas fa-user-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <form action="{{ route('users.verify', $user->id) }}" method="POST" 
                                                  style="display: inline;" onsubmit="return confirm('Are you sure you want to verify this user?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Verify User">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                @endauth

                                    {{-- Reset password button (admin only) --}}
                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('users.reset-password', $user->id) }}" class="btn btn-sm btn-outline-danger" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                        @endif
                                    @endauth

                                    {{-- Assign as Doctor Button --}}
                                @if($user->role === 'doctor' && $user->is_verified && !$user->doctor)
                                    <a href="{{ route('doctors.create', ['user_id' => $user->id]) }}" 
                                       class="btn btn-sm btn-success" title="Assign as Doctor">
                                        <i class="fas fa-user-md"></i>
                                    </a>
                                @elseif($user->doctor && $user->doctor->id)
                                    <a href="{{ route('doctors.show', $user->doctor->id) }}" 
                                       class="btn btn-sm btn-info" title="View Doctor Profile">
                                        <i class="fas fa-stethoscope"></i>
                                    </a>
                                @endif
                                
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                @if($user->role !== 'super_admin')
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete User?')" class="btn btn-sm btn-danger" title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if($users->hasPages())
                {{ $users->links('pagination::bootstrap-4') }}
            @endif
        </div>
    </div>
@endsection

@section('extra_footer_content')
@endsection