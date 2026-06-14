<!-- HR Dashboard -->
<li class="nav-item">
  <a href="{{ route('hr.dashboard') }}" class="nav-link nav-header {{ nav_active_class(['hr.dashboard']) }}">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      HR Dashboard
    </p>
  </a>
</li>

<!-- Human Resources -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['hr.employees.*', 'hr.salary-payments.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['hr.employees.*', 'hr.salary-payments.*']) }}">
    <i class="nav-icon bi bi-people-fill text-secondary"></i>
    <p class="text-bold">
      Human Resources
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['hr.employees.*', 'hr.salary-payments.*']) }}">
    <li class="nav-item">
      <a href="{{ route('hr.employees.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['hr.employees.*']) }}">
        <i class="nav-icon bi bi-person-badge text-info"></i>
        <p>Employees</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('hr.salary-payments.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['hr.salary-payments.*']) }}">
        <i class="nav-icon bi bi-cash-coin text-success"></i>
        <p>Salary Payments</p>
      </a>
    </li>
  </ul>
</li>
