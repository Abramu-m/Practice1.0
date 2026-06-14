<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield("page_title")</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.png') }}" />
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="@yield("page_title")" />
    <meta name="author" content="Abramu Mibaraka" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!--end::Primary Meta Tags-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->

    <!--start::PWA plugin-->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0d6efd">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Practice1.0">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <!--end::PWA plugin-->
    
    <!--begin::Vendor JS (classic, render-blocking - must run BEFORE the Vite
    bundle's deferred module so window.jQuery/$/bootstrap/etc. exist for
    inline page scripts that execute during HTML parsing)-->
    <script src="{{ asset('vendor/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/daterangepicker.js') }}"></script>
    <script src="{{ asset('vendor/select2.min.js') }}"></script>
    <script src="{{ asset('vendor/dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('vendor/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendor/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('vendor/toastr.min.js') }}"></script>
    <script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
    <script src="{{ asset('vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('vendor/overlayscrollbars.browser.es5.min.js') }}"></script>
    <script src="{{ asset('vendor/alpine.min.js') }}" defer></script>
    <!--end::Vendor JS-->

    <!-- Include custom CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  <!-- Page-specific styles from views -->
  @yield('styles')

  <!-- Select2 customization (global) - provides searchable dropdowns across the app -->
  <style>
    /* Make Select2 match Bootstrap's native select appearance */
    .select2-container--default .select2-selection--single {
      height: calc(1.5em + 0.75rem + 2px);
      padding: 0.375rem 0.75rem;
      font-size: 1rem;
      font-weight: 400;
      line-height: 1.5;
      color: #212529;
      background-color: #fff;
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
      padding: 0;
      color: #212529;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #6c757d;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 100%;
      top: 0;
      right: 8px;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
      border-color: #86b7fe;
      outline: 0;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .select2-dropdown {
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
      border: 1px solid #ced4da;
      border-radius: 0.25rem;
      padding: 0.25rem 0.5rem;
    }
  </style>
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-mini sidebar-collapse bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
            <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
          </ul>
          <!--end::Start Navbar Links-->
          <!--begin::Patient Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">@yield("patient_info")</a></li>
          </ul>
          <!--end::Patient Navbar Links-->
          <!--begin::End Navbar Links-->
          <ul class="navbar-nav">
            <!--begin::Navbar Search-->
            <li class="nav-item">
              <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="bi bi-search"></i>
              </a>
            </li>
            <!--end::Navbar Search-->
            <!--begin::Messages Dropdown Menu-->
            <li class="nav-item dropdown" style="display: none;">
              <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-chat-text"></i>
                <span class="navbar-badge badge text-bg-danger">0</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-item dropdown-header">No new messages</span>
              </div>
            </li>
            <!--end::Messages Dropdown Menu-->
            <!--begin::Notifications Dropdown Menu-->
            <li class="nav-item dropdown" style="display: none;">
              <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-bell-fill"></i>
                <span class="navbar-badge badge text-bg-warning">0</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-item dropdown-header">No new notifications</span>
              </div>
            </li>
            <!--end::Notifications Dropdown Menu-->
            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->
            <!--begin::Not Auth User Menu-->
            @if (Auth::guest())
            <li class="nav-item">
              <a class="nav-link" href="{{ route('login') }}">
                <i class="bi bi-box-arrow-in-right"></i>
                <span class="d-none d-md-inline">Login</span >
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('register') }}">
                <i class="bi bi-person-plus"></i>
                <span class="d-none d-md-inline">Register</span>
              </a>
            </li>            
            <!--end::Not Auth User Menu-->
            <!--begin::User Menu Dropdown-->
            @else
            @php
              $currentUser = Auth::user();

              // Role label
              if ($currentUser->isSuperAdmin()) {
                $role = 'Super Admin';
              } elseif ($currentUser->isAdmin()) {
                $role = 'Admin';
              } elseif ($currentUser->isReceptionist()) {
                $role = 'Receptionist';
              } elseif ($currentUser->isDoctor()) {
                $role = 'Doctor';
              } elseif ($currentUser->isCashier()) {
                $role = 'Cashier';
              } elseif ($currentUser->isLabTechnician()) {
                $role = 'Lab Technician';
              } elseif ($currentUser->isPharmacist()) {
                $role = 'Pharmacist';
              } elseif ($currentUser->isNurse()) {
                $role = 'Nurse';
              } elseif ($currentUser->isRadiologist()) {
                $role = 'Radiologist';
              } else {
                $role = $currentUser->role;
              }

              // Clinical icon — admin/super have no clinical role icon
              if ($currentUser->isReceptionist()) {
                $roleIcon = 'fas fa-headset';
              } elseif ($currentUser->isDoctor()) {
                $roleIcon = 'fas fa-stethoscope';
              } elseif ($currentUser->isCashier()) {
                $roleIcon = 'fas fa-cash-register';
              } elseif ($currentUser->isLabTechnician()) {
                $roleIcon = 'fas fa-flask-vial';
              } elseif ($currentUser->isPharmacist()) {
                $roleIcon = 'fas fa-capsules';
              } elseif ($currentUser->isNurse()) {
                $roleIcon = 'fas fa-user-nurse';
              } elseif ($currentUser->isRadiologist()) {
                $roleIcon = 'fas fa-x-ray';
              } else {
                $roleIcon = null;
              }

              // Name color for admin/super only
              $nameColorClass = '';
              $nameColorStyle = '';

              if ($currentUser->isSuperAdmin()) {
                $nameColorStyle = 'color: #d4af37;';
              } elseif ($currentUser->isAdmin()) {
                $nameColorClass = 'text-success';
              }
            @endphp
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                @if(Auth::user()->profile_picture && file_exists(storage_path('app/public/' . Auth::user()->profile_picture)))
                  <img
                    src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                    class="user-image rounded-circle shadow me-2"
                    alt="User Image"
                    style="width: 25px; height: 25px; object-fit: cover;"
                  />
                @else
                  <div class="user-image rounded-circle shadow bg-primary d-flex align-items-center justify-content-center me-2" 
                       style="width: 25px; height: 25px; font-size: 12px; color: white;">
                    {{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->last_name, 0, 1)) }}
                  </div>
                @endif
                <span class="d-none d-md-inline {{ $nameColorClass }}" style="{{ $nameColorStyle }}">
                  @if($roleIcon)<i class="{{ $roleIcon }} me-1"></i>@endif{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                </span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary text-center">
                  <div class="d-flex flex-column align-items-center">
                    @if(Auth::user()->profile_picture && file_exists(storage_path('app/public/' . Auth::user()->profile_picture)))
                      <img
                        src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                        class="rounded-circle shadow mb-2"
                        alt="User Image"
                        style="width: 90px; height: 90px; object-fit: cover;"
                      />
                    @else
                      <div class="rounded-circle shadow bg-light d-flex align-items-center justify-content-center mb-2" 
                           style="width: 90px; height: 90px; font-size: 36px; color: #007bff;">
                        <i class="fas fa-user"></i>
                      </div>
                    @endif
                    <p class="mb-0">
                      <span class="{{ $nameColorClass }}" style="{{ $nameColorStyle }}">
                        @if($roleIcon)<i class="{{ $roleIcon }} me-1"></i>@endif{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                      </span>
                      -
                      {{ $role }}
                      <small class="d-block">User since {{ Auth::user()->created_at->format('M Y') }}</small>
                    </p>
                  </div>
                </li>
                <!--end::User Image-->
                @if($currentUser->isAdmin() && $currentUser->getFunctionalNavRole())
                  <li><div class="dropdown-divider"></div></li>
                  <li>
                    <form method="POST" action="{{ route('nav_view.switch', session('nav_view', 'role') === 'admin' ? 'role' : 'admin') }}">
                      @csrf
                      <button type="submit" class="dropdown-item">
                        <i class="bi bi-arrow-left-right me-1"></i>
                        @if(session('nav_view', 'role') === 'admin')
                          Switch to {{ ucwords(str_replace('_', ' ', $currentUser->role)) }} View
                        @else
                          Switch to Admin View
                        @endif
                      </button>
                    </form>
                  </li>
                @endif
                <!--begin::Menu Body-->
                {{-- <li class="user-body">
                  <!--begin::Row-->
                  <div class="row">
                    <div class="col-4 text-center">
                      <a href="{{ route('patients.index') }}">
                        <i class="bi bi-people"></i><br>
                        <small>Patients</small>
                      </a>
                    </div>
                    <div class="col-4 text-center">
                      <a href="#">
                        <i class="bi bi-calendar-check"></i><br>
                        <small>Appointments</small>
                      </a>
                    </div>
                    <div class="col-4 text-center">
                      <a href="{{ route('users.index') }}">
                        <i class="bi bi-person-badge"></i><br>
                        <small>Staff</small>
                      </a>
                    </div>
                  </div>
                  <!--end::Row-->
                </li> --}}
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer d-flex flex-column align-items-center">
                  <div class="d-flex justify-content-between w-100 gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat border flex-fill">
                      <i class="bi bi-person-gear me-1"></i>Profile
                    </a>
                    <a href="{{ route('profile.signature.edit') }}" class="btn btn-default btn-flat border flex-fill">
                      <i class="bi bi-pen me-1"></i>My Signature
                    </a>
                  </div>
                  <form method="POST" action="{{ route('logout') }}" class="mt-3 mb-1">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-flat" onclick="return confirm('Are you sure you want to logout?')">
                      <i class="bi bi-box-arrow-right me-1"></i>Sign out
                    </button>
                  </form>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
            @endif
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::App Toolbar-->
        <div class="app-toolbar d-flex gap-2 p-2 bg-dark">
            <button onclick="history.back()" class="btn btn-link p-0 text-white text-decoration-none small hover-light">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <span class="text-secondary small">⟳</span>
            <button onclick="location.reload()" class="btn btn-link p-0 text-white text-decoration-none small hover-light">
                Refresh
            </button>
        </div>

        <!--end::App Toolbar-->
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="{{ url('dashboard')}}" class="brand-link">
            <!--begin::Brand Image-->
            <img
              {{-- $facility is shared globally as a Facility model, but some report views locally
                   override it with a plain array (facility info for the printed header) which
                   shadows the shared value for this layout too — guard against that here. --}}
              src="{{ ($facility instanceof \App\Models\Facility && $facility->logo) ? asset('storage/' . $facility->logo) : asset('images/logo.png') }}"
              alt="Facility Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">Practice 1.0</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="menu"
              data-accordion="false"
            >
              <!-- Role based aside Menu -->
              @php
                $navUser = auth()->user();
                $navFunctionalRole = $navUser?->getFunctionalNavRole();
                $navView = session('nav_view', 'role');
              @endphp
              @if(auth()->check() && $navUser->isAdmin() && $navFunctionalRole && $navView !== 'admin')
                @include('layouts.role_specific.' . $navFunctionalRole)
              @elseif(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                @include('layouts.role_specific.admin')
              @elseif(auth()->check() && auth()->user()->isReceptionist())
                @include('layouts.role_specific.receptionist')
              @elseif(auth()->check() && auth()->user()->isDoctor())
                @include('layouts.role_specific.doctor')
              @elseif(auth()->check() && auth()->user()->isCashier())
                @include('layouts.role_specific.receptionist')
              @elseif(auth()->check() && auth()->user()->isLabTechnician())
                @include('layouts.role_specific.lab_technician')
              @elseif(auth()->check() && auth()->user()->isPharmacist())
                @include('layouts.role_specific.pharmacist')
              @elseif(auth()->check() && auth()->user()->isNurse())
                @include('layouts.role_specific.nurse')
              @elseif(auth()->check() && auth()->user()->isRadiologist())
                @include('layouts.role_specific.radiologist')
              @else
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-exclamation-triangle"></i>
                  <p>Access Denied</p>
                </a>
              </li>
              @endif
              <!--end::Role based aside Menu-->
              <!-- Settings - Admins only -->
              @if(auth()->check() && $navUser->isAdmin())
              <li class="nav-item">
                <a href="{{ url('/settings') }}" class="nav-link">
                  <i class="nav-icon bi bi-gear"></i>
                  <p>Settings</p>
                </a>
              </li>
              @endif
              <!-- Email - Available to all authenticated users -->
              @if(auth()->check() && $navUser->canAccessEmail())
              <li class="nav-item">
                <a href="{{ route('email.index') }}" class="nav-link {{ nav_active_class(['email.*']) }}">
                  <i class="nav-icon bi bi-envelope"></i>
                  <p>Email</p>
                </a>
              </li>
              @endif
              <!-- Help - Available to all authenticated users -->
              <li class="nav-item">
                <a href="{{ url('/help') }}" class="nav-link" target="_blank" rel="noopener">
                  <i class="nav-icon bi bi-question-circle"></i>
                  <p>Help</p>
                </a>
              </li>
              <!-- Logout - shows confirmation modal -->
              <li class="nav-item">
                <a href="#" class="nav-link text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal" id="sidebarLogoutLink">
                  <i class="nav-icon bi bi-box-arrow-right"></i>
                  <p>Logout</p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <div class="app-content">
          <!--begin::Container-->
          @yield("infoboxes")
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-md-12">
                <div class="card mb-2 mt-2">
                  <div class="card-body">
                    <!--begin::Row-->
                    <div class="row">
                      <div class="col-md-12">
                        @yield("main_content")
                      </div>
                    </div>
                    <!--end::Row-->
                  </div>
                  <!-- ./card-body -->
                </div>
                <!-- /.card -->
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2025&nbsp;
          <a href="{{ url('/') }}" class="text-decoration-none">Practice1.0</a>.
        </strong>
         <span>V <strong>0.0.1</strong></span>
        All rights reserved.
        <!--end::Copyright-->
        <!--begin::Extra Footer Content-->
        <div class="float-end d-none d-sm-inline">
          @yield("extra_footer_content")
        </div>
        <!--end::Extra Footer Content-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('dist/js/adminlte.js') }}" defer></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
        
        // Ensure page is fully loaded
        document.body.classList.remove('sidebar-collapse');
        
        // Remove any potential loading overlays
        const loadingElements = document.querySelectorAll('.preloader, .loading-overlay, .overlay');
        loadingElements.forEach(element => {
          element.style.display = 'none';
        });
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!--end::Script-->

    <!-- Select2: focus the search field when a dropdown opens -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        $(document).on('select2:open', function() {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        });
    });
    </script>

    <!-- Page-specific scripts -->
    @yield('scripts')
    @stack('scripts')
  <!-- Page-specific footer scripts (kept separate for big pages) -->
  @yield('footer_scripts')
  @stack('footer_scripts')
  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to logout?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="confirmLogoutBtn" class="btn btn-danger">Logout</button>
        </div>
        <!-- Hidden logout form submitted by the modal -->
        <form id="logoutFormSidebar" method="POST" action="{{ route('logout') }}" style="display:none;">
          @csrf
        </form>
      </div>
    </div>
  </div>

  <!-- Confirmation modal for admin direct 'set user password' actions -->
  <div class="modal fade" id="adminResetConfirmModal" tabindex="-1" aria-labelledby="adminResetConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="adminResetConfirmLabel">Confirm Password Change</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>You're about to set a new password for the user:</p>
          <p><strong id="adminResetTargetEmail">(no email)</strong></p>
          <p class="text-muted small">This action will immediately change the user's password. <br>
            The user may be notified if you selected that option.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmAdminResetBtn">Confirm change</button>
        </div>
      </div>
    </div>
  </div>

  <!--start::Global Modals-->
  <!-- Investigation Results Modal -->
  <div class="modal fade" id="complexResultsModal" tabindex="-1" role="dialog" aria-labelledby="complexResultsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="complexResultsModalLabel">
                      <i class="fas fa-chart-line"></i> Investigation Results
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" id="complexResultsContent" style="max-height: 70vh; overflow-y: auto;">
                  <div class="d-flex justify-content-center">
                      <div class="spinner-border" role="status">
                          <span class="visually-hidden">Loading...</span>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <a href="#" id="printComplexResult" class="btn btn-primary">
                      <i class="fas fa-print"></i> Print Results
                  </a>
              </div>
          </div>
      </div>
  </div>

  <!-- Visit Prescriptions Modal -->
  <div class="modal fade" id="visitPrescriptionsModal" tabindex="-1" role="dialog" aria-labelledby="visitPrescriptionsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="visitPrescriptionsModalLabel">
                      <i class="fas fa-prescription-bottle-alt"></i> Patient Prescriptions
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" id="visitPrescriptionsModalContent" style="max-height: 70vh; overflow-y: auto;">
                  <div class="d-flex justify-content-center">
                      <div class="spinner-border" role="status">
                          <span class="visually-hidden">Loading...</span>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <a href="#" id="printVisitPrescriptions" class="btn btn-primary">
                      <i class="fas fa-print"></i> Print Prescriptions
                  </a>
              </div>
          </div>
      </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const confirmBtn = document.getElementById('confirmLogoutBtn');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
          // Submit the hidden logout form
          const form = document.getElementById('logoutFormSidebar');
          if (form) form.submit();
        });
      }
    });
  </script>
  <!--start::PWA plugin scripts-->
  <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {

            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('ServiceWorker registered:', registration.scope);
                })
                .catch(function(error) {
                    console.log('ServiceWorker registration failed:', error);
                });

        });
    }
  </script>
  <!--end::PWA plugin scripts-->

  <!--start::Global JavaScripts-->
  <script>
    //Function to view all visit results in a modal
    function viewVisitResultsModal(visitId) {
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('complexResultsModal'));
        modal.show();

        // Show loading state
        const contentDiv = document.getElementById('complexResultsContent');
        contentDiv.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading investigation results...</p>
                </div>
            </div>
        `;

        // Update the print button link to generate a visit-level PDF
        document.getElementById('printComplexResult').href = `/lab/visit-results/${visitId}/pdf`;
    
        // Fetch the result details
        fetch(`/lab/visit-results/${visitId}/modal`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch result details');
                }
                return response.text();
            })
            .then(html => {
                contentDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading visit results:', error);
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> Failed to load visit results.
                        <br><small class="text-muted">${error.message}</small>
                    </div>
                `;
            });
    }

    function viewVisitPrescriptionsModal(id) {
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('visitPrescriptionsModal'));
        modal.show();

        // Show loading state
        const contentDiv = document.getElementById('visitPrescriptionsModalContent');
        contentDiv.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading prescription details...</p>
                </div>
            </div>
        `;
        
        // Update print link for prescriptions PDF
        document.getElementById('printVisitPrescriptions').href = `/consultations/${id}/prescriptions-pdf`;

        // Determine URL
        const url = `/consultations/${id}/prescriptions-partial?forModal=true`;
        
        // Make AJAX call to get prescriptions
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#visitPrescriptionsModalContent').html(response.html);
                } else {
                    $('#visitPrescriptionsModalContent').html(`
                        <div class="text-center text-danger py-3">
                            <i class="fas fa-exclamation-triangle"></i> Failed to load prescriptions
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                console.error('Failed to load prescriptions:', xhr);
                $('#visitPrescriptionsModalContent').html(`
                    <div class="text-center text-danger py-3">
                        <i class="fas fa-exclamation-triangle"></i> Error loading prescriptions
                    </div>
                `);
            }
        });
    }
  </script>
  <!--end::Global JavaScripts-->

  <!-- Page-specific modals -->
  @yield('extra_modals')
  </body>
  <!--end::Body-->
</html>
