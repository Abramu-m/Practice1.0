<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Case Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            margin: 24px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin-bottom: 4px;
            font-size: 22px;
        }
        .header p {
            margin: 0;
            color: #555;
            font-size: 12px;
        }
        .section-title {
            margin: 24px 0 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #222;
        }
        .summary-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .summary-item {
            min-width: 200px;
            flex: 1 1 200px;
        }
        .summary-item strong {
            display: block;
            font-size: 12px;
            color: #333;
            margin-bottom: 2px;
        }
        .summary-content {
            margin-bottom: 16px;
            font-size: 12px;
            line-height: 1.45;
            color: #222;
        }
        h6 {
            margin-bottom: 8px;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 12px;
        }
        table td {
            padding: 6px 8px;
            vertical-align: top;
            border: 1px solid #ddd;
        }
        ul {
            margin: 0;
            padding-left: 20px;
        }
        ul li {
            margin-bottom: 4px;
        }
        .small-note {
            font-size: 11px;
            color: #666;
            margin-top: 18px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            margin-bottom: 18px;
            background-color: #fff;
        }
        .card-header {
            padding: 10px 14px;
            border-bottom: 1px solid #ddd;
            background-color: #f7f7f7;
            font-weight: 700;
        }
        .card-body {
            padding: 14px;
        }
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .bg-success { background-color: #198754; }
        .bg-warning { background-color: #ffc107; color: #212529; }
        .bg-danger { background-color: #dc3545; }
        .bg-secondary { background-color: #6c757d; }
        .bg-light { background-color: #f8f9fa; }
        .alert {
            padding: 10px 14px;
            margin-bottom: 16px;
            border: 1px solid #dcdcdc;
            border-radius: 0.375rem;
            background-color: #f8f9fa;
            color: #212529;
        }
        .table th, .table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: 700;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -8px;
        }
        .col-md-6, .col-md-12, .col-12 {
            padding: 0 8px;
            box-sizing: border-box;
        }
        .col-md-6 { width: 50%; }
        .col-md-12, .col-12 { width: 100%; }
        .text-muted { color: #6c757d; }
        .fw-medium { font-weight: 500; }
        .fw-semibold { font-weight: 600; }
        .border { border: 1px solid #ddd; }
        .rounded { border-radius: 0.375rem; }
        .p-3 { padding: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .gap-4 { gap: 1.5rem; }
        .d-flex { display: flex; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Case Summary</h1>
        <p>Patient consultation summary with investigations, results, and prescriptions included.</p>
    </div>

    @include('consultations.partials.case_summary_content')
</body>
</html>
