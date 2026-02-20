<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield("page_title")</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="@yield("page_title")" />
    <meta name="author" content="Abramu Mibaraka" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--end::Font Awesome-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->
    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />
    
    <!-- Include custom CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  <!-- Page-specific styles from views -->
  @yield('styles')

  <!-- Select2 CSS (global) - provides searchable dropdowns across the app -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                <span class="d-none d-md-inline">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
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
                      {{ Auth::user()->first_name }} {{ Auth::user()->last_name }} - 
                      @php
                        if (Auth::user()->isSuperAdmin()) {
                          $role = 'Super Admin';
                        } elseif (Auth::user()->isAdmin()) {
                          $role = 'Admin';
                        } elseif (Auth::user()->isReceptionist()) {
                          $role = 'Receptionist';
                        } elseif (Auth::user()->isDoctor()) {
                          $role = 'Doctor';
                        } elseif (Auth::user()->isCashier()) {
                          $role = 'Cashier';
                        } elseif (Auth::user()->isLabTechnician()) {
                          $role = 'Lab Technician';
                        } elseif (Auth::user()->isPharmacist()) {
                          $role = 'Pharmacist';
                        } elseif (Auth::user()->isNurse()) {
                          $role = 'Nurse';
                        } elseif (Auth::user()->isRadiologist()) {
                          $role = 'Radiologist';
                        } else {
                          $role = Auth::user()->role; // Fallback to the role field
                        }
                      @endphp
                      {{ $role }}
                      <small class="d-block">User since {{ Auth::user()->created_at->format('M Y') }}</small>
                    </p>
                  </div>
                </li>
                <!--end::User Image-->
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
                <li class="user-footer">
                  <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">
                    <i class="bi bi-person-gear me-1"></i>Profile
                  </a>
                  <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-default btn-flat float-end" onclick="return confirm('Are you sure you want to logout?')">
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
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="{{ url('dashboard')}}" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="{{ asset('images/icon.ico') }}"
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
              @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
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
              @elseif(auth()->check() && auth()->user()->isLearning())
                @include('layouts.role_specific.user')
              @else
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-exclamation-triangle"></i>
                  <p>Access Denied</p>
                </a>
              </li>
              @endif
              <!--end::Role based aside Menu-->
              <!-- Settings - Available to all authenticated users -->
              <li class="nav-item">
                <a href="{{ url('/settings') }}" class="nav-link">
                  <i class="nav-icon bi bi-gear"></i>
                  <p>Settings</p>
                </a>
              </li>
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
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>
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
    <!-- OPTIONAL SCRIPTS -->
    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>
    <!--end::Script-->
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS (must come after jQuery). Note: some individual views already include Select2 themselves
      so this global include may result in duplicate loads; consider removing per-view includes if you want a single centralized include. -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Toastr for notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- DataTables (Bootstrap 5 styling) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.0/css/responsive.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Page-specific scripts -->
    @yield('scripts')
  <!-- Page-specific footer scripts (kept separate for big pages) -->
  @yield('footer_scripts')
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

  <!-- Page-specific modals -->
  @yield('extra_modals')
  </body>
  <!--end::Body-->
</html>
