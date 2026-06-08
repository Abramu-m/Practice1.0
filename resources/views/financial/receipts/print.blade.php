<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        /* Toolbar (hidden when printing) */
        .print-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .print-toolbar button {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-print {
            background: #0d6efd;
            color: #fff;
        }

        .btn-print:hover {
            background: #0b5ed7;
        }

        .btn-close-tab {
            background: #6c757d;
            color: #fff;
        }

        .btn-close-tab:hover {
            background: #5c636a;
        }

        /* Receipt wrapper */
        .receipt-wrapper {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 4px;
            padding: {{ $format === 'thermal' ? '10px' : '30px' }};
            display: inline-block;
            {{ $format === 'thermal' ? 'max-width: 70mm;' : 'min-width: 600px; max-width: 860px; width: 100%;' }}
        }

        /* Print media — hide toolbar, reset background */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .print-toolbar {
                display: none;
            }

            .receipt-wrapper {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-toolbar">
        <button class="btn-print" onclick="window.print()">
            &#x1F5A8; Print
        </button>
        <button class="btn-close-tab" onclick="window.close()">
            Close
        </button>
    </div>

    <div class="receipt-wrapper">
        {!! $receipt_html !!}
    </div>

    <script>
        // Auto-trigger browser print dialog when page loads
        window.addEventListener('load', function () {
            window.print();
        });
    </script>

</body>
</html>
