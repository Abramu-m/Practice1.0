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

@if(auth()->user()->isCashier())
<!-- Salary Payments (HR) -->
<li class="nav-item">
  <a href="{{ route('hr.salary-payments.index') }}" class="nav-link nav-header {{ nav_active_class(['hr.salary-payments.*']) }}">
    <i class="nav-icon bi bi-cash-coin text-success"></i>
    <p class="text-bold">
      Salary Payments
    </p>
  </a>
</li>
@endif

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