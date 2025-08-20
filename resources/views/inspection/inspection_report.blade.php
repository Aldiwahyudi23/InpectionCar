<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; }
        h1,h2,h3 { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f4f4f4; }
        .images img { max-width: 120px; margin: 3px; border: 1px solid #ddd; }
    </style>
</head>
<body>

    <h1>Inspection Report</h1>

    {{-- Data Mobil --}}
    <h2>Car Information</h2>
    <table>
        <tr><th>Brand</th><td>{{ $inspection->car->brand->name ?? '-' }}</td></tr>
        <tr><th>Model</th><td>{{ $inspection->car->model->name ?? '-' }}</td></tr>
        <tr><th>Type</th><td>{{ $inspection->car->type->name ?? '-' }}</td></tr>
        <tr><th>Category</th><td>{{ $inspection->category->name ?? '-' }}</td></tr>
    </table>

    {{-- Inspection Points --}}
    <h2>Inspection Points</h2>
    <table>
        <thead>
            <tr>
                <th>Component</th>
                <th>Result</th>
                <th>Notes</th>
                <th>Images</th>
            </tr>
        </thead>
        
    </table>

</body>
</html>
