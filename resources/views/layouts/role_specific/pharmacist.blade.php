<!-- Pharmacist Dashboard -->
<li class="nav-item">
  <a href="{{ route('pharmacist.dashboard') }}" class="nav-link nav-header {{ nav_active_class(['pharmacist.dashboard']) }}">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Pharmacist Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>

<!-- Pharmacy Operations -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['pharmacist.prescriptions.*', 'medication-cash-sales.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['pharmacist.prescriptions.*', 'medication-cash-sales.*']) }}">
    <i class="nav-icon bi bi-capsule-pill text-info"></i>
    <p class="text-bold">
      Pharmacy Department
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['pharmacist.prescriptions.*', 'medication-cash-sales.*']) }}">
    <li class="nav-item">
      <a href="{{ route('medication-cash-sales.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['medication-cash-sales.*']) }}">
        <i class="nav-icon bi bi-cash-stack text-success"></i>
        <p>Cash Sales</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index') }}" class="nav-link nav-sub-item {{ nav_active_query_class(['pharmacist.prescriptions.index'], ['status' => null]) }}">
        <i class="nav-icon bi bi-list-check text-info"></i>
        <p>
          All Prescriptions
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'pending']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['pharmacist.prescriptions.index'], ['status' => 'pending']) }}">
        <i class="nav-icon bi bi-clock-history text-warning"></i>
        <p>
          Pending Prescriptions
          <span class="badge badge-warning right" id="pending-prescriptions-count">0</span>
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'dispensed']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['pharmacist.prescriptions.index'], ['status' => 'dispensed']) }}">
        <i class="nav-icon bi bi-check-circle-fill text-success"></i>
        <p>Dispensed Prescriptions</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'unavailable']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['pharmacist.prescriptions.index'], ['status' => 'unavailable']) }}">
        <i class="nav-icon bi bi-x-circle-fill text-danger"></i>
        <p>Unavailable Items</p>
      </a>
    </li>
  </ul>
</li>

<!-- Stock Management -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
  <a href="#" class="nav-link nav-header {{ nav_active_class(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <i class="nav-icon bi bi-boxes text-warning"></i>
    <p class="text-bold">
      Pharmacy Stock Management
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview" style="{{ nav_display_style(['store.requisitions.*', 'store-locations-stock.*']) }}">
    <li class="nav-item">
      <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store.requisitions.*']) }}">
        <i class="nav-icon bi bi-clipboard-data text-warning"></i>
        <p>Pharmacy Requisitions</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store-locations-stock.*']) }}">
        <i class="nav-icon bi bi-capsule text-primary"></i>
        <p>Pharmacy Stock Levels</p>
      </a>
    </li>
  </ul>
</li>

<script>
// Update pending prescriptions count in sidebar
function updatePendingPrescriptionsCount() {
    fetch('/pharmacist/data?type=pending_count')
        .then(response => response.json())
        .then(data => {
            const countBadge = document.getElementById('pending-prescriptions-count');
            if (countBadge && data.pending_count !== undefined) {
                countBadge.textContent = data.pending_count;
                if (data.pending_count > 0) {
                    countBadge.style.display = 'inline';
                    countBadge.classList.add('pulse');
                } else {
                    countBadge.style.display = 'none';
                    countBadge.classList.remove('pulse');
                }
            }
        })
        .catch(error => {
            console.log('Could not fetch pending prescriptions count:', error);
        });
}

// Update counts on page load and every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const isPharmacyPage = currentPath.includes('/pharmacist') ||
                          currentPath.includes('/prescription') ||
                          currentPath.includes('/dashboard') ||
                          currentPath === '/';
    
    if (isPharmacyPage) {
        updatePendingPrescriptionsCount();
        setInterval(updatePendingPrescriptionsCount, 30000);
    }
});
</script>