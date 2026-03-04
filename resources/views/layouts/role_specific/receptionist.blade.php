<!-- Reception Dashboard -->
<li class="nav-item">
  <a href="{{ route('dashboard.receptionist') }}" class="nav-link nav-header {{ nav_active_class(['dashboard.receptionist']) }}">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Reception Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>

<!-- Reception Desk -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['patient_visits.*'], ['patients', 'patients/*', 'readyInvResults']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['patient_visits.*'], ['patients', 'patients/*', 'readyInvResults']) }}">
    <i class="nav-icon bi bi-person-check-fill text-info"></i>
    <p class="text-bold">
      Reception Desk
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['patient_visits.*'], ['patients', 'patients/*', 'readyInvResults']) }}">
    <li class="nav-item">
      <a href="{{ url('patients') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['patients', 'patients/*']) }}">
        <i class="nav-icon bi bi-person-plus-fill text-success"></i>
        <p>
          Patient Registration
        </p>               
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('patient_visits.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['patient_visits.*']) }}">
        <i class="nav-icon bi bi-calendar-check-fill text-primary"></i>
        <p>
          Patient Visits
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('readyInvResults') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['readyInvResults']) }}">
        <i class="nav-icon bi bi-file-earmark-check text-success"></i>
        <p>Ready Results</p>
      </a>
    </li>
  </ul>
</li>

<!-- Cashier Operations -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['cashier.*', 'financial.*'], ['pharmacy_cash_sales']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['cashier.*', 'financial.*'], ['pharmacy_cash_sales']) }}">
    <i class="nav-icon bi bi-cash-stack text-success"></i>
    <p class="text-bold">
      Cashier Operations
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['cashier.*', 'financial.*'], ['pharmacy_cash_sales']) }}">
    <li class="nav-item">
      <a href="{{ route('cashier.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['cashier.index']) }}">
        <i class="nav-icon bi bi-speedometer2 text-primary"></i>
        <p>
          Cashier Dashboard
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ url('pharmacy_cash_sales') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['pharmacy_cash_sales']) }}">
        <i class="nav-icon bi bi-capsule text-info"></i>
        <p>Cash Sales</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('financial.transactions.create') }}" class="nav-link nav-sub-item {{ nav_active_class(['financial.transactions.*']) }}">
        <i class="nav-icon bi bi-journal-text text-primary"></i>
        <p>Cash Book</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('financial.receipts.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['financial.receipts.index']) }}">
        <i class="nav-icon bi bi-receipt-cutoff text-success"></i>
        <p>Receipt Management</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('financial.receipts.daily.summary') }}" class="nav-link nav-sub-item {{ nav_active_class(['financial.receipts.daily.summary']) }}">
        <i class="nav-icon bi bi-calendar-day text-info"></i>
        <p>Daily Summary</p>
      </a>
    </li>
  </ul>
</li>

<!-- Administrative Tools -->
<li class="nav-item has-treeview {{ nav_menu_open_class([], ['signature1']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class([], ['signature1']) }}">
    <i class="nav-icon bi bi-tools text-secondary"></i>
    <p class="text-bold">
      Administrative Tools
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style([], ['signature1']) }}">
    <li class="nav-item">
      <a href="{{ url('signature1') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['signature1']) }}">
        <i class="nav-icon bi bi-pen text-primary"></i>
        <p>Digital Signature</p>
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