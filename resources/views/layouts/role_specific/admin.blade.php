<!-- Receptionist -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-person-check-fill text-info"></i>
                  <p class="text-bold">
                    Reception Desk
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ url('patients') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-person-plus-fill text-success"></i>
                      <p>
                        Patient Registration
                      </p>               
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('patient_visits.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-calendar-check-fill text-primary"></i>
                      <p>
                        Patient Visits
                      </p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- Cashier -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-cash-stack text-success"></i>
                  <p class="text-bold">
                    Cashier Operations
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cashier.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        Cashier Dashboard
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ url('patients') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-person-lines-fill text-info"></i>
                      <p>Patient Registration</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('patient_visits.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-calendar2-week text-warning"></i>
                      <p>Patient Visits</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- Triage -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-heart-pulse-fill text-danger"></i>
                  <p class="text-bold">
                    Triage Center
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ url('vitals') }}" class="nav-link nav-sub-item">
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
                <ul class="nav nav-treeview">
                  <!-- CTC submenu items can be added here -->
                </ul>
              </li>

              <!-- Doctor -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-person-badge-fill text-primary"></i>
                  <p class="text-bold">
                    Doctor Portal
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ url('patient_visits') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-clipboard2-pulse-fill text-success"></i>
                      <p>Consultation</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- Pharmacy -->
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
                    <a href="{{ route('pharmacist.dashboard') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        Pharmacist Dashboard
                      </p>
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
                  <li class="nav-item">
                    <a href="{{ route('medication-cash-sales.index') }}" class="nav-link nav-sub-item {{ request()->routeIs('medication-cash-sales.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-cash-stack text-success"></i>
                      <p>Cash Sales</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- Laboratory -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-clipboard2-pulse-fill text-success"></i>
                  <p class="text-bold">
                    Lab & Investigations
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <!-- Lab Personnel Interface -->
                  <li class="nav-item">
                    <a href="{{ route('lab.visits.index') }}" class="nav-link nav-sub-item {{ request()->routeIs('lab.visits.*') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-vial text-primary"></i>
                      <p>Lab Personnel Dashboard</p>
                    </a>
                  </li>
                  <!-- Modern Clinical Management -->
                  <li class="nav-item">
                    <a href="{{ route('procedures.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-clipboard-check-fill text-primary"></i>
                      <p>Procedure Results Management</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('investigations.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-journal-medical text-success"></i>
                      <p>Investigation Dashboard</p>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- Store Manager // New Store Management -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-shop-window text-warning"></i>
                  <p class="text-bold">
                    Store Management
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('medications.dashboard') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>Primary Dashboard</p>
                    </a>
                  </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-box-seam-fill text-info"></i>
                      <p>
                        Items Management
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="{{ route('medications.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-capsule text-primary"></i>
                          <p>Medications/Items</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medication-pricing.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-currency-dollar text-success"></i>
                          <p>Medication Pricing</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.ledger.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-journal-text text-info"></i>
                          <p>Medication Ledger</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-categories.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-tags text-warning"></i>
                          <p>Categories</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <!-- Procurement -->
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-cart-plus-fill text-success"></i>
                      <p>
                        Procurement
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.grn.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-receipt text-info"></i>
                          <p>Goods Received Notes</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.suppliers.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-building text-warning"></i>
                          <p>Suppliers</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-units.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-rulers text-primary"></i>
                          <p>Store Units</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medication-units.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-calculator text-success"></i>
                          <p>Medications Units</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <!-- Stock Management -->
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-boxes text-danger"></i>
                      <p>
                        Stock Management
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.grn.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-receipt-cutoff text-info"></i>
                          <p>GRN Management</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store.requisitions.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-clipboard-data text-warning"></i>
                          <p>Requisitions</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.transfers.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-arrow-left-right text-primary"></i>
                          <p>Stock Transfers</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.adjustments.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-sliders text-success"></i>
                          <p>Adjustments</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.stock.disposal.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-trash text-danger"></i>
                          <p>Disposal</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-locations.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-building text-primary"></i>
                          <p>Store Locations</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-locations-stock.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-geo-alt text-info"></i>
                          <p>Location Stock</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('store-stock-movements.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-arrow-up-down text-warning"></i>
                          <p>Stock Movements</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-graph-down text-info"></i>
                      <p>
                        Consumption Tracking
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="{{ route('medications.consumption.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-eye text-primary"></i>
                          <p>Overview</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.consumption.prescriptions.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-prescription2 text-success"></i>
                          <p>Prescriptions</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.consumption.analytics') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-bar-chart text-warning"></i>
                          <p>Analytics</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-check2-square text-success"></i>
                      <p>
                        Reconciliation
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-speedometer text-primary"></i>
                          <p>Dashboard</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.discrepancies') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-exclamation-triangle text-warning"></i>
                          <p>Discrepancies</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.audit') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-clipboard-data text-info"></i>
                          <p>Audit Trail</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.comparison') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-arrow-left-right text-primary"></i>
                          <p>Stock Comparison</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medications.reconciliation.corrections.form') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-pencil-square text-danger"></i>
                          <p>Manual Corrections</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>

              <!-- Accounting -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-calculator-fill text-success"></i>
                  <p class="text-bold">
                    Financial Management
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('financial.dashboard') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        Financial Dashboard
                        <i class="bi bi-graph-up text-success ms-auto"></i>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-list-task text-info"></i>
                      <p>
                        All Transactions
                        <i class="bi bi-receipt text-muted ms-auto"></i>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.create') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-plus-circle-fill text-success"></i>
                      <p>New Transaction</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index', ['transaction_type' => 'income']) }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-arrow-up-circle-fill text-success"></i>
                      <p>Income Transactions</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index', ['transaction_type' => 'expense']) }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-arrow-down-circle-fill text-danger"></i>
                      <p>Expense Transactions</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.index', ['status' => 'pending']) }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-clock-history text-warning"></i>
                      <p>
                        Pending Approvals
                        <span class="badge badge-warning right" id="pending-transactions-count">0</span>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.transactions.export') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-download text-primary"></i>
                      <p>Export Reports</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.receipts.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-receipt-cutoff text-success"></i>
                      <p>Receipt Management</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('financial.receipts.daily.summary') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-calendar-day text-info"></i>
                      <p>Daily Summary</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- NHIF Integration -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-shield-check text-primary"></i>
                  <p class="text-bold">
                    NHIF Integration
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('nhif.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                      <p>
                        NHIF Dashboard
                        <i class="bi bi-hospital text-success ms-auto"></i>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('nhif.verify') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-person-check-fill text-success"></i>
                      <p>
                        Member Verification
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('nhif.tariffs') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-download text-info"></i>
                      <p>
                        Sync Tariffs
                        <i class="bi bi-arrow-repeat text-info ms-auto"></i>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('nhif.claims') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-file-medical text-warning"></i>
                      <p>
                        Claims Management
                        <i class="bi bi-clipboard-check text-warning ms-auto"></i>
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('nhif.reports') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-graph-up text-purple"></i>
                      <p>
                        NHIF Reports
                        <i class="bi bi-bar-chart text-purple ms-auto"></i>
                      </p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- User Management -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-shield-lock-fill text-warning"></i>
                  <p class="text-bold">
                    User Management
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-people-fill text-primary"></i>
                      <p>
                        All Users
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('users.pending-verification') }}" class="nav-link nav-sub-item">
                      <i class="nav-icon bi bi-person-exclamation text-warning"></i>
                      <p>
                        Pending Verification
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('users.password.reset') }}" class="nav-link nav-sub-item">
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
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-gear-fill text-secondary"></i>
                  <p class="text-bold">System Management<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-hospital text-info"></i>
                      <p>
                        Consultations
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="{{ url('doctors') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-person-badge text-primary"></i>
                          <p>
                            Doctors
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('patient_categories') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-tags text-warning"></i>
                          <p>
                            Patient Categories
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('visit_types') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-calendar-event text-info"></i>
                          <p>
                            Visit Types 
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('designations') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-award text-purple"></i>
                          <p>
                            Designations
                          </p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ url('consultation_fees') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-currency-dollar text-success"></i>
                          <p>
                            Consultation Charges
                          </p>
                        </a>
                      </li>
                  </ul>
                  </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-heart-pulse text-danger"></i>
                      <p>
                        Medical Services
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="{{ route('medical_services.index') }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-speedometer text-primary"></i>
                          <p>All Medical Services</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medical_services.index', ['category' => 'investigations']) }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-search text-info"></i>
                          <p>Investigations</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medical_services.index', ['category' => 'procedures']) }}" class="nav-link nav-sub-sub-item">
                          <i class="nav-icon bi bi-clipboard-plus text-success"></i>
                          <p>Medical Procedures</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('medical-service-pricing.index') }}" class="nav-link nav-sub-item">
                          <i class="nav-icon bi bi-currency-dollar text-success"></i>
                          <p>Service Pricing</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('service_categories.index') }}" class="nav-link nav-sub-item">
                          <i class="nav-icon bi bi-tags-fill text-success"></i>
                          <p>Service Categories</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('sample_types.index') }}" class="nav-link nav-sub-item">
                          <i class="nav-icon bi bi-droplet-fill text-warning"></i>
                          <p>Sample Types</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('lab.service-consumables.index') }}" class="nav-link nav-sub-item {{ request()->routeIs('lab.service-consumables.*') ? 'active' : '' }}">
                          <i class="nav-icon bi bi-clipboard2-data text-info"></i>
                          <p>Service Consumable Templates</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('result-templates.index') }}" class="nav-link nav-sub-item {{ request()->routeIs('result-templates.*') ? 'active' : '' }}">
                          <i class="nav-icon bi bi-file-medical text-primary"></i>
                          <p>Result Templates</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="{{ route('form-templates.index') }}" class="nav-link nav-sub-item {{ request()->routeIs('form-templates.*') ? 'active' : '' }}">
                          <i class="nav-icon bi bi-file-medical text-primary"></i>
                          <p>Form Templates</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                                   </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link nav-sub-header">
                      <i class="nav-icon bi bi-gear-fill text-danger"></i>
                      <p>
                        Configure reports
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                          <a href="{{ route('icd10.index') }}" class="nav-link nav-sub-sub-item">
                            <i class="nav-icon bi bi-gear-fill text-secondary"></i>
                            <p>Mtuha</p>
                          </a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>
              <!-- Reports -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link nav-header">
                  <i class="nav-icon bi bi-file-earmark-text-fill text-secondary"></i>
                  <p class="text-bold">
                    Reports
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('reports.mtuha.month') }}" class="nav-link nav-sub-item {{ request()->routeIs('reports.mtuha.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-list-columns-reverse text-primary"></i>
                      <p>MTUHA Monthly Report</p>
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

.nav-sub-header .nav-link {
    background: rgba(0,123,255,0.1);
    border-left: 3px solid #28a745;
    margin: 1px 0;
    margin-left: 15px;
    border-radius: 0 5px 5px 0;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.nav-sub-header .nav-link:hover {
    background: rgba(40,167,69,0.15);
    border-left-color: #20c997;
    transform: translateX(2px);
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

.nav-sub-sub-item .nav-link {
    margin-left: 35px;
    padding-left: 12px;
    border-left: 1px solid #adb5bd;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.nav-sub-sub-item .nav-link:hover {
    background: rgba(173,181,189,0.1);
    border-left-color: #6c757d;
    transform: translateX(2px);
}

/* Icon enhancements */
.nav-icon {
    margin-right: 8px;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

.nav-header .nav-icon {
    font-size: 1.2rem;
}

.nav-sub-header .nav-icon {
    font-size: 1.05rem;
}

.nav-sub-sub-item .nav-icon {
    font-size: 0.9rem;
}

/* Text and badge styling */
.text-bold {
    font-weight: 700;
}

.ms-auto {
    margin-left: auto !important;
}

/* Custom color classes */
.text-purple {
    color: #6f42c1 !important;
}

/* Badge positioning */
.badge.right {
    margin-left: auto;
}

/* Animation for pulse effect */
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .nav-header .nav-link,
    .nav-sub-header .nav-link,
    .nav-sub-item .nav-link,
    .nav-sub-sub-item .nav-link {
        margin-left: 5px;
        font-size: 0.8rem;
    }
    
    .nav-sub-sub-item .nav-link {
        margin-left: 15px;
    }
}

/* Active state styling */
.nav-link.active {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white !important;
    border-left-color: #ffc107;
}

.nav-link.active .nav-icon {
    color: white !important;
}

/* Hover effects for tree items */
.nav-treeview {
    transition: all 0.3s ease;
}

.nav-item.has-treeview > .nav-link.active ~ .nav-treeview {
    background: rgba(0,123,255,0.05);
    border-radius: 0 8px 8px 0;
    margin-right: 5px;
}

/* Expanded/Open section styling */
.nav-item.menu-open > .nav-treeview {
    background: linear-gradient(135deg, rgba(0,0,0,0.08) 0%, rgba(0,0,0,0.12) 100%);
    border-radius: 0 8px 8px 0;
    margin-right: 5px;
    padding: 5px 0;
    border-left: 2px solid rgba(0,123,255,0.3);
}

.nav-item.menu-open > .nav-header .nav-link {
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
    color: #ffffff !important;
    border-left-color: #ffc107;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.nav-item.menu-open > .nav-header .nav-link .nav-icon {
    color: #ffffff !important;
}

.nav-item.menu-open > .nav-header .nav-link .text-bold {
    color: #ffffff !important;
}

/* Nested expanded sections */
.nav-item.menu-open .nav-item.menu-open > .nav-treeview {
    background: linear-gradient(135deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.20) 100%);
    border-left: 2px solid rgba(40,167,69,0.4);
}

.nav-item.menu-open .nav-item.menu-open > .nav-sub-header .nav-link {
    background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
    color: #ffffff !important;
    border-left-color: #20c997;
}

.nav-item.menu-open .nav-item.menu-open > .nav-sub-header .nav-link .nav-icon {
    color: #ffffff !important;
}

/* Open section border enhancement */
.nav-item.menu-open {
    border-radius: 0 8px 8px 0;
    margin: 2px 0;
}

<!-- Confirmation modal for admin direct 'set user password' actions -->
<div class="modal fade" id="adminResetConfirmModal" tabindex="-1" aria-labelledby="adminResetConfirmLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
    <h5 class="modal-title" id="adminResetConfirmLabel">Confirm Password Change</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
    <p>You're about to set a new password for the user:</p>
    <p><strong id="adminResetTargetEmail">(no email)</strong></p>
    <p class="text-muted small">This action will immediately change the user's password. The user may be notified if you selected that option.</p>
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-danger" id="confirmAdminResetBtn">Confirm change</button>
    </div>
  </div>
  </div>
</div>

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
</style>
