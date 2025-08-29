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
            height: 250px;
            object-fit: cover;
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

       /* Gaya khusus untuk komponen Foto Kendaraan */
.photo-component .images {
    justify-content: flex-start; /* Rata kiri */
    gap: 5px; /* Jarak antar gambar */
    margin-top: 15px; /* Tambahkan atau sesuaikan jarak dari nama komponen */
}

.photo-component .images img {
    aspect-ratio: 1/1; /* Memastikan rasio 1:1 (persegi) */
    height: auto; /* Mengabaikan height sebelumnya */
    width: calc(25% - 6px); /* 4 gambar per baris (100%/4) */
}

/* Biarkan .images umum dengan margin-top yang lebih kecil atau hapus jika tidak perlu */
.images {
    display: flex;
    flex-wrap: wrap; 
    gap: 10px; /* Jarak antar gambar */
    margin-top: 10px; /* Jarak dari atas */
}

.images img {
    width: 120px;
    height: 120px; /* Membuat gambar persegi (1:1) */
    object-fit: cover;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.photo-component .point {
    display: none;
}
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 8px;
            margin-bottom: 10px;
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
        
        /* Gambar Point */
        .inspection-images {
            display: flex;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .inspection-images img {
            aspect-ratio: 1/1; /* Memastikan rasio 1:1 (persegi) */
            height: auto; /* Mengabaikan height sebelumnya */
            width: calc(20% - 6px);
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .conclusion {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
            border-radius: 4px;
        }
        
        /* .textarea-note {
            margin-top: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 12px;
            white-space: pre-wrap; */
        /* } */
        .textarea-note {
            margin: 5px 0;
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>

    {{-- Header dengan Foto Utama + Data Mobil --}}
    <div class="header" style="display: flex; flex-direction: column; gap: 20px;">

        {{-- Bagian atas: gambar + judul --}}
        <div style="display: flex; align-items: center; gap: 20px;">
            @if($coverImage && file_exists(public_path($coverImage->image_path)))
                <img src="{{ public_path($coverImage->image_path) }}" alt="Foto Utama" style="width: 250px; height: 250px; object-fit: cover; border:1px solid #ccc;">
            @else
                <div style="width:250px; height:250px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">
                    <span>Gambar tidak tersedia</span>
                </div>
            @endif

            <div class="mt-10">
                <h2 style="margin: 0;">{{ $inspection->car_name }}</h2>
            </div>
        </div>

        {{-- Tabel info mobil --}}
        @if ($inspection->car_id)    
        <div class="car-info">
            <table>
                <tr><td>Nomor Polisi</td><td>{{ $inspection->plate_number }}</td></tr>
                <tr><td>Merek</td><td>{{ $inspection->car->brand->name }}</td></tr>
                <tr><td>Model</td><td>{{ $inspection->car->model->name }}</td></tr>
                <tr><td>Tipe</td><td>{{ $inspection->car->type->name }}</td></tr>
                <tr><td>CC</td><td>{{ $inspection->car->cc }}</td></tr>
                <tr><td>Bahan Bakar</td><td>{{ $inspection->car->fuel_type }}</td></tr>
                <tr><td>Transmisi</td><td>{{ $inspection->car->transmission }}</td></tr>
                <tr><td>Periode Model</td><td>{{ $inspection->car->production_period ?? '-' }}</td></tr>
                <tr><td>Tahun Pembuatan</td><td>{{ $inspection->car->year }}</td></tr>
            </table>
        </div>
        @endif
        
        {{-- Kesimpulan --}}
        @if($inspection->notes)
        <div class="conclusion">
            <h3>Kesimpulan Inspeksi:</h3>
            <p>{{ $inspection->notes }}</p>
        </div>
        @endif
    </div>

    <h2 style="border-bottom: 2px solid #333; padding-bottom: 5px;">Hasil Inspeksi</h2>

    {{-- Group per Komponen --}}
    @foreach($inspection_points->groupBy('component.name') as $componentName => $points)
        <div class="section {{ $componentName == 'Foto Kendaraan' ? 'photo-component' : '' }}">
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
                                    <div style="width:calc(25% - 15px); height:180px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center; margin:5px;">
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
                    @php
                        // Akses settings yang sudah di-cast sebagai array
                        $settings = $point->settings ?? [];
                        $resul   = $point->results->first();
                        $selected = $resul->status ?? null;

                        // Cari radio option yang sesuai status
                        $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $selected);

                        $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                        $showTextarea    = $selectedOption['settings']['show_textarea'] ?? false;


                        $inputType = $point->input_type ?? '';

                        $hasResult = $point->results && $point->results->count() > 0;
                        $hasImage  = $point->images && $point->images->count() > 0;

                         // Tentukan apakah image perlu ditampilkan
                        $showImages = (
                            ($inputType === 'image') ||
                            ($inputType === 'imageTOradio') ||
                            ($inputType === 'radio' && $showImageUpload)
                        ) && $hasImage;

                        $symbol = $settings['currency_symbol'] ?? 'Rp';
                        $thousand = $settings['thousands_separator'] ?? '.';
                        $decimal = $settings['decimal_separator'] ?? ',';

                @endphp

                {{-- Skip point kalau tidak ada result dan tidak ada image --}}
                @if(!$hasResult && !$hasImage)
                    @continue
                @endif
                    
                    <div class="point">
                        <span class="point-name">{{ $point->name ?? '-' }}</span>                       
                        <div class="point-content">
                            {{-- Cek apakah ada result untuk point ini --}}
                            @if($point->results && $point->results->count())
                                @php
                                    $result = $point->results->first();
                                    $hasStatus = !empty($result->status);
                                    $hasNote = !empty($result->note);
                                    
                                    // Tentukan kelas CSS berdasarkan status
                                    $statusClass = 'status-warning';
                                    if (in_array(strtolower($result->status), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                        $statusClass = 'status-good';
                                    } elseif (in_array(strtolower($result->status), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                        $statusClass = 'status-bad';
                                    }
                                @endphp
                                
                                {{-- TAMPILKAN STATUS --}}
                                {{-- Untuk input_type radio dan imageTOradio, tampilkan status di samping nama --}}
                                @if(in_array($inputType, ['radio', 'imageTOradio']) && $hasStatus)
                                    <span class="status-badge {{ $statusClass }}">{{ $result->status }}</span>
                                @endif
                                
                                {{-- TAMPILKAN NOTE/TEXT --}}
                                {{-- Untuk input_type text, number, account, date, textarea, tampilkan note --}}
                                @if(in_array($inputType, ['text', 'number', 'date', 'textarea']) && $hasNote)
                                    <div class="point-note">{{ $result->note }}</div>
                                @endif

                                {{-- TAMPILKAN NOTE/TEXT --}}
                                {{-- Untuk input_type text, number, account, date, textarea, tampilkan note --}}
                                @if(in_array($inputType, ['account']) && $hasNote)
                                    <div class="point-note">
                                        {{ $symbol . ' ' . number_format($result->note, 0, $decimal, $thousand) }}
                                    </div>
                                @endif
                                      
                            @endif
                        </div>
                         {{-- TAMPILKAN GAMBAR --}}
                        {{-- GAMBAR (jika sesuai aturan $showImages) --}}
                            @if($showImages)
                                <div class="inspection-images">
                                    @foreach($point->images as $img)
                                        @if(file_exists(public_path($img->image_path)))
                                            <img src="{{ public_path($img->image_path) }}" alt="image">
                                        @else
                                            <div style="width:calc(20% - 8px); height:90px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center;">
                                                <span style="font-size:10px;">Gambar tidak ditemukan</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                                
                                {{-- TAMPILKAN TEXTAREA --}}
                                {{-- Untuk semua input_type, cek show_textarea --}}
                                @if($showTextarea && $hasNote)
                                    <div class="textarea-note">{{ $result->note }}</div>
                                @endif
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach

</body>
</html>
