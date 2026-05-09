<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcodes</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background: #f3f4f6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 20px; 
        }
        .card { 
            border: 2px dashed #e5e7eb; 
            padding: 15px; 
            text-align: center; 
            border-radius: 8px; 
            page-break-inside: avoid;
            background: white;
        }
        .barcode { 
            max-width: 100%; 
            height: auto; 
            margin-top: 10px; 
        }
        .name { 
            font-weight: 600; 
            font-size: 16px; 
            margin-bottom: 5px; 
            color: #1f2937;
        }
        .sku { 
            color: #6b7280; 
            font-size: 14px; 
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .print-btn { 
            padding: 10px 20px; 
            font-size: 14px; 
            font-weight: 600;
            cursor: pointer; 
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }
        @media print {
            body { 
                padding: 0; 
                background: white;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
            .no-print { 
                display: none !important; 
            }
            .card { 
                border: 1px dashed #ccc; 
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header no-print">
            <h2>Print Product Barcodes</h2>
            <button class="print-btn" onclick="window.print()">Print Now</button>
        </div>
        
        <div class="grid">
            @foreach($products as $product)
                <div class="card">
                    <div class="name">{{ $product->name }}</div>
                    <div class="sku">SKU: {{ $product->sku_code }}</div>
                    @if($product->barcode)
                        <img src="{{ Storage::url($product->barcode) }}" alt="Barcode for {{ $product->sku_code }}" class="barcode">
                    @else
                        <div style="padding: 20px; color: #9ca3af; font-size: 12px; font-style: italic;">No Barcode Generated</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <script>
        // Automatically open print dialog when the page loads
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
