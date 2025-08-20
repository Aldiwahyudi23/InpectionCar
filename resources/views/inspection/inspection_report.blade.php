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
        .point-name { 
            display: inline-block; 
            min-width: 150px; 
            font-weight: bold;
            vertical-align: top;
        }
        .point-content {
            display: inline-block;
            width: calc(100% - 170px);
            vertical-align: top;
        }
        .point-note {
            margin: 5px 0;
            font-style: italic;
            color: #555;
        }
        .images { 
            display: flex; 
            flex-wrap: wrap; 
            margin-top: 5px;
            gap: 10px;
        }
        .images img { 
            width: 120px; 
            height: 90px;
            object-fit: cover;
            border: 1px solid #ddd; 
            border-radius: 3px; 
        }
        /* Gaya khusus untuk komponen Foto Kendaraan */
        .photo-component .images {
            margin-left: 0;
            justify-content: flex-start;
            gap: 15px;
        }
        .photo-component .images img {
            width: 45%;
            max-width: 300px;
            height: auto;
            min-height: 180px;
            margin: 0;
            flex: 1 1 calc(50% - 15px);
        }
        .photo-component .point {
            display: none; /* Sembunyikan titik inspeksi untuk foto kendaraan */
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 8px;
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
        
        /* Media query untuk tampilan responsif */
        @media (max-width: 768px) {
            .photo-component .images img {
                width: 100%;
                flex: 1 1 100%;
            }
            .point-name {
                min-width: 120px;
            }
            .point-content {
                width: calc(100% - 130px);
            }
        }
    </style>
</head>
<body>

    {{-- Header dengan Foto Utama + Data Mobil --}}
    <div class="header" style="display: flex; flex-direction: column; gap: 20px;">

        {{-- Bagian atas: gambar + judul --}}
        <div style="display: flex; align-items: center; gap: 20px;">
            @if($coverImage && file_exists(public_path($coverImage->image_path)))
                <img src="{{ public_path($coverImage->image_path) }}" alt="Foto Utama" style="width: 250px; height: 188px; object-fit: cover; border:1px solid #ccc;">
            @else
                <div style="width:250px; height:188px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">
                    <span>Gambar tidak tersedia</span>
                </div>
            @endif

            <div>
                <h2 style="margin: 0;">{{ strtoupper($inspection->car->brand->name.' '.$inspection->car->model->name.' '.$inspection->car->type->name) }}</h2>
                <h3 style="margin: 5px 0 0 0; font-weight: normal;">{{ $inspection->car->year }} {{ $inspection->car->engine_size }} CC {{ $inspection->car->fuel_type }} ({{ $inspection->car->model->period ?? '' }})</h3>
            </div>
        </div>

        {{-- Tabel info mobil --}}
        <div class="car-info">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">Nomor Polisi</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">{{ $inspection->car->license_plate }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">Merek</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">{{ $inspection->car->brand->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">Model</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">{{ $inspection->car->model->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">Tipe</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">{{ $inspection->car->type->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">Periode Model</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">{{ $inspection->car->model->period ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">Warna</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">{{ $inspection->car->color }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">Tahun Pembuatan</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ccc;">{{ $inspection->car->year }}</td>
                </tr>
            </table>
        </div>
    </div>


    <h2 style="border-bottom: 2px solid #333; padding-bottom: 5px;">Hasil Inspeksi</h2>

    {{-- Group per Komponen --}}
    @foreach($inspection_points->groupBy('component.name') as $componentName => $points)
        <div class="section avoid-break {{ $componentName == 'Foto Kendaraan' ? 'photo-component' : '' }}">
            <div class="component-title">{{ $componentName ?? 'Tanpa Komponen' }}</div>

            @if($componentName == 'Foto Kendaraan')
                {{-- Tampilan khusus untuk Foto Kendaraan --}}
                <div class="images">
                    @foreach($points as $point)
                        @if($point->images && $point->images->count())
                            @foreach($point->images as $img)
                                @if(file_exists(public_path($img->image_path)))
                                    <img src="{{ public_path($img->image_path) }}" alt="Foto Kendaraan">
                                @else
                                    <div style="width:45%; max-width:300px; height:180px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center; margin:5px;">
                                        <span style="font-size:10px;">Gambar tidak ditemukan</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>
            @else
                {{-- Tampilan normal untuk komponen lainnya --}}
                @foreach($points as $point)
                    <div class="point avoid-break">
                        <span class="point-name">{{ $point->name ?? '-' }}</span>
                        
                        <div class="point-content">
                            {{-- Cek apakah ada result untuk point ini --}}
                            @if($point->results && $point->results->count())
                                @php
                                    $result = $point->results->first();
                                    $hasStatus = !empty($result->status);
                                    $hasNote = !empty($result->note);
                                    
                                    // Tentukan kelas status
                                    $statusClass = 'status-good';
                                    if (strpos(strtolower($result->status ?? ''), 'rusak') !== false || 
                                        strpos(strtolower($result->status ?? ''), 'tidak baik') !== false) {
                                        $statusClass = 'status-bad';
                                    } elseif (strpos(strtolower($result->status ?? ''), 'perbaikan') !== false || 
                                             strpos(strtolower($result->status ?? ''), 'warning') !== false) {
                                        $statusClass = 'status-warning';
                                    }
                                @endphp
                                
                                {{-- Tampilkan status jika ada --}}
                                @if($hasStatus)
                                    <span class="status-badge {{ $statusClass }}">{{ $result->status }}</span>
                                @endif
                                
                                {{-- Tampilkan note di samping jika status tidak ada --}}
                                @if($hasNote && !$hasStatus)
                                    <span class="point-note">{{ $result->note }}</span>
                                @endif
                                
                                {{-- Tampilkan note di bawah jika status ada --}}
                                @if($hasNote && $hasStatus)
                                    <div class="point-note">{{ $result->note }}</div>
                                @endif
                                
                                {{-- Tampilkan gambar jika ada --}}
                                @if($point->images && $point->images->count())
                                    <div class="images">
                                        @foreach($point->images as $img)
                                            @if(file_exists(public_path($img->image_path)))
                                                <img src="{{ public_path($img->image_path) }}" alt="image">
                                            @else
                                                <div style="width:120px; height:90px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center;">
                                                    <span style="font-size:10px;">Gambar tidak ditemukan</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                
                            @else
                                {{-- Jika tidak ada result --}}
                                <span class="status-badge status-warning">Belum diperiksa</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach

</body>
</html>