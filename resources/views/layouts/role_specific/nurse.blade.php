<!-- Nurse Dashboard -->
<li class="nav-item">
  <a href="{{ route('dashboard.nurse') }}" class="nav-link nav-header {{ nav_active_class(['dashboard.nurse']) }}">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Nurse Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>

<!-- Triage Center -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['procedures.*'], ['vitals', 'pitc']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['procedures.*'], ['vitals', 'pitc']) }}">
    <i class="nav-icon bi bi-heart-pulse-fill text-danger"></i>
    <p class="text-bold">
      Triage Center
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['procedures.*'], ['vitals', 'pitc']) }}">
    <li class="nav-item">
      <a href="{{ url('vitals') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['vitals']) }}">
        <i class="nav-icon bi bi-activity text-danger"></i>
        <p>
          Vitals Management
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('pitc') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['pitc']) }}">
        <i class="nav-icon bi bi-shield-check text-info"></i>
        <p>PITC Screening</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('procedures.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['procedures.*']) }}">
        <i class="nav-icon bi bi-clipboard-plus text-success"></i>
        <p>Nursing Procedures</p>
      </a>
    </li>
  </ul>
</li>

<!-- CTC Services -->
<li class="nav-item has-treeview {{ nav_menu_open_class([], ['pitc', 'ctc_drug_issue', 'cd4_form_result']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class([], ['pitc', 'ctc_drug_issue', 'cd4_form_result']) }}">
    <i class="nav-icon bi bi-hospital-fill text-purple"></i>
    <p class="text-bold">
      CTC Services
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style([], ['pitc', 'ctc_drug_issue', 'cd4_form_result']) }}">
    <li class="nav-item">
      <a href="{{ url('pitc') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['pitc']) }}">
        <i class="nav-icon bi bi-shield-check text-info"></i>
        <p>PITC Screening</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('ctc_drug_issue') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['ctc_drug_issue']) }}">
        <i class="nav-icon bi bi-capsule-pill text-success"></i>
        <p>Drug Dispensing</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('cd4_form_result') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['cd4_form_result']) }}">
        <i class="nav-icon bi bi-file-medical text-warning"></i>
        <p>CD4 Results</p>
      </a>
    </li>
  </ul>
</li>

<!-- Stock Management -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <i class="nav-icon bi bi-clipboard-data-fill text-info"></i>
    <p class="text-bold">
      Ward Stock Management
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <li class="nav-item">
      <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store.requisitions.*']) }}">
        <i class="nav-icon bi bi-clipboard-data text-warning"></i>
        <p>Ward Supply Requests</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store-locations-stock.*']) }}">
        <i class="nav-icon bi bi-hospital text-primary"></i>
        <p>Ward Stock Levels</p>
      </a>
    </li>
  </ul>
</li>