<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\users\show.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'User Details' }}
 @endsection

@section('Content_Description')
    {{ 'User information and details.' }}
@endsection

@section('main_content')
    <div class="card">
        <div class="card-header">
            <h3>{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" class="img-thumbnail mb-3" width="200">
                    @else
                        <div class="bg-light p-4 text-center mb-3">
                            <i class="fas fa-user fa-3x text-muted"></i>
                            <p class="text-muted mt-2">No profile picture</p>
                        </div>
                    @endif
                </div>
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr>
                            <th>First Name:</th>
                            <td>{{ $user->first_name }}</td>
                        </tr>
                        <tr>
                            <th>Middle Name:</th>
                            <td>{{ $user->middle_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Last Name:</th>
                            <td>{{ $user->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td>{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth:</th>
                            <td>{{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td>{{ ucfirst($user->gender) ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $user->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><span class="badge badge-primary text-black">{{ ucfirst($user->role) }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-success text-black">Active</span>
                                @else
                                    <span class="badge badge-danger text-black">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Verified:</th>
                            <td>
                                @if($user->is_verified)
                                    <span class="badge badge-success text-black">Verified</span>
                                @else
                                    <span class="badge badge-warning">Not Verified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-8">
                    @auth
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
                            @if($user->is_verified)
                                @if($user->role !== 'super_admin')
                                    <form action="{{ route('users.unverify', $user->id) }}" method="POST" 
                                          style="display: inline;" onsubmit="return confirm('Are you sure you want to revoke verification for this user?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-user-times"></i> Revoke Verification
                                        </button>
                                    </form>
                                @endif
                            @else
                                <form action="{{ route('users.verify', $user->id) }}" method="POST" 
                                      style="display: inline;" onsubmit="return confirm('Are you sure you want to verify this user?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-user-check"></i> Verify User
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endauth
                    
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    
                    @if($user->role === 'doctor')
                        @if(!$user->doctor)
                            <a href="{{ route('doctors.create', ['user_id' => $user->id]) }}" class="btn btn-success">
                                <i class="fas fa-user-md"></i> Assign as Doctor
                            </a>
                        @elseif($user->doctor && $user->doctor->id)
                            <a href="{{ route('doctors.show', $user->doctor->id) }}" class="btn btn-info">
                                <i class="fas fa-stethoscope"></i> View Doctor Profile
                            </a>
                        @endif
                    @endif
                </div>
                <div class="col-md-4 text-right">
                    @if($user->role !== 'super_admin')
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this user?')" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra_footer_content')
@endsection