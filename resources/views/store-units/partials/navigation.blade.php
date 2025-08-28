<!-- Store Units Navigation Sidebar -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-cubes"></i> Store Management
        </h3>
    </div>
    <div class="card-body p-0">
        <nav class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
            <!-- Store Units -->
            <li class="nav-item">
                <a href="{{ route('store-units.index') }}" class="nav-link {{ request()->routeIs('store-units.*') ? 'active' : '' }}">
                    <i class="fas fa-cubes nav-icon"></i>
                    <p>Store Units</p>
                </a>
            </li>
            
            <!-- Medications -->
            <li class="nav-item">
                <a href="{{ route('medications.index') }}" class="nav-link {{ request()->routeIs('medications.*') ? 'active' : '' }}">
                    <i class="fas fa-pills nav-icon"></i>
                    <p>Medications</p>
                </a>
            </li>
            
            <!-- Medication Units -->
            <li class="nav-item">
                <a href="{{ route('medication-units.index') }}" class="nav-link {{ request()->routeIs('medication-units.*') ? 'active' : '' }}">
                    <i class="fas fa-weight nav-icon"></i>
                    <p>Medication Units</p>
                </a>
            </li>
            
            <!-- GRN -->
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="alert('GRN module - coming soon!')">
                    <i class="fas fa-file-invoice nav-icon"></i>
                    <p>Goods Received Notes</p>
                </a>
            </li>
            
            <!-- Stock -->
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="alert('Stock module - coming soon!')">
                    <i class="fas fa-boxes nav-icon"></i>
                    <p>Stock Management</p>
                </a>
            </li>
            
            <!-- Suppliers -->
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="alert('Suppliers module - coming soon!')">
                    <i class="fas fa-truck nav-icon"></i>
                    <p>Suppliers</p>
                </a>
            </li>
            
            <!-- Reports -->
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    <p>
                        Reports
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="alert('Stock reports - coming soon!')">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Stock Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="alert('Usage reports - coming soon!')">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Usage Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="alert('Expiry reports - coming soon!')">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Expiry Reports</p>
                        </a>
                    </li>
                </ul>
            </li>
        </nav>
    </div>
</div>

<!-- Quick Stats -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-info-circle"></i> Quick Stats
        </h3>
    </div>
    <div class="card-body">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-info elevation-1">
                <i class="fas fa-cubes"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Units</span>
                <span class="info-box-number">{{ \App\Models\StoreUnit::count() }}</span>
            </div>
        </div>
        
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1">
                <i class="fas fa-check-circle"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Active Units</span>
                <span class="info-box-number">{{ \App\Models\StoreUnit::active()->count() }}</span>
            </div>
        </div>
    </div>
</div>
