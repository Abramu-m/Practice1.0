<!-- Doctor Dashboard -->
<li class="nav-item">
  <a href="{{ route('dashboard.doctor') }}" class="nav-link nav-header {{ nav_active_class(['dashboard.doctor']) }}">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Doctor Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>

<!-- Patient Consultations -->
<li class="nav-item">
  <a href="{{ route('patient_visits.index') }}" class="nav-link nav-header {{ nav_active_class(['patient_visits.*']) }}">
    <i class="nav-icon bi bi-calendar-check-fill text-primary"></i>
    <p class="text-bold">
      Patient Consultations
    </p>
  </a>
</li>

<!-- Medical Procedures -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['procedures.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['procedures.*']) }}">
    <i class="nav-icon bi bi-clipboard-plus-fill text-success"></i>
    <p class="text-bold">
      Medical Procedures
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['procedures.*']) }}">
    <li class="nav-item">
      <a href="{{ route('procedures.index', ['filter_type' => 'procedures']) }}" class="nav-link nav-sub-item {{ nav_active_class(['procedures.index']) }}">
        <i class="nav-icon bi bi-clipboard-check-fill text-primary"></i>
        <p>General Procedures</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('procedures.index', ['filter_type' => 'radiology']) }}" class="nav-link nav-sub-item {{ nav_active_class(['procedures.index']) }}">
        <i class="nav-icon bi bi-radioactive text-warning"></i>
        <p>Radiology Investigations</p>
      </a>
    </li>
  </ul>
</li>

<!-- Claims Management -->
<li class="nav-item has-treeview {{ nav_menu_open_class([], ['PostClaim', 'failedClaim']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class([], ['PostClaim', 'failedClaim']) }}">
    <i class="nav-icon bi bi-file-medical-fill text-warning"></i>
    <p class="text-bold">
      Claims Management
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style([], ['PostClaim', 'failedClaim']) }}">
    <li class="nav-item">
      <a href="{{ url('PostClaim') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['PostClaim']) }}">
        <i class="nav-icon bi bi-check-circle-fill text-success"></i>
        <p>Posted Claims</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('failedClaim') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['failedClaim']) }}">
        <i class="nav-icon bi bi-x-circle-fill text-danger"></i>
        <p>Failed Claims</p>
      </a>
    </li>
  </ul>
</li>
<!-- Stock Management -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <i class="nav-icon bi bi-clipboard-data-fill text-info"></i>
    <p class="text-bold">
      Radiology Stock Management
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <li class="nav-item">
      <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store.requisitions.*']) }}">
        <i class="nav-icon bi bi-clipboard-data text-warning"></i>
        <p>Supply Requests</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store-locations-stock.*']) }}">
        <i class="nav-icon bi bi-hospital text-primary"></i>
        <p>Stock Levels</p>
      </a>
    </li>
  </ul>
</li>

<style>
/* Navigation Menu Styling */
.nav-header .nav-link {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #007bff;
    margin: 2px 0;
    border-radius: 0 6px 6px 0;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.nav-header .nav-link:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-left-color: #2196f3;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
}

.nav-sub-item .nav-link {
    margin-left: 20px;
    padding-left: 15px;
    border-left: 2px solid #6c757d;
    transition: all 0.3s ease;
    font-size: 0.85rem;
}

.nav-sub-item .nav-link:hover {
    background: rgba(108,117,125,0.1);
    border-left-color: #495057;
    transform: translateX(2px);
}

.nav-icon {
    margin-right: 8px;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

.text-bold {
    font-weight: 700;
}

.ms-auto {
    margin-left: auto !important;
}

.nav-link.active {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white !important;
    border-left-color: #ffc107;
}

.nav-item.menu-open > .nav-treeview {
    background: linear-gradient(135deg, rgba(0,0,0,0.08) 0%, rgba(0,0,0,0.12) 100%);
    border-radius: 0 8px 8px 0;
    margin-right: 5px;
    padding: 5px 0;
    border-left: 2px solid rgba(0,123,255,0.3);
}
</style>