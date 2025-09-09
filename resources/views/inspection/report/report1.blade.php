<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inspeksi Kendaraan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
        }

        .header img {
            width: 250px;
            height: 250px;
            object-fit: cover;
            margin-right: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .car-info {
            font-size: 13px;
            flex: 1;
        }

        .car-info h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .car-info table {
            margin-top: 10px;
            border-collapse: collapse;
            width: 100%;
        }

        .car-info td {
            padding: 5px 8px;
            vertical-align: top;
            border: 1px solid #ddd;
        }

        .car-info td:first-child {
            width: 30%;
            font-weight: bold;
            background-color: #f7f7f7;
        }

        .conclusion {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #555;
            border-radius: 4px;
        }
        
        /* Gaya untuk setiap blok komponen */
        .component-block {
            margin-top: 25px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .component-title {
            font-weight: bold;
            font-size: 16px;
            padding: 10px 15px;
            color: white;
            background-color: #4338CA; /* indigo-700 */
        }
        
        .component-content {
            padding: 15px;
            background-color: #fff;
        }

        .point {
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px dotted #ccc;
        }

        .point:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .point-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .point-name {
            font-weight: bold;
            display: block;
        }
        
        .point-note {
            font-style: italic;
            color: #777;
            flex-grow: 1;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 0;
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

        .photo-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .photo-gallery img {
            width: calc(25% - 10px);
            aspect-ratio: 1/1;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Gaya untuk cetak PDF */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                padding: 20px;
            }

            .header {
                page-break-after: always;
            }

            .component-block {
                page-break-before: always;
                margin-top: 0;
            }

            .point {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        @if($coverImage && file_exists(public_path($coverImage->image_path)))
            <img src="{{ public_path($coverImage->image_path) }}" alt="Foto Utama">
        @else
            <div style="width:250px; height:250px; border:1px solid #ccc; border-radius:8px; display:flex; align-items:center; justify-content:center; background-color: #f0f0f0;">
                <span>Gambar tidak tersedia</span>
            </div>
        @endif

        <div class="car-info">
            <h2>Laporan Inspeksi Kendaraan</h2>
            <h3 style="margin: 5px 0 15px;">{{ $inspection->car_name }}</h3>
            @if ($inspection->car_id)
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
            @endif
        </div>
    </div>
    
    @if($inspection->notes)
    <div class="conclusion">
        <h3>Kesimpulan Inspeksi:</h3>
        <p>{!! $inspection->notes !!}</p>
    </div>
    @endif

    @foreach($menu_points->groupBy('inspection_point.component.name') as $componentName => $points)
        <div class="component-block">
            <div class="component-title">{{ $componentName ?? 'Tanpa Komponen' }}</div>
            
            <div class="component-content">
                @if($componentName == 'Foto Kendaraan')
                    <div class="photo-gallery">
                        @foreach($points as $point)
                            @if($point->inspection_point->images && $point->inspection_point->images->count())
                                @foreach($point->inspection_point->images as $img)
                                    @if(file_exists(public_path($img->image_path)))
                                        <img src="{{ public_path($img->image_path) }}" alt="Foto Kendaraan">
                                    @else
                                        <div style="width:calc(25% - 10px); aspect-ratio:1/1; border:1px solid #ddd; border-radius:5px; display:flex; align-items:center; justify-content:center; background-color: #f9f9f9;">
                                            <span style="font-size:10px; text-align: center;">Gambar tidak ditemukan</span>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                @else
                    @foreach($points as $point)
                        @php
                            $result = $point->inspection_point->results->first();
                            $hasResult = $result && (!empty($result->status) || !empty($result->note));
                            $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;
                            
                            if (!$hasResult && !$hasImage) {
                                continue;
                            }

                            $inputType = $point->input_type ?? '';
                            $selected = $result->status ?? null;
                            $settings = $point->settings ?? [];
                            $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $selected);
                            $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                            $showTextarea = $selectedOption['settings']['show_textarea'] ?? false;
                            $showImages = (in_array($inputType, ['image', 'imageTOradio']) || ($inputType === 'radio' && $showImageUpload)) && $hasImage;
                            
                            $symbol = $settings['currency_symbol'] ?? 'Rp';
                            $thousand = $settings['thousands_separator'] ?? '.';
                            $decimal = $settings['decimal_separator'] ?? ',';

                            $statusClass = 'status-warning';
                            if (in_array(strtolower($selected), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                $statusClass = 'status-good';
                            } elseif (in_array(strtolower($selected), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                $statusClass = 'status-bad';
                            }
                        @endphp
                        
                        <div class="point">
                            <div class="point-info">
                                <span class="point-name">
                                    {{ $point->inspection_point->name ?? '-' }}
                                </span>
                                @if(in_array($inputType, ['radio', 'imageTOradio']) && !empty($result->status))
                                    <span class="status-badge {{ $statusClass }}">{{ $result->status }}</span>
                                @endif
                                @if(!$showImages && !empty($result->note))
                                    <span class="point-note">{{ $result->note }}</span>
                                @endif
                            </div>

                            @if($showImages)
                                <div class="photo-gallery">
                                    @foreach($point->inspection_point->images as $img)
                                        @if(file_exists(public_path($img->image_path)))
                                            <img src="{{ public_path($img->image_path) }}" alt="image">
                                        @else
                                            <div style="width:calc(25% - 10px); aspect-ratio:1/1; border:1px solid #ddd; border-radius:5px; display:flex; align-items:center; justify-content:center; background-color: #f9f9f9;">
                                                <span style="font-size:10px; text-align: center;">Gambar tidak ditemukan</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @if(!empty($result->note))
                                    <div class="point-note" style="padding-left: 0; margin-top: 10px;">{{ $result->note }}</div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach

</body>
</html>