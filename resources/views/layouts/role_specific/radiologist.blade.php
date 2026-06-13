<!-- Doctor Dashboard -->
<li class="nav-item">
  <a href="{{ route('dashboard') }}" class="nav-link nav-header {{ nav_active_class(['dashboard']) }}">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>

<!-- Medical Procedures -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['lab.visits.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['lab.visits.*']) }}">
    <i class="nav-icon bi bi-clipboard-plus-fill text-success"></i>
    <p class="text-bold">
      Medical Procedures
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['lab.visits.*']) }}">
    <li class="nav-item">
      <a href="{{ route('lab.visits.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['lab.visits.*']) }}">
        <i class="nav-icon bi bi-radioactive text-warning"></i>
        <p>Pending Investigations</p>
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