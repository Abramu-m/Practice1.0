<!-- Lab Dashboard -->
<li class="nav-item">
  <a href="{{ route('dashboard.lab_technician') }}" class="nav-link nav-header {{ nav_active_class(['dashboard.lab_technician']) }}">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Lab Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>
<li class="nav-item">
  <a href="{{ route('procedures.index') }}" class="nav-link nav-header {{ nav_active_class(['procedures.*']) }}">
    <i class="nav-icon bi bi-clipboard-check-fill text-primary"></i>
    <p>
      Procedures
    </p>
  </a>
</li>
<li class="nav-item">
  <a href="{{ route('investigations.index') }}" class="nav-link nav-header {{ nav_active_class(['investigations.*']) }}">
    <i class="nav-icon bi bi-journal-medical text-success"></i>
    <p>Investigations</p>
  </a>
</li>

<!-- Stock Management -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <i class="nav-icon bi bi-box-seam-fill text-warning"></i>
    <p class="text-bold">
      Lab Stock Management
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <li class="nav-item">
      <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store.requisitions.*']) }}">
        <i class="nav-icon bi bi-clipboard-data text-warning"></i>
        <p>Lab Supply Requests</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store-locations-stock.*']) }}">
        <i class="nav-icon bi bi-flask text-primary"></i>
        <p>Lab Stock Levels</p>
      </a>
    </li>
  </ul>
</li>

<!-- Quality Control -->
<li class="nav-item has-treeview {{ nav_menu_open_class([], ['clinical_chemistry_control', 'lab_diary']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class([], ['clinical_chemistry_control', 'lab_diary']) }}">
    <i class="nav-icon bi bi-shield-check text-success"></i>
    <p class="text-bold">
      Quality Control
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style([], ['clinical_chemistry_control', 'lab_diary']) }}">
    <li class="nav-item">
      <a href="{{ url('clinical_chemistry_control') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['clinical_chemistry_control']) }}">
        <i class="nav-icon bi bi-clipboard-check text-warning"></i>
        <p>Clinical Chemistry Controls</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('lab_diary') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['lab_diary']) }}">
        <i class="nav-icon bi bi-journal-text text-success"></i>
        <p>Lab Diary</p>
      </a>
    </li>
  </ul>
</li>

<!-- Specialized Forms -->
<li class="nav-item has-treeview {{ nav_menu_open_class([], ['tb_leprosy_form', 'tb_leprosy_register']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class([], ['tb_leprosy_form', 'tb_leprosy_register']) }}">
    <i class="nav-icon bi bi-file-medical-fill text-danger"></i>
    <p class="text-bold">
      Specialized Forms
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style([], ['tb_leprosy_form', 'tb_leprosy_register']) }}">
    <li class="nav-item">
      <a href="{{ url('tb_leprosy_form') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['tb_leprosy_form']) }}">
        <i class="nav-icon bi bi-file-earmark-medical text-danger"></i>
        <p>TB Leprosy Forms</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('tb_leprosy_register') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['tb_leprosy_register']) }}">
        <i class="nav-icon bi bi-journal-bookmark text-warning"></i>
        <p>TB Leprosy Register</p>
      </a>
    </li>
  </ul>
</li>