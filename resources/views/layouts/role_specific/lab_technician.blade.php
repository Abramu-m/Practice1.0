<!-- Lab Dashboard -->
<li class="nav-item">
  <a href="{{ route('dashboard.lab_technician') }}" class="nav-link nav-header">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Lab Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>
<li class="nav-item">
  <a href="{{ route('procedures.index') }}" class="nav-link nav-header{{ request()->routeIs('procedures.*') ? 'active' : '' }}">
    <i class="nav-icon bi bi-clipboard-check-fill text-primary"></i>
    <p>
      Procedures
    </p>
  </a>
</li>
<li class="nav-item">
  <a href="{{ route('investigations.index') }}" class="nav-link nav-header">
    <i class="nav-icon bi bi-journal-medical text-success"></i>
    <p>Investigations</p>
  </a>
</li>

<!-- Stock Management -->
<li class="nav-item has-treeview">
  <a href="#" class="nav-link nav-header">
    <i class="nav-icon bi bi-box-seam-fill text-warning"></i>
    <p class="text-bold">
      Lab Stock Management
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-clipboard-data text-warning"></i>
        <p>Lab Supply Requests</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-flask text-primary"></i>
        <p>Lab Stock Levels</p>
      </a>
    </li>
  </ul>
</li>

<!-- Quality Control -->
<li class="nav-item has-treeview">
  <a href="#" class="nav-link nav-header">
    <i class="nav-icon bi bi-shield-check text-success"></i>
    <p class="text-bold">
      Quality Control
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ url('clinical_chemistry_control') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-clipboard-check text-warning"></i>
        <p>Clinical Chemistry Controls</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('lab_diary') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-journal-text text-success"></i>
        <p>Lab Diary</p>
      </a>
    </li>
  </ul>
</li>

<!-- Specialized Forms -->
<li class="nav-item has-treeview">
  <a href="#" class="nav-link nav-header">
    <i class="nav-icon bi bi-file-medical-fill text-danger"></i>
    <p class="text-bold">
      Specialized Forms
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ url('tb_leprosy_form') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-file-earmark-medical text-danger"></i>
        <p>TB Leprosy Forms</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('tb_leprosy_register') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-journal-bookmark text-warning"></i>
        <p>TB Leprosy Register</p>
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