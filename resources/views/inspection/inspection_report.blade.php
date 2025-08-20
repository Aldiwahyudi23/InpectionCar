<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inspeksi</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 13px; 
            color: #333; 
            margin: 0;
            padding: 20px;
        }
        .header { 
            display: flex; 
            margin-bottom: 20px; 
            align-items: flex-start;
        }
        .header img { 
            width: 250px; 
            height: auto; 
            margin-right: 20px; 
            border: 1px solid #ccc; 
        }
        .car-info { 
            font-size: 13px; 
            flex: 1;
        }
        .car-info h2 { 
            margin: 0 0 5px 0; 
            font-size: 16px; 
            font-weight: bold; 
        }
        .car-info h3 { 
            margin: 0 0 10px 0; 
            font-size: 14px; 
            font-weight: normal; 
            color: #555; 
        }
        .car-info table { 
            margin-top: 10px; 
            border-collapse: collapse;
            width: 100%;
        }
        .car-info td { 
            padding: 3px 6px; 
            vertical-align: top; 
            border: 1px solid #ddd;
        }
        .car-info td:first-child {
            width: 30%;
            font-weight: bold;
        }

        h1, h2, h3 { 
            margin: 5px 0; 
        }
        .section { 
            margin-bottom: 20px; 
            page-break-inside: avoid;
        }
        .component-title { 
            font-weight: bold; 
            font-size: 14px; 
            margin-top: 15px; 
            background-color: #f5f5f5;
            padding: 5px 10px;
            border-left: 3px solid #333;
        }
        .point { 
            margin-left: 15px; 
            margin-bottom: 10px; 
            padding: 5px 0;
            border-bottom: 1px dotted #eee;
        }
        .point span { 
            display: inline-block; 
            min-width: 150px; 
            font-weight: bold;
        }
        .images { 
            display: flex; 
            flex-wrap: wrap; 
            margin-top: 5px; 
            margin-left: 25px; 
        }
        .images img { 
            width: 120px; 
            height: 90px;
            object-fit: cover;
            margin: 5px; 
            border: 1px solid #ddd; 
            border-radius: 3px; 
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 10px;
        }
        .status-good {
            background-color: #d4edda;
            color: #155724;
        }
        .status-bad {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        /* Untuk menghindari pemotongan halaman di tempat yang tidak tepat */
        .page-break {
            page-break-after: always;
        }
        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    {{-- Header dengan Foto Utama + Data Mobil --}}
    <div class="header">
        @if($coverImage && file_exists(public_path($coverImage->image_path)))
            <img src="{{ public_path($coverImage->image_path) }}" alt="Foto Utama">
        @else
            <div style="width:250px; height:188px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; margin-right:20px;">
                <span>Gambar tidak tersedia</span>
            </div>
        @endif
        <div class="car-info">
            <h2>{{ strtoupper($inspection->car->brand->name.' '.$inspection->car->model->name.' '.$inspection->car->type->name) }}</h2>
            <h3>{{ $inspection->car->year }} {{ $inspection->car->engine_size }} CC {{ $inspection->car->fuel_type }} ({{ $inspection->car->model->period ?? '' }})</h3>
            
            <table>
                <tr><td>Nomor Polisi</td><td>{{ $inspection->car->license_plate }}</td></tr>
                <tr><td>Merek</td><td>{{ $inspection->car->brand->name }}</td></tr>
                <tr><td>Model</td><td>{{ $inspection->car->model->name }}</td></tr>
                <tr><td>Tipe</td><td>{{ $inspection->car->type->name }}</td></tr>
                <tr><td>Periode Model</td><td>{{ $inspection->car->model->period ?? '-' }}</td></tr>
                <tr><td>Warna</td><td>{{ $inspection->car->color }}</td></tr>
                <tr><td>Tahun Pembuatan</td><td>{{ $inspection->car->year }}</td></tr>
            </table>
        </div>
    </div>

    <h2 style="border-bottom: 2px solid #333; padding-bottom: 5px;">Hasil Inspeksi</h2>

    {{-- Group per Komponen --}}
    @foreach($inspection_points->groupBy('component.name') as $componentName => $points)
        <div class="section avoid-break">
            <div class="component-title">{{ $componentName ?? 'Tanpa Komponen' }}</div>

            @foreach($points as $point)
                <div class="point avoid-break">
                    <span>{{ $point->name ?? '-' }}</span>
                    
                    {{-- Ambil hasil dari result.value --}}
                    @if($point->results && $point->results->count())
                        @foreach($point->results as $res)
                            @php
                                $statusClass = 'status-good';
                                if (strpos(strtolower($res->status ?? ''), 'rusak') !== false || 
                                    strpos(strtolower($res->status ?? ''), 'tidak baik') !== false) {
                                    $statusClass = 'status-bad';
                                } elseif (strpos(strtolower($res->status ?? ''), 'perbaikan') !== false || 
                                         strpos(strtolower($res->status ?? ''), 'warning') !== false) {
                                    $statusClass = 'status-warning';
                                }
                            @endphp
                            
                            <span class="status-badge {{ $statusClass }}">{{ $res->status ?? '' }}</span>
                            
                            @if(!empty($res->notes))
                                : {{ $res->notes }}
                            @endif
                        @endforeach
                    @else
                        <span class="status-badge status-warning">Belum diperiksa</span>
                    @endif
                </div>

                {{-- Foto --}}
                @if($point->images && $point->images->count())
                    <div class="images avoid-break">
                        @foreach($point->images as $img)
                            @if(file_exists(public_path($img->image_path)))
                                <img src="{{ asset($img->image_path) }}" alt="image">
                            @else
                                <div style="width:120px; height:90px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center; margin:5px;">
                                    <span style="font-size:10px;">Gambar tidak ditemukan</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach

</body>
</html>