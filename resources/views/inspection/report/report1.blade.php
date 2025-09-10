<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inspeksi Kendaraan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
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
            border: none;
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
            border: none;
        }
        
        .car-info tr:not(:last-child) td {
            border-bottom: 1px solid #ddd;
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

        /* Komponen */
        .component-block {
            margin-top: 25px;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
            border: 1px solid #ddd;
        }

        .component-title {
            font-weight: bold;
            font-size: 16px;
            padding: 10px 15px;
            color: white;
            background-color: #4338CA;
        }

        .component-content {
            padding: 15px;
            background-color: #fff;
        }

        /* Tabel untuk poin-poin inspeksi */
        .point-table {
            width: 100%;
            border-collapse: collapse;
        }

        .point-table td {
            padding: 5px 8px;
            vertical-align: top;
            border: none;
        }
        
        /* Garis pembatas antar poin */
        .point-separator {
            border-bottom: 2px solid #000;
            line-height: 0; /* Menghilangkan spasi pada baris pembatas */
            height: 0; /* Menghilangkan tinggi baris */
            padding: 0; /* Menghilangkan padding */
        }
        
        .point-separator-row td {
            padding: 0;
            border: none;
        }

        .point-name-cell {
            width: 40%;
            font-weight: bold;
            vertical-align: middle;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            white-space: nowrap;
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

        .point-note {
            font-style: italic;
            color: #777;
        }

        /* Tabel untuk foto */
        .photo-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .photo-table td {
            padding: 5px;
            text-align: center;
            vertical-align: top;
            border: none;
        }

        .photo-table.car-photos-4 td {
            width: 25%;
        }

        .photo-table.other-photos td {
            width: 20%;
        }

        .photo-table img {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 4px;
            border: none;
        }

        .img-placeholder {
            width: 100%;
            height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
            font-size: 10px;
            text-align: center;
        }

        /* Info Card Styles for Flooded and Collision */
        .info-card {
            display: inline-block;
            padding: 8px 12px;
            margin: 10px 0;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .color-red {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .color-green {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .color-yellow {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }

        .color-orange {
            background-color: #ffeaa7;
            color: #d63031;
            border-color: #fab1a0;
        }

        /* Cetak PDF */
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
            table, tr, td {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        @if($coverImage && $coverImage->image_path && file_exists(public_path($coverImage->image_path)))
            <img src="{{ public_path($coverImage->image_path) }}" alt="Foto Utama">
        @else
            <div class="img-placeholder" style="width:250px; height:250px; font-size:12px;">
                Gambar tidak tersedia
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
                <tr><td>Tahun Pembuatan</td><td>{{ $inspection->car->year }}</td></tr>
                <tr><td>Periode Model</td><td>{{ $inspection->car->production_period ?? '-' }}</td></tr>
                <tr><td>Warna</td><td>{{ $inspection->color }}</td></tr>
                <tr><td>Jarak Tempuh</td><td>{{ $inspection->km }}</td></tr>
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

    @php
        $settings = $inspection->settings ?? [];
        $flooded = $settings['flooded'] ?? 'no';
        $collision = $settings['collision'] ?? 'no';
        $collisionSeverity = $settings['collision_severity'] ?? ''; 
    @endphp

    
    

    @foreach($menu_points->groupBy('inspection_point.component.name') as $componentName => $points)
        <div class="component-block">
            <div class="component-title">{{ $componentName ?? 'Tanpa Komponen' }}</div>


            <div class="component-content">
                                @if ($componentName == 'Interior (Validasi Banjir)')
                    @if ($flooded == 'yes')
                        <div class="info-card color-red">
                            <span>Bekas Banjir</span>
                        </div>
                        @endif
                        @if ($flooded == 'no')
                        <div class="info-card color-green">
                            <span>Bebas Banjir</span>
                        </div>
                        @endif
                @endif
                @if ($componentName == 'Rangka (Validasi Tabrak)')
                    @if ($collision == 'yes')
                        @php
                            $collisionText = '';
                            $collisionColor = 'color-yellow';
                            if ($collisionSeverity === 'minor') {
                                $collisionText = 'Tabrak Ringan';
                                $collisionColor = 'color-yellow';
                            } elseif ($collisionSeverity === 'moderate') {
                                $collisionText = 'Tabrak Sedang';
                                $collisionColor = 'color-orange';
                            } elseif ($collisionSeverity === 'major') {
                                $collisionText = 'Tabrak Berat';
                                $collisionColor = 'color-red';
                            }
                        @endphp
                        <div class="info-card {{ $collisionColor }}">
                            <span>{{ $collisionText }}</span>
                        </div>
                    @else
                    <div class="info-card color-green">
                        <span>Bebas Tabrak</span>
                    </div>
                    @endif
                @endif
                
                @if($componentName == 'Foto Kendaraan')
                    @php
                        $allCarImages = [];
                        foreach($points as $point) {
                            if($point->inspection_point->images) {
                                $allCarImages = array_merge($allCarImages, $point->inspection_point->images->all());
                            }
                        }
                        $columns = 4;
                        $imageChunks = array_chunk($allCarImages, $columns);
                    @endphp
                    @foreach($imageChunks as $chunk)
                        <table class="photo-table car-photos-4">
                            <tr>
                                @foreach($chunk as $img)
                                    <td>
                                        @if($img->image_path && file_exists(public_path($img->image_path)))
                                            <img src="{{ public_path($img->image_path) }}" alt="Foto Kendaraan">
                                        @else
                                            <div class="img-placeholder">Gambar tidak ditemukan</div>
                                        @endif
                                    </td>
                                @endforeach
                                @for($i = count($chunk); $i < $columns; $i++)
                                    <td></td>
                                @endfor
                            </tr>
                        </table>
                    @endforeach
                @else
                    <table class="point-table">
                    @foreach($points as $point)
                        @php
                            $result = $point->inspection_point->results->first();
                            $hasResult = $result && (!empty($result->status) || !empty($result->note));
                            $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;

                            if (!$hasResult && !$hasImage && $inputType !== 'account') {
                                continue;
                            }
                            
                            $inputType = $point->input_type ?? '';
                            $selected = $result->status ?? null;
                            $settings = $point->settings ?? [];
                            $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $selected);
                            $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                            $showImages = (in_array($inputType, ['image', 'imageTOradio']) || ($inputType === 'radio' && $showImageUpload)) && $hasImage;

                            $statusClass = 'status-warning';
                            if (in_array(strtolower($selected), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                $statusClass = 'status-good';
                            } elseif (in_array(strtolower($selected), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                $statusClass = 'status-bad';
                            }
                            
                            $formattedNote = $result->note ?? '';
                            if ($inputType === 'account' && !empty($formattedNote)) {
                                $currencySymbol = $settings['currency_symbol'] ?? 'Rp';
                                $thousandsSeparator = $settings['thousands_separator'] ?? '.';
                                $number = preg_replace('/[^0-9.]/', '', $formattedNote);
                                if (is_numeric($number)) {
                                    $formattedNote = $currencySymbol . ' ' . number_format($number, 0, ',', $thousandsSeparator);
                                }
                            }
                        @endphp
                        
                        <tr>
                            <td class="point-name-cell">{{ $point->inspection_point->name ?? '-' }}</td>
                            <td>
                                @if(in_array($inputType, ['radio', 'imageTOradio']) && !empty($result->status))
                                    <span class="status-badge {{ $statusClass }}">{{ $result->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if(!$showImages && !empty($formattedNote))
                                    <span class="point-note">{{ $formattedNote }}</span>
                                @endif
                            </td>
                        </tr>

                        @if($showImages)
                            <tr>
                                <td colspan="3">
                                    <table class="photo-table other-photos">
                                        <tr>
                                            @foreach($point->inspection_point->images as $index => $img)
                                                <td>
                                                    @if($img->image_path && file_exists(public_path($img->image_path)))
                                                        <img src="{{ public_path($img->image_path) }}" alt="image">
                                                    @else
                                                        <div class="img-placeholder">Gambar tidak ditemukan</div>
                                                    @endif
                                                </td>
                                                @if(($index+1) % 5 == 0) </tr><tr> @endif
                                            @endforeach
                                            @php
                                                $totalOtherImages = $point->inspection_point->images->count();
                                            @endphp
                                            @if($totalOtherImages > 0 && $totalOtherImages % 5 != 0)
                                                @for($i = 0; $i < (5 - ($totalOtherImages % 5)); $i++)
                                                    <td></td>
                                                @endfor
                                            @endif
                                        </tr>
                                    </table>
                                    @if(!empty($formattedNote))
                                        <div class="point-note" style="margin-top: 10px;">{{ $formattedNote }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr class="point-separator-row">
                            <td colspan="3" class="point-separator"></td>
                        </tr>
                    @endforeach
                    </table>
                @endif
            </div>
        </div>
    @endforeach

</body>
</html>