@php
    $user = auth()->user();
    $isAdmin = $user && in_array($user->role, ['admin', 'super_admin']);
    $isSuperAdmin = $user && $user->role === 'super_admin';
@endphp

<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <!-- Patient Management -->
        <li class="nav-item {{ request()->routeIs('patients.*') || request()->routeIs('patient_categories.*') || request()->routeIs('patient_visits.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('patients.*') || request()->routeIs('patient_categories.*') || request()->routeIs('patient_visits.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>
                    Patient Management
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Patients</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('patient_categories.index') }}" class="nav-link {{ request()->routeIs('patient_categories.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Patient Categories</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('patient_visits.index') }}" class="nav-link {{ request()->routeIs('patient_visits.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Patient Visits</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Doctor Management -->
        <li class="nav-item {{ request()->routeIs('doctors.*') || request()->routeIs('designations.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('doctors.*') || request()->routeIs('designations.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-md"></i>
                <p>
                    Medical Staff
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Doctors</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('designations.index') }}" class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Designations</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Visit Types -->
        <li class="nav-item">
            <a href="{{ route('visit_types.index') }}" class="nav-link {{ request()->routeIs('visit_types.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-calendar-check"></i>
                <p>Visit Types</p>
            </a>
        </li>

        @if($isAdmin)
        <!-- User Management - Admin and Super Admin only -->
        <li class="nav-item {{ request()->routeIs('users.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users-cog"></i>
                <p>
                    User Management
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('users.pending-verification') }}" class="nav-link {{ request()->routeIs('users.pending-verification') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Pending Verification</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('users.create') }}" class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add User</p>
                    </a>
                </li>
            </ul>
        </li>
        @endif

        @if($isSuperAdmin)
        <!-- System Settings - Super Admin only -->
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>
                    System Settings
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>General Settings</p>
                    </a>
                </li>
            </ul>
        </li>
        @endif

        <!-- Pharmacy Management -->
        <li class="nav-item {{ request()->routeIs('medication-pricing.*') || request()->routeIs('medication-ledger.*') || request()->routeIs('medications.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('medication-pricing.*') || request()->routeIs('medication-ledger.*') || request()->routeIs('medications.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-pills"></i>
                <p>
                    Pharmacy Management
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('medications.index') }}" class="nav-link {{ request()->routeIs('medications.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Medications</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('medication-pricing.index') }}" class="nav-link {{ request()->routeIs('medication-pricing.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Medication Pricing</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('medication-ledger.index') }}" class="nav-link {{ request()->routeIs('medication-ledger.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Medication Ledger</p>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>
