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
<li class="nav-item">
  <a href="{{ route('lab.visits.index') }}" class="nav-link nav-header {{ nav_active_class(['lab.visits.*']) }}">
    <i class="nav-icon bi bi-clipboard-plus-fill text-success"></i>
    <p class="text-bold">
      Pending Investigations
    </p>
  </a>
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