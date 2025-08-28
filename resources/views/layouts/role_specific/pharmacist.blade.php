<!-- Pharmacist Dashboard -->
<li class="nav-item">
  <a href="{{ route('pharmacist.dashboard') }}" class="nav-link nav-header">
    <i class="nav-icon bi bi-speedometer2 text-primary"></i>
    <p class="text-bold">
      Pharmacist Dashboard
      <i class="bi bi-house-fill text-success ms-auto"></i>
    </p>
  </a>
</li>

<!-- Pharmacy Operations -->
<li class="nav-item has-treeview">
  <a href="#" class="nav-link nav-header">
    <i class="nav-icon bi bi-capsule-pill text-info"></i>
    <p class="text-bold">
      Pharmacy Department
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ route('medication-cash-sales.index') }}" class="nav-link nav-sub-item {{ request()->routeIs('medication-cash-sales.*') ? 'active' : '' }}">
        <i class="nav-icon bi bi-cash-stack text-success"></i>
        <p>Cash Sales</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-list-check text-info"></i>
        <p>
          All Prescriptions
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'pending']) }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-clock-history text-warning"></i>
        <p>
          Pending Prescriptions
          <span class="badge badge-warning right" id="pending-prescriptions-count">0</span>
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'dispensed']) }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-check-circle-fill text-success"></i>
        <p>Dispensed Prescriptions</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'unavailable']) }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-x-circle-fill text-danger"></i>
        <p>Unavailable Items</p>
      </a>
    </li>
  </ul>
</li>

<!-- Stock Management -->
<li class="nav-item has-treeview">
  <a href="#" class="nav-link nav-header">
    <i class="nav-icon bi bi-boxes text-warning"></i>
    <p class="text-bold">
      Pharmacy Stock Management
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-clipboard-data text-warning"></i>
        <p>Pharmacy Requisitions</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-item">
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

.badge.right {
    margin-left: auto;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
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