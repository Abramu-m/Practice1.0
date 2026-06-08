              <!-- Cashier -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['cashier.*', 'patient_visits.*'], ['patients', 'patient_visits']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['cashier.*', 'patient_visits.*'], ['patients', 'patient_visits']) }}">
                  <i class="nav-icon bi bi-cash-stack text-success"></i>
                  <p class="text-bold">
                    Cashier Operations
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['cashier.*', 'patient_visits.*'], ['patients', 'patient_visits']) }}">
                  <li class="nav-item">
                    <a href="{{ route('cashier.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['cashier.index']) }}">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        Cashier Dashboard
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ url('patients') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['patients']) }}">
                      <i class="nav-icon bi bi-person-lines-fill text-info"></i>
                      <p>Patient Registration</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('patient_visits.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['patient_visits.*']) }}">
                      <i class="nav-icon bi bi-calendar2-week text-warning"></i>
                      <p>Patient Visits</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- Triage -->
              <li class="nav-item has-treeview {{ nav_menu_open_class([], ['vitals']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class([], ['vitals']) }}">
                  <i class="nav-icon bi bi-heart-pulse-fill text-danger"></i>
                  <p class="text-bold">
                    Triage Center
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style([], ['vitals']) }}">
                  <li class="nav-item">
                    <a href="{{ url('vitals') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['vitals']) }}">
                      <i class="nav-icon bi bi-activity text-danger"></i>
                      <p>Vitals Management</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- CTC -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-hospital-fill text-purple"></i>
                  <p class="text-bold">
                    CTC Services
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="display: none;">
                  <!-- CTC submenu items can be added here -->
                </ul>
              </li>

              <!-- Pharmacy -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['pharmacist.*', 'medication-cash-sales.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['pharmacist.*', 'medication-cash-sales.*']) }}">
                  <i class="nav-icon bi bi-capsule-pill text-info"></i>
                  <p class="text-bold">
                    Pharmacy Department
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['pharmacist.*', 'medication-cash-sales.*']) }}">
                  <li class="nav-item">
                    <a href="{{ route('pharmacist.dashboard') }}" class="nav-link nav-sub-item {{ nav_active_class(['pharmacist.dashboard']) }}">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        Pharmacist Dashboard
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('pharmacist.prescriptions.index') }}" class="nav-link nav-sub-item {{ nav_active_query_class(['pharmacist.prescriptions.index'], ['status' => null]) }}">
                      <i class="nav-icon bi bi-list-check text-info"></i>
                      <p>
                        Prescriptions
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('medication-cash-sales.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['medication-cash-sales.*']) }}">
                      <i class="nav-icon bi bi-cash-stack text-success"></i>
                      <p>Cash Sales</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- Laboratory -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['lab.visits.*', 'procedures.*', 'investigations.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['lab.visits.*', 'procedures.*', 'investigations.*']) }}">
                  <i class="nav-icon bi bi-clipboard2-pulse-fill text-success"></i>
                  <p class="text-bold">
                    Lab & Investigations
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['lab.visits.*', 'procedures.*', 'investigations.*']) }}">
                  <!-- Lab Personnel Interface -->
                  <li class="nav-item">
                    <a href="{{ route('lab.visits.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['lab.visits.*']) }}">
                      <i class="nav-icon fas fa-vial text-primary"></i>
                      <p>Lab Personnel Dashboard</p>
                    </a>
                  </li>
                  <!-- Modern Clinical Management -->
                  <li class="nav-item">
                    <a href="{{ route('procedures.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['procedures.*']) }}">
                      <i class="nav-icon bi bi-clipboard-check-fill text-primary"></i>
                      <p>Procedure Results Management</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('investigations.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['investigations.*']) }}">
                      <i class="nav-icon bi bi-journal-medical text-success"></i>
                      <p>Investigation Dashboard</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('investigation-form-records.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['investigation-form-records.*']) }}">
                      <i class="nav-icon bi bi-printer text-success"></i>
                      <p>Investigation Form Records</p>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- Store Manager // New Store Management -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['medications.dashboard', 'medications.stock.*', 'store-units.*', 'medication-units.*', 'store.requisitions.*', 'store-locations.*', 'store-locations-stock.*', 'store-stock-movements.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['medications.dashboard', 'medications.stock.*', 'store-units.*', 'medication-units.*', 'store.requisitions.*', 'store-locations.*', 'store-locations-stock.*', 'store-stock-movements.*']) }}">
                  <i class="nav-icon bi bi-shop-window text-warning"></i>
                  <p class="text-bold">
                    Store Management
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['medications.dashboard', 'medications.stock.*', 'store-units.*', 'medication-units.*', 'store.requisitions.*', 'store-locations.*', 'store-locations-stock.*', 'store-stock-movements.*']) }}">
                  <li class="nav-item">
                    <a href="{{ route('medications.dashboard') }}" class="nav-link nav-sub-item {{ nav_active_class(['medications.dashboard']) }}">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>Primary Dashboard</p>
                    </a>
                  </li>
                  <!-- Procurement -->
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['medications.stock.grn.*', 'medications.stock.suppliers.*', 'store-units.*', 'medication-units.*']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['medications.stock.grn.*', 'medications.stock.suppliers.*', 'store-units.*', 'medication-units.*']) }}">
                      <i class="nav-icon bi bi-cart-plus-fill text-success"></i>
                      <p>
                        Procurement
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['medications.stock.grn.*', 'medications.stock.suppliers.*', 'store-units.*', 'medication-units.*']) }}">
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.grn.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.stock.grn.*']) }}">
                          <i class="nav-icon bi bi-receipt text-info"></i>
                          <p>Goods Received Notes</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.suppliers.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.stock.suppliers.*']) }}">
                          <i class="nav-icon bi bi-building text-warning"></i>
                          <p>Suppliers</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-units.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['store-units.*']) }}">
                          <i class="nav-icon bi bi-rulers text-primary"></i>
                          <p>Store Units</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medication-units.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medication-units.*']) }}">
                          <i class="nav-icon bi bi-calculator text-success"></i>
                          <p>Medications Units</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <!-- Stock Management -->
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['medications.stock.ledger.*', 'store.requisitions.*', 'medications.stock.transfers.*', 'medications.stock.adjustments.*', 'medications.stock.disposal.*', 'store-locations.*', 'store-locations-stock.*', 'store-stock-movements.*']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['medications.stock.ledger.*', 'store.requisitions.*', 'medications.stock.transfers.*', 'medications.stock.adjustments.*', 'medications.stock.disposal.*', 'store-locations.*', 'store-locations-stock.*', 'store-stock-movements.*']) }}">
                      <i class="nav-icon bi bi-boxes text-danger"></i>
                      <p>
                        Stock Management
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['medications.stock.ledger.*', 'store.requisitions.*', 'medications.stock.transfers.*', 'medications.stock.adjustments.*', 'medications.stock.disposal.*', 'store-locations.*', 'store-locations-stock.*', 'store-stock-movements.*']) }}">
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.ledger.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.stock.ledger.*']) }}">
                          <i class="nav-icon bi bi-journal-text text-info"></i>
                          <p>Medication Ledger</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['store.requisitions.*']) }}">
                          <i class="nav-icon bi bi-clipboard-data text-warning"></i>
                          <p>Restocking Requests</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.transfers.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.stock.transfers.*']) }}">
                          <i class="nav-icon bi bi-arrow-left-right text-primary"></i>
                          <p>Stock Transfers</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.adjustments.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.stock.adjustments.*']) }}">
                          <i class="nav-icon bi bi-sliders text-success"></i>
                          <p>Adjustments</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.disposal.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.stock.disposal.*']) }}">
                          <i class="nav-icon bi bi-trash text-danger"></i>
                          <p>Disposal</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-locations.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['store-locations.index']) }}">
                          <i class="nav-icon bi bi-building text-primary"></i>
                          <p>Store Locations</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['store-locations-stock.*']) }}">
                          <i class="nav-icon bi bi-geo-alt text-info"></i>
                          <p>Location Stock</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-stock-movements.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['store-stock-movements.*']) }}">
                          <i class="nav-icon bi bi-arrow-up-down text-warning"></i>
                          <p>Stock Movements</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['medications.consumption.*']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['medications.consumption.*']) }}">
                      <i class="nav-icon bi bi-graph-down text-info"></i>
                      <p>
                        Consumption Tracking
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['medications.consumption.*']) }}">
                      <li class="nav-item">
                        <a href="{{ route('medications.consumption.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.consumption.index']) }}">
                          <i class="nav-icon bi bi-eye text-primary"></i>
                          <p>Overview</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.consumption.prescriptions.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.consumption.prescriptions.*']) }}">
                          <i class="nav-icon bi bi-prescription2 text-success"></i>
                          <p>Prescriptions</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.consumption.analytics') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.consumption.analytics']) }}">
                          <i class="nav-icon bi bi-bar-chart text-warning"></i>
                          <p>Analytics</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['medications.reconciliation.*']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['medications.reconciliation.*']) }}">
                      <i class="nav-icon bi bi-check2-square text-success"></i>
                      <p>
                        Reconciliation
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['medications.reconciliation.*']) }}">
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.reconciliation.index']) }}">
                          <i class="nav-icon bi bi-speedometer text-primary"></i>
                          <p>Dashboard</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.discrepancies') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.reconciliation.discrepancies']) }}">
                          <i class="nav-icon bi bi-exclamation-triangle text-warning"></i>
                          <p>Discrepancies</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.audit') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.reconciliation.audit']) }}">
                          <i class="nav-icon bi bi-clipboard-data text-info"></i>
                          <p>Audit Trail</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.comparison') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.reconciliation.comparison']) }}">
                          <i class="nav-icon bi bi-arrow-left-right text-primary"></i>
                          <p>Stock Comparison</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.corrections.form') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['medications.reconciliation.corrections.*']) }}">
                          <i class="nav-icon bi bi-pencil-square text-danger"></i>
                          <p>Manual Corrections</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>

              <!-- Accounting -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['financial.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['financial.*']) }}">
                  <i class="nav-icon bi bi-calculator-fill text-success"></i>
                  <p class="text-bold">
                    Financial Management
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['financial.*']) }}">
                  <li class="nav-item">
                    <a href="{{ route('financial.dashboard') }}" class="nav-link nav-sub-item {{ nav_active_class(['financial.dashboard']) }}">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        Financial Dashboard
                        <i class="bi bi-graph-up text-success ms-auto"></i>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index') }}" class="nav-link nav-sub-item {{ nav_active_query_class(['financial.transactions.index'], ['transaction_type' => null, 'status' => null]) }}">
                      <i class="nav-icon bi bi-list-task text-info"></i>
                      <p>
                        All Transactions
                        <i class="bi bi-receipt text-muted ms-auto"></i>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.create') }}" class="nav-link nav-sub-item {{ nav_active_class(['financial.transactions.create']) }}">
                      <i class="nav-icon bi bi-plus-circle-fill text-success"></i>
                      <p>New Transaction</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index', ['transaction_type' => 'income']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['financial.transactions.index'], ['transaction_type' => 'income']) }}">
                      <i class="nav-icon bi bi-arrow-up-circle-fill text-success"></i>
                      <p>Income Transactions</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index', ['transaction_type' => 'expense']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['financial.transactions.index'], ['transaction_type' => 'expense']) }}">
                      <i class="nav-icon bi bi-arrow-down-circle-fill text-danger"></i>
                      <p>Expense Transactions</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index', ['status' => 'pending']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['financial.transactions.index'], ['status' => 'pending']) }}">
                      <i class="nav-icon bi bi-clock-history text-warning"></i>
                      <p>
                        Pending Approvals
                        <span class="badge bg-warning right" id="pending-transactions-count">0</span>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.export') }}" class="nav-link nav-sub-item {{ nav_active_class(['financial.transactions.export']) }}">
                      <i class="nav-icon bi bi-download text-primary"></i>
                      <p>Export Reports</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.receipts.index') }}" class="nav-link nav-sub-item {{ nav_active_query_class(['financial.receipts.index'], ['view' => null]) }}">
                      <i class="nav-icon bi bi-receipt-cutoff text-success"></i>
                      <p>Receipt Management</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.receipts.daily.summary') }}" class="nav-link nav-sub-item {{ nav_active_class(['financial.receipts.daily.*']) }}">
                      <i class="nav-icon bi bi-calendar-day text-info"></i>
                      <p>Daily Summary</p>
                    </a>
                  </li>
                </ul>
              </li>

              {{-- Insurances Integration --}}
              @php
                  $otherInsurers = \App\Models\PatientCategory::where('type', 'insurance')
                      ->whereNotNull('tariffs_table')
                      ->where('tariffs_table', '!=', 'nhif_tariffs')
                      ->where('tariffs_table', '!=', '')
                      ->orderBy('description')
                      ->get(['id', 'description']);

                  $otherInsurerActive = fn($catId) =>
                      (request()->routeIs('medical-service-insurance-map.index') ||
                       request()->routeIs('medication-insurance-map.index'))
                      && (string) request()->query('patient_category_id') === (string) $catId;

                  $insurancesIntegrationActive = request()->routeIs('nhif.*')
                      || $otherInsurers->contains(fn($insurer) => $otherInsurerActive($insurer->id));
              @endphp

              <li class="nav-item has-treeview {{ $insurancesIntegrationActive ? 'menu-open' : '' }}">
                <a href="#" class="nav-link nav-header {{ $insurancesIntegrationActive ? 'active' : '' }}">
                  <i class="nav-icon bi bi-shield-check text-primary"></i>
                  <p class="text-bold">
                    Insurances Integration
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ $insurancesIntegrationActive ? 'display: block;' : 'display: none;' }}">
                  <!-- NHIF Integration -->
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['nhif.*']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['nhif.*']) }}">
                      <i class="nav-icon bi bi-shield-check text-primary"></i>
                      <p>
                        NHIF Integration
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['nhif.*']) }}">
                      <li class="nav-item">
                        <a href="{{ route('nhif.verify') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.verify']) }}">
                          <i class="nav-icon bi bi-person-check-fill text-success"></i>
                          <p>
                            Member Verification
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('nhif.tariffs') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.tariffs']) }}">
                          <i class="nav-icon bi bi-download text-info"></i>
                          <p>
                            Sync Tariffs
                            <i class="bi bi-arrow-repeat text-info ms-auto"></i>
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('nhif.claims') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.claims']) }}">
                          <i class="nav-icon bi bi-file-medical text-warning"></i>
                          <p>
                            Claims Management
                            <i class="bi bi-clipboard-check text-warning ms-auto"></i>
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('nhif.reports') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.reports']) }}">
                          <i class="nav-icon bi bi-graph-up text-purple"></i>
                          <p>
                            NHIF Reports
                            <i class="bi bi-bar-chart text-purple ms-auto"></i>
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('nhif.claim-batches.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.claim-batches.index']) }}">
                          <i class="nav-icon bi bi-file-earmark-plus text-success"></i>
                          <p>
                            Claim Batch Register
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('nhif.service-mapping') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.service-mapping']) }}">
                          <i class="nav-icon bi bi-diagram-3 text-info"></i>
                          <p>
                            Service Mapping
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('nhif.medication-mapping') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.medication-mapping']) }}">
                          <i class="nav-icon bi bi-capsule text-warning"></i>
                          <p>
                            Medication Mapping
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('nhif.settings') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['nhif.settings']) }}">
                          <i class="nav-icon bi bi-gear text-secondary"></i>
                          <p>
                            Settings
                          </p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  {{-- Other Insurers (Jubilee, Strategies, Assemble, SHIB, etc.) --}}
                  @foreach($otherInsurers as $insurer)
                  @php $isThisActive = $otherInsurerActive($insurer->id); @endphp
                  <li class="nav-item has-treeview {{ $isThisActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link nav-sub-header {{ $isThisActive ? 'active' : '' }}">
                      <i class="nav-icon bi bi-shield-check text-secondary"></i>
                      <p>
                        {{ $insurer->description }}
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ $isThisActive ? 'display: block' : 'display: none' }}">
                      <li class="nav-item">
                        <a href="{{ route('medical-service-insurance-map.index') }}?patient_category_id={{ $insurer->id }}"
                           class="nav-link nav-sub-sub-item {{ request()->routeIs('medical-service-insurance-map.index') && request()->query('patient_category_id') == $insurer->id ? 'active' : '' }}">
                          <i class="nav-icon bi bi-diagram-3 text-info"></i>
                          <p>Service Mapping</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medication-insurance-map.index') }}?patient_category_id={{ $insurer->id }}"
                           class="nav-link nav-sub-sub-item {{ request()->routeIs('medication-insurance-map.index') && request()->query('patient_category_id') == $insurer->id ? 'active' : '' }}">
                          <i class="nav-icon bi bi-capsule text-warning"></i>
                          <p>Medication Mapping</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  @endforeach
                </ul>
              </li>

              <!-- User Management -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['users.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['users.*']) }}">
                  <i class="nav-icon bi bi-shield-lock-fill text-warning"></i>
                  <p class="text-bold">
                    User Management
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['users.*']) }}">
                  <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link nav-sub-item {{ nav_active_query_class(['users.index'], ['view' => null]) }}">
                      <i class="nav-icon bi bi-people-fill text-primary"></i>
                      <p>
                        All Users
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('users.pending-verification') }}" class="nav-link nav-sub-item {{ nav_active_class(['users.pending-verification']) }}">
                      <i class="nav-icon bi bi-person-exclamation text-warning"></i>
                      <p>
                        Pending Verification
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('users.password.reset') }}" class="nav-link nav-sub-item {{ nav_active_class(['users.password.reset']) }}">
                      <i class="nav-icon bi bi-key-fill text-danger"></i>
                      <p>Reset Password</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-person-fill-gear text-primary"></i>
                      <p>Set User Password</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- System Settings -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['medical_services.*', 'medical-service-insurance-map.*', 'service_categories.*', 'sample_types.*', 'lab.service-consumables.*', 'result-templates.*', 'form-templates.*', 'icd10.*', 'msd-codes.*', 'lab-codes.*', 'medications.index', 'medications.pricing', 'medications.tracer', 'medication-insurance-map.*', 'store-categories.*', 'administration-routes.*'], ['doctors', 'patient_categories', 'visit_types', 'designations', 'consultation_fees']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['medical_services.*', 'medical-service-insurance-map.*', 'service_categories.*', 'sample_types.*', 'lab.service-consumables.*', 'result-templates.*', 'form-templates.*', 'icd10.*', 'msd-codes.*', 'lab-codes.*', 'medications.index', 'medications.pricing', 'medications.tracer', 'medication-insurance-map.*', 'store-categories.*', 'administration-routes.*'], ['doctors', 'patient_categories', 'visit_types', 'designations', 'consultation_fees']) }}">
                  <i class="nav-icon bi bi-gear-fill text-secondary"></i>
                  <p class="text-bold">System Management<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['medical_services.*', 'medical-service-insurance-map.*', 'service_categories.*', 'sample_types.*', 'lab.service-consumables.*', 'result-templates.*', 'form-templates.*', 'icd10.*', 'msd-codes.*', 'lab-codes.*', 'medications.index', 'medications.pricing', 'medications.tracer', 'medication-insurance-map.*', 'store-categories.*', 'administration-routes.*'], ['doctors', 'patient_categories', 'visit_types', 'designations', 'consultation_fees']) }}">
                  <li class="nav-item has-treeview {{ nav_menu_open_class([], ['doctors', 'patient_categories', 'visit_types', 'designations', 'consultation_fees']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class([], ['doctors', 'patient_categories', 'visit_types', 'designations', 'consultation_fees']) }}">
                      <i class="nav-icon bi bi-hospital text-info"></i>
                      <p>
                        Consultations
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style([], ['doctors', 'patient_categories', 'visit_types', 'designations', 'consultation_fees']) }}">
                      <li class="nav-item">
                        <a href="{{ url('doctors') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['doctors', 'doctors/*']) }}">
                          <i class="nav-icon bi bi-person-badge text-primary"></i>
                          <p>
                            Doctors
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('patient_categories') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['patient_categories', 'patient_categories/*']) }}">
                          <i class="nav-icon bi bi-tags text-warning"></i>
                          <p>
                            Patient Categories
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('visit_types') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['visit_types', 'visit_types/*']) }}">
                          <i class="nav-icon bi bi-calendar-event text-info"></i>
                          <p>
                            Visit Types 
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('designations') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['designations', 'designations/*']) }}">
                          <i class="nav-icon bi bi-award text-purple"></i>
                          <p>
                            Designations
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('consultation_fees') }}" class="nav-link nav-sub-item {{ nav_active_class([], ['consultation_fees', 'consultation_fees/*']) }}">
                          <i class="nav-icon bi bi-cash-coin text-success"></i>
                          <p>
                            Consultation Fees
                          </p>
                        </a>
                      </li>
                  </ul>
                  </li>
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['medical_services.*', 'medical-service-insurance-map.*', 'service_categories.*', 'sample_types.*', 'lab.service-consumables.*', 'result-templates.*', 'form-templates.*']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['medical_services.*', 'medical-service-insurance-map.*', 'service_categories.*', 'sample_types.*', 'lab.service-consumables.*', 'result-templates.*', 'form-templates.*']) }}">
                      <i class="nav-icon bi bi-heart-pulse text-danger"></i>
                      <p>
                        Medical Services
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['medical_services.*', 'medical-service-insurance-map.*', 'service_categories.*', 'sample_types.*', 'lab.service-consumables.*', 'result-templates.*', 'form-templates.*']) }}">
                      <li class="nav-item">
                        <a href="{{ route('medical_services.index') }}" class="nav-link nav-sub-item {{ nav_active_query_class(['medical_services.index'], ['category' => null]) }}">
                          <i class="nav-icon bi bi-speedometer text-primary"></i>
                          <p>All Medical Services</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medical_services.index', ['category' => 'investigations']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['medical_services.index'], ['category' => 'investigations']) }}">
                          <i class="nav-icon bi bi-search text-info"></i>
                          <p>Investigations</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medical_services.index', ['category' => 'procedures']) }}" class="nav-link nav-sub-item {{ nav_active_query_class(['medical_services.index'], ['category' => 'procedures']) }}">
                          <i class="nav-icon bi bi-clipboard-plus text-success"></i>
                          <p>Medical Procedures</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medical_services.pricing') }}" class="nav-link nav-sub-item {{ nav_active_class(['medical_services.pricing']) }}">
                          <i class="nav-icon bi bi-tag-fill text-success"></i>
                          <p>Selling Prices</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medical-service-insurance-map.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['medical-service-insurance-map.*']) }}">
                          <i class="nav-icon bi bi-cash-coin text-success"></i>
                          <p>Service Insurance Map</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('service_categories.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['service_categories.*']) }}">
                          <i class="nav-icon bi bi-tags-fill text-success"></i>
                          <p>Service Categories</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('sample_types.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['sample_types.*']) }}">
                          <i class="nav-icon bi bi-droplet-fill text-warning"></i>
                          <p>Sample Types</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('lab.service-consumables.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['lab.service-consumables.*']) }}">
                          <i class="nav-icon bi bi-clipboard2-data text-info"></i>
                          <p>Service Consumable Templates</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('result-templates.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['result-templates.*']) }}">
                          <i class="nav-icon bi bi-file-medical text-primary"></i>
                          <p>Result Templates</p>
                        </a>
                      </li>

                      <li class="nav-item">
                        <a href="{{ route('investigation-forms.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['investigation-forms.*']) }}">
                          <i class="nav-icon bi bi-clipboard2-check text-primary"></i>
                          <p>Investigation Forms</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['medications.index', 'medications.pricing', 'medications.tracer', 'medication-insurance-map.*', 'store-categories.*', 'administration-routes.*']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['medications.index', 'medications.pricing', 'medications.tracer', 'medication-insurance-map.*', 'store-categories.*', 'administration-routes.*']) }}">
                      <i class="nav-icon bi bi-box-seam-fill text-info"></i>
                      <p>
                        Items Management
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['medications.index', 'medications.pricing', 'medications.tracer', 'medication-insurance-map.*', 'store-categories.*', 'administration-routes.*']) }}">
                      <li class="nav-item">
                        <a href="{{ route('medications.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['medications.index']) }}">
                          <i class="nav-icon bi bi-capsule text-primary"></i>
                          <p>Medications/Items</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.pricing') }}" class="nav-link nav-sub-item {{ nav_active_class(['medications.pricing']) }}">
                          <i class="nav-icon bi bi-tag-fill text-success"></i>
                          <p>Selling Prices</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.tracer') }}" class="nav-link nav-sub-item {{ nav_active_class(['medications.tracer']) }}">
                          <i class="nav-icon bi bi-star-fill text-warning"></i>
                          <p>Tracer Medicines</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medication-insurance-map.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['medication-insurance-map.*']) }}">
                          <i class="nav-icon bi bi-cash-coin text-success"></i>
                          <p>Medication Insurance Map</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-categories.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['store-categories.*']) }}">
                          <i class="nav-icon bi bi-tags text-warning"></i>
                          <p>Categories</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('administration-routes.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['administration-routes.*']) }}">
                          <i class="nav-icon bi bi-road text-info"></i>
                          <p>Administration Routes</p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  <li class="nav-item has-treeview {{ nav_menu_open_class(['icd10.*', 'msd-codes.*', 'lab-codes.*', 'settings.index']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['icd10.*', 'msd-codes.*', 'lab-codes.*', 'settings.index']) }}">
                      <i class="nav-icon bi bi-gear-fill text-danger"></i>
                      <p>
                        Configure reports
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['icd10.*', 'msd-codes.*', 'lab-codes.*', 'settings.index']) }}">
                      <li class="nav-item">
                          <a href="{{ route('icd10.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['icd10.*']) }}">
                            <i class="nav-icon bi bi-gear-fill text-secondary"></i>
                            <p>Mtuha</p>
                          </a>
                      </li>
                      <li class="nav-item">
                          <a href="{{ route('msd-codes.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['msd-codes.*']) }}">
                            <i class="nav-icon bi bi-capsule text-secondary"></i>
                            <p>MSD Item Codes</p>
                          </a>
                      </li>
                      <li class="nav-item">
                          <a href="{{ route('lab-codes.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['lab-codes.*']) }}">
                            <i class="nav-icon bi bi-clipboard2-pulse text-secondary"></i>
                            <p>LOINC / SNOMED Codes</p>
                          </a>
                      </li>
                      <li class="nav-item">
                          <a href="{{ route('settings.index') }}#report-config" class="nav-link nav-sub-item">
                            <i class="nav-icon bi bi-virus text-danger"></i>
                            <p>Malaria Vipimo</p>
                          </a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>

              <!-- Clinical Decision Support (CDS) -->
              @php
                $cdsMedSafetyOpen = request()->routeIs(
                    'admin.cds.medication-policies.*',
                    'admin.cds.drug-interactions.*',
                    'admin.cds.allergy-checks.*',
                    'admin.cds.dose-range-rules.*'
                ) || (
                    request()->routeIs('admin.cds.rules.index') && request()->query('category') === 'medication'
                );
                $cdsLabOpen = request()->routeIs('admin.cds.rules.index') && request()->query('category') === 'laboratory';
                $cdsWorkflowOpen = request()->routeIs('admin.cds.rules.index') && (
                    request()->query('category') === 'workflow' ||
                    in_array(request()->query('type'), ['order_set', 'guideline_prompt'])
                );
                $cdsConfigOpen = request()->routeIs('admin.cds.test-patients.*') || (
                    request()->routeIs('admin.cds.rules.index') && (
                        in_array(request()->query('view'), ['categories', 'types']) ||
                        request()->query('sort') === 'priority'
                    )
                );
              @endphp
              <li class="nav-item has-treeview {{ nav_menu_open_class(['admin.cds.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['admin.cds.*']) }}">
                  <i class="nav-icon bi bi-lightbulb-fill text-warning"></i>
                  <p class="text-bold">
                    Clinical Decision Support
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['admin.cds.*']) }}">
                  <!-- CDS Dashboard -->
                  <li class="nav-item">
                    <a href="{{ route('admin.cds.dashboard') }}" class="nav-link nav-sub-item {{ nav_active_class(['admin.cds.dashboard']) }}">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        CDS Dashboard
                        <i class="bi bi-graph-up text-success ms-auto"></i>
                      </p>
                    </a>
                  </li>

              @php
                $cdsRulesOpen = request()->routeIs('admin.cds.rules.create', 'admin.cds.rules.edit', 'admin.cds.rules.show') || (
                    request()->routeIs('admin.cds.rules.index') &&
                    !$cdsMedSafetyOpen && !$cdsLabOpen && !$cdsWorkflowOpen && !$cdsConfigOpen
                );
              @endphp

                  <!-- Medication Safety -->
                  <li class="nav-item has-treeview {{ $cdsMedSafetyOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link nav-sub-header {{ $cdsMedSafetyOpen ? 'active' : '' }}">
                      <i class="nav-icon bi bi-shield-fill-check text-success"></i>
                      <p>
                        Medication Safety
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ $cdsMedSafetyOpen ? 'display:block' : 'display:none' }}">
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.medication-policies.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.cds.medication-policies.*']) }}">
                          <i class="nav-icon bi bi-file-medical text-primary"></i>
                          <p>Medication Policies</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.drug-interactions.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.cds.drug-interactions.*']) }}">
                          <i class="nav-icon bi bi-capsule text-info"></i>
                          <p>Drug Interaction Rules</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.allergy-checks.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.cds.allergy-checks.*']) }}">
                          <i class="nav-icon bi bi-exclamation-triangle text-warning"></i>
                          <p>Allergy Checks</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.dose-range-rules.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.cds.dose-range-rules.*']) }}">
                          <i class="nav-icon bi bi-calculator text-success"></i>
                          <p>Dose Range Rules</p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  <!-- Lab & Diagnostics -->
                  <li class="nav-item has-treeview {{ $cdsLabOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link nav-sub-header {{ $cdsLabOpen ? 'active' : '' }}">
                      <i class="nav-icon bi bi-clipboard2-pulse text-danger"></i>
                      <p>
                        Lab & Diagnostics
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ $cdsLabOpen ? 'display:block' : 'display:none' }}">
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.rules.index', ['category' => 'laboratory']) }}" class="nav-link nav-sub-sub-item {{ nav_active_query_class(['admin.cds.rules.index'], ['category' => 'laboratory', 'type' => null]) }}">
                          <i class="nav-icon bi bi-vial text-primary"></i>
                          <p>Lab Result Rules</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.rules.index', ['category' => 'laboratory', 'type' => 'critical_value']) }}" class="nav-link nav-sub-sub-item {{ nav_active_query_class(['admin.cds.rules.index'], ['category' => 'laboratory', 'type' => 'critical_value']) }}">
                          <i class="nav-icon bi bi-exclamation-octagon text-danger"></i>
                          <p>Critical Value Alerts</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.rules.index', ['category' => 'laboratory', 'type' => 'follow_up']) }}" class="nav-link nav-sub-sub-item {{ nav_active_query_class(['admin.cds.rules.index'], ['category' => 'laboratory', 'type' => 'follow_up']) }}">
                          <i class="nav-icon bi bi-arrow-repeat text-info"></i>
                          <p>Follow-up Recommendations</p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  <!-- Clinical Workflows -->
                  <li class="nav-item has-treeview {{ $cdsWorkflowOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link nav-sub-header {{ $cdsWorkflowOpen ? 'active' : '' }}">
                      <i class="nav-icon bi bi-diagram-3 text-purple"></i>
                      <p>
                        Clinical Workflows
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ $cdsWorkflowOpen ? 'display:block' : 'display:none' }}">
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.rules.index', ['category' => 'workflow']) }}" class="nav-link nav-sub-sub-item {{ nav_active_query_class(['admin.cds.rules.index'], ['category' => 'workflow', 'type' => null]) }}">
                          <i class="nav-icon bi bi-flow-chart text-primary"></i>
                          <p>All Workflow Rules</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.rules.index', ['type' => 'order_set']) }}" class="nav-link nav-sub-sub-item {{ nav_active_query_class(['admin.cds.rules.index'], ['type' => 'order_set']) }}">
                          <i class="nav-icon bi bi-list-task text-success"></i>
                          <p>Order Sets</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.rules.index', ['type' => 'guideline_prompt']) }}" class="nav-link nav-sub-sub-item {{ nav_active_query_class(['admin.cds.rules.index'], ['type' => 'guideline_prompt']) }}">
                          <i class="nav-icon bi bi-book text-info"></i>
                          <p>Clinical Guidelines</p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  <!-- System Configuration -->
                  <li class="nav-item has-treeview {{ $cdsConfigOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link nav-sub-header {{ $cdsConfigOpen ? 'active' : '' }}">
                      <i class="nav-icon bi bi-gear-fill text-secondary"></i>
                      <p>
                        Configuration
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ $cdsConfigOpen ? 'display:block' : 'display:none' }}">
                      <li class="nav-item">
                        <a href="{{ route('admin.cds.test-patients.index') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.cds.test-patients.index']) }}">
                          <i class="nav-icon bi bi-person-fill-gear text-danger"></i>
                          <p>Test Patients</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>

              <!-- Reports & Analytics -->
              <li class="nav-item has-treeview {{ nav_menu_open_class(['reports.mtuha.*', 'admin.reports.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['reports.mtuha.*', 'admin.reports.*']) }}">
                  <i class="nav-icon bi bi-file-earmark-text-fill text-info"></i>
                  <p class="text-bold">
                    Reports & Analytics
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['reports.mtuha.*', 'admin.reports.*']) }}">
                  <!-- Admin Reports Dashboard -->
                  <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['admin.reports.index']) }}">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>Reports Dashboard</p>
                    </a>
                  </li>

                  <!-- Disease Surveillance Reports -->
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['admin.reports.idsr-weekly', 'admin.reports.std-sti-monthly', 'admin.reports.dtc-monthly']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['admin.reports.idsr-weekly', 'admin.reports.std-sti-monthly', 'admin.reports.dtc-monthly']) }}">
                      <i class="nav-icon bi bi-virus text-danger"></i>
                      <p>
                        Disease Surveillance
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['admin.reports.idsr-weekly', 'admin.reports.std-sti-monthly', 'admin.reports.dtc-monthly']) }}">
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.idsr-weekly') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.idsr-weekly']) }}">
                          <i class="nav-icon bi bi-graph-up text-warning"></i>
                          <p>IDSR Weekly</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.std-sti-monthly') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.std-sti-monthly']) }}">
                          <i class="nav-icon bi bi-exclamation-triangle text-warning"></i>
                          <p>STI/STD Monthly</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.dtc-monthly') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.dtc-monthly']) }}">
                          <i class="nav-icon bi bi-heart-pulse text-info"></i>
                          <p>DTC Monthly</p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  <!-- Pharmacy & Inventory Reports -->
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['admin.reports.medicines-monthly', 'admin.reports.tracer-medicines', 'admin.reports.low-stock-medicines']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['admin.reports.medicines-monthly', 'admin.reports.tracer-medicines', 'admin.reports.low-stock-medicines']) }}">
                      <i class="nav-icon bi bi-capsule-pill text-success"></i>
                      <p>
                        Pharmacy & Inventory
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['admin.reports.medicines-monthly', 'admin.reports.tracer-medicines', 'admin.reports.low-stock-medicines']) }}">
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.medicines-monthly') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.medicines-monthly']) }}">
                          <i class="nav-icon bi bi-pill text-info"></i>
                          <p>Medicines Monthly</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.tracer-medicines') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.tracer-medicines']) }}">
                          <i class="nav-icon bi bi-check-circle text-success"></i>
                          <p>Tracer Medicines</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.low-stock-medicines') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.low-stock-medicines']) }}">
                          <i class="nav-icon bi bi-exclamation-circle text-danger"></i>
                          <p>Low Stock Alert</p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  <!-- Monthly Lab Reports -->
                  <li class="nav-item has-treeview {{ nav_menu_open_class(['admin.reports.monthly-lab-reports', 'admin.reports.lab-hematology', 'admin.reports.lab-blood-transfusion', 'admin.reports.lab-clinical-chemistry', 'admin.reports.lab-microbiology', 'admin.reports.lab-serology', 'admin.reports.lab-parasitology', 'admin.reports.malaria-vipimo']) }}">
                    <a href="#" class="nav-link nav-sub-header {{ nav_active_class(['admin.reports.monthly-lab-reports', 'admin.reports.lab-hematology', 'admin.reports.lab-blood-transfusion', 'admin.reports.lab-clinical-chemistry', 'admin.reports.lab-microbiology', 'admin.reports.lab-serology', 'admin.reports.lab-parasitology', 'admin.reports.malaria-vipimo']) }}">
                      <i class="nav-icon bi bi-flask text-primary"></i>
                      <p>
                        Monthly Lab Reports
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview" style="{{ nav_display_style(['admin.reports.monthly-lab-reports', 'admin.reports.lab-hematology', 'admin.reports.lab-blood-transfusion', 'admin.reports.lab-clinical-chemistry', 'admin.reports.lab-microbiology', 'admin.reports.lab-serology', 'admin.reports.lab-parasitology', 'admin.reports.malaria-vipimo']) }}">
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.monthly-lab-reports') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.monthly-lab-reports']) }}">
                          <i class="nav-icon bi bi-flask-fill text-info"></i>
                          <p>All Lab Tests</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.malaria-vipimo') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.malaria-vipimo']) }}">
                          <i class="nav-icon bi bi-virus text-danger"></i>
                          <p>Malaria Vipimo</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.lab-hematology') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.lab-hematology']) }}">
                          <i class="nav-icon bi bi-droplet text-danger"></i>
                          <p>Hematology</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.lab-blood-transfusion') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.lab-blood-transfusion']) }}">
                          <i class="nav-icon bi bi-droplets text-danger"></i>
                          <p>Blood Transfusion</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.lab-clinical-chemistry') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.lab-clinical-chemistry']) }}">
                          <i class="nav-icon bi bi-vial text-info"></i>
                          <p>Clinical Chemistry</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.lab-microbiology') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.lab-microbiology']) }}">
                          <i class="nav-icon bi bi-bacteria text-success"></i>
                          <p>Microbiology</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.lab-serology') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.lab-serology']) }}">
                          <i class="nav-icon bi bi-virus2 text-warning"></i>
                          <p>Serology</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('admin.reports.lab-parasitology') }}" class="nav-link nav-sub-sub-item {{ nav_active_class(['admin.reports.lab-parasitology']) }}">
                          <i class="nav-icon bi bi-bug text-secondary"></i>
                          <p>Parasitology</p>
                        </a>
                      </li>
                    </ul>
                  </li>

                  <!-- Legacy Reports -->
                  <li class="nav-item">
                    <a href="{{ route('reports.mtuha.select') }}" class="nav-link nav-sub-item {{ nav_active_class(['reports.mtuha.*']) }}">
                      <i class="nav-icon bi bi-list-columns-reverse text-secondary"></i>
                      <p>MTUHA Monthly (Legacy)</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item has-treeview {{ nav_menu_open_class(['system.logs.*']) }}">
                <a href="#" class="nav-link nav-header {{ nav_active_class(['system.logs.*']) }}">
                  <i class="nav-icon bi bi-file-earmark-text-fill text-secondary"></i>
                  <p class="text-bold">
                    Logs
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview" style="{{ nav_display_style(['system.logs.*']) }}">
                  <li class="nav-item">
                    <a href="{{ route('system.logs.index') }}" class="nav-link nav-sub-item {{ nav_active_class(['system.logs.*']) }}">
                      <i class="nav-icon bi bi-list-columns-reverse text-primary"></i>
                      <p>All logs</p>
                    </a>
                  </li>
                </ul>
              </li>

<script>
// Update pending transactions count in sidebar
function updatePendingTransactionsCount() {
    fetch('/financial/data?type=pending_count')
        .then(response => response.json())
        .then(data => {
            const countBadge = document.getElementById('pending-transactions-count');
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
            console.log('Could not fetch pending transactions count:', error);
        });
}

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
    // Only run on relevant pages
    const currentPath = window.location.pathname;
    const isFinancialPage = currentPath.includes('/financial') || 
                           currentPath.includes('/dashboard') || 
                           currentPath.includes('/billing') ||
                           currentPath === '/';
    
    const isPharmacyPage = currentPath.includes('/pharmacist') ||
                          currentPath.includes('/prescription') ||
                          currentPath.includes('/dashboard') ||
                          currentPath === '/';
    
    if (isFinancialPage) {
        updatePendingTransactionsCount();
        setInterval(updatePendingTransactionsCount, 30000);
    }
    
    if (isPharmacyPage) {
        updatePendingPrescriptionsCount();
        setInterval(updatePendingPrescriptionsCount, 30000);
    }
});
</script>

<script>
// Intercept admin direct-reset password forms and show confirmation modal.
document.addEventListener('DOMContentLoaded', function() {
  // Find forms that are used for admin direct resets.
  // Heuristic: form action contains '/reset-password' and the form has a password input.
  const resetForms = Array.from(document.querySelectorAll('form[action*="/reset-password"]'))
    .filter(f => f.querySelector('input[name="password"]') || f.querySelector('input[type="password"]'));

  if (!resetForms.length) return;

  // Bootstrap modal instance (if bootstrap is present)
  let bsModal = null;
  const modalEl = document.getElementById('adminResetConfirmModal');
  if (window.bootstrap && modalEl) {
    bsModal = new bootstrap.Modal(modalEl);
  }

  let currentForm = null;

  function showModalForForm(form) {
    currentForm = form;
    // Try to fetch email from common field names
    const emailField = form.querySelector('input[name="email"]') || form.querySelector('input[name="user_email"]') || form.querySelector('input[type="email"]');
    const email = emailField ? emailField.value : '(unknown)';
    const targetSpan = document.getElementById('adminResetTargetEmail');
    if (targetSpan) targetSpan.textContent = email;
    if (bsModal) {
      bsModal.show();
    } else {
      // Fallback to native confirm
      const ok = window.confirm('Confirm setting a new password for ' + email + '?');
      if (ok) form.submit();
    }
  }

  resetForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      // If modal already open and confirm pressed, allow submit
      if (form.dataset.confirmed === '1') {
        // reset flag for future submissions
        form.dataset.confirmed = '0';
        return true;
      }
      e.preventDefault();
      showModalForForm(form);
    });
  });

  const confirmBtn = document.getElementById('confirmAdminResetBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function() {
      if (!currentForm) return;
      // mark form as confirmed to bypass the handler
      currentForm.dataset.confirmed = '1';
      // hide modal first (if present) then submit
      if (bsModal) {
        bsModal.hide();
        // small timeout to allow modal hide animation
        setTimeout(() => currentForm.submit(), 200);
      } else {
        currentForm.submit();
      }
    });
  }
});
</script>
