<div class="alert alert-info">
    <h6><i class="fas fa-info-circle"></i> Rule Testing Interface</h6>
    <p><strong>Rule:</strong> {{ $rule->name }}</p>
    <p><strong>Type:</strong> {{ $rule->ruleType->display_name }}</p>
    <p><strong>Priority:</strong> {{ $rule->priority }}</p>
</div>

<form id="testRuleForm">
    <div class="row">
        <div class="col-md-6">
            <h6>Patient Information</h6>
            <div class="form-group">
                <label>Patient Age</label>
                <input type="number" class="form-control" name="patient_age" value="35">
            </div>
            <div class="form-group">
                <label>Patient Weight (kg)</label>
                <input type="number" class="form-control" name="patient_weight" value="70">
            </div>
        </div>
        <div class="col-md-6">
            <h6>Medication Information</h6>
            <div class="form-group">
                <label>Medication Name</label>
                <input type="text" class="form-control" name="medication_name" value="Paracetamol">
            </div>
            <div class="form-group">
                <label>Dose Amount</label>
                <input type="number" class="form-control" name="dose_amount" value="500">
            </div>
            <div class="form-group">
                <label>Dose Unit</label>
                <select class="form-control" name="dose_unit">
                    <option value="mg">mg</option>
                    <option value="ml">ml</option>
                    <option value="tablets">tablets</option>
                </select>
            </div>
        </div>
    </div>

    @if($rule->conditions->count() > 0)
    <div class="mt-3">
        <h6>Rule Conditions</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Operator</th>
                        <th>Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rule->conditions as $condition)
                    <tr>
                        <td><code>{{ $condition->field }}</code></td>
                        <td>{{ ucwords(str_replace('_', ' ', $condition->operator)) }}</td>
                        <td><strong>{{ $condition->value }}</strong></td>
                        <td><span class="badge badge-secondary">Ready</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="mt-3">
        <button type="button" class="btn btn-primary" onclick="runTest()">
            <i class="fas fa-play"></i> Run Test
        </button>
        <button type="button" class="btn btn-secondary" onclick="resetForm()">
            <i class="fas fa-redo"></i> Reset
        </button>
    </div>
</form>

<div id="testResults" class="mt-3" style="display: none;">
    <div class="alert alert-info">
        <h6><i class="fas fa-check-circle"></i> Test Results</h6>
        <div id="testResultsContent"></div>
    </div>
</div>

<script>
function runTest() {
    const formData = new FormData(document.getElementById('testRuleForm'));
    const data = Object.fromEntries(formData);
    
    // Simulate test results
    const results = {
        rule_triggered: Math.random() > 0.5,
        severity: '{{ $rule->severity }}',
        message: 'Test evaluation for rule: {{ $rule->name }}'
    };
    
    displayTestResults(results);
}

function displayTestResults(results) {
    const resultsDiv = document.getElementById('testResults');
    const contentDiv = document.getElementById('testResultsContent');
    
    let html = '<div class="row">';
    html += '<div class="col-md-6">';
    html += '<strong>Rule Triggered:</strong> ';
    html += results.rule_triggered ? 
        '<span class="badge badge-warning">Yes</span>' : 
        '<span class="badge badge-success">No</span>';
    html += '<br><strong>Severity:</strong> ';
    html += `<span class="badge badge-${results.severity === 'critical' ? 'danger' : (results.severity === 'warning' ? 'warning' : 'info')}">${results.severity}</span>`;
    html += '</div>';
    html += '<div class="col-md-6">';
    html += '<strong>Message:</strong><br>' + results.message;
    html += '</div>';
    html += '</div>';
    
    contentDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
}

function resetForm() {
    document.getElementById('testRuleForm').reset();
    document.getElementById('testResults').style.display = 'none';
}
</script>