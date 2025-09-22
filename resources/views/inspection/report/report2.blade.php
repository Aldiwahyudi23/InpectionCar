<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
    
    <table class="header-table" style="margin-bottom: 20px;">
        <tr>
            <td style="width: 50%;">
                <h1 style="font-size: 20px; margin: 0;">Laporan Inspeksi Kendaraan</h1>
            </td>
            <td style="width: 50%; text-align: right; color: #555;">
                <p style="margin: 0; font-size: 13px;">Tanggal: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d-m-Y') }}</p>
                <p style="margin: 0; font-size: 13px;">Waktu: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('H:i:s') }}</p>
            </td>
        </tr>
    </table>
    
    @php
        $conclusionSettings = $inspection->settings['conclusion'] ?? [];
        $flooded = $conclusionSettings['flooded'] ?? 'no';
        $collision = $conclusionSettings['collision'] ?? 'no';
        $collisionSeverity = $conclusionSettings['collision_severity'] ?? '';
    @endphp

    <table class="main-info-table" style="margin-bottom: 20px;">
        <tr>
            <td style="width: 250px;">
                @if($coverImage && $coverImage->image_path && file_exists(public_path($coverImage->image_path)))
                    <img src="{{ public_path($coverImage->image_path) }}" alt="Foto Utama" style="width: 220px; height: 220px; object-fit: cover; border: 1px solid #ccc;">
                @else
                    <div style="width: 220px; height: 220px; background: #f5f5f5; border: 1px dashed #bbb; text-align: center; color: #666; font-size: 12px;">
                        <span style="display: block; margin-top: 50%; transform: translateY(-50%);">Gambar tidak tersedia</span>
                    </div>
                @endif
                <h3 style="margin: 5px 0 15px; font-size: 16px;">{{ $inspection->car_name }}</h3>
            </td>
            <td style="padding-left: 20px;">
                @if ($inspection->car_id)
                <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Nomor Polisi</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->plate_number }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Merek</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->brand->name }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Model</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->model->name }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Tipe</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->type->name }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">CC</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->cc }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Bahan Bakar</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->fuel_type }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Transmisi</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->transmission }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Tahun Pembuatan</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->year }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; width: 140px; font-size: 13px;">Periode Model</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px;">{{ $inspection->car->production_period ?? '-' }}</td>
                            </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>

    @if($flooded == 'yes' || $collision == 'yes')
    <table style="width: 100%; margin: 15px 0; border-collapse: collapse; text-align: center;">
        <tr>
            <td style="width: 50%; padding: 6px;">
                @if ($flooded === 'yes')
                    <img src="{{ public_path('images/icons/banjir.png') }}" alt="Banjir" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: #dc3545;">Bekas Banjir</p>
                @else
                    <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: #28a745;">Bebas Banjir</p>
                @endif
            </td>
            <td style="width: 50%; padding: 6px;">
                @if ($collision == 'yes')
                    @php
                        $collisionImage = public_path('images/icons/ringan.png');
                        $collisionText = 'Tabrak Ringan';
                        $collisionColor = '#ffc107';
                        if ($collisionSeverity === 'moderate') {
                            $collisionImage = public_path('images/icons/beruntun.png');
                            $collisionText = 'Tabrak Beruntun';
                            $collisionColor = '#fd7e14';
                        } elseif ($collisionSeverity === 'heavy') {
                            $collisionImage = public_path('images/icons/berat.png');
                            $collisionText = 'Tabrak Berat';
                            $collisionColor = '#dc3545';
                        }
                    @endphp
                    <img src="{{ $collisionImage }}" alt="Tabrak" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: {{ $collisionColor }};">{{ $collisionText }}</p>
                @else
                    <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: #28a745;">Bebas Tabrak</p>
                @endif
            </td>
        </tr>
    </table>
    @endif

    @if($inspection->notes)
    <div style="margin: 15px 0; padding: 12px; background: #f9f9f9; border-left: 4px solid #0d98d8;">
        <h3 style="margin: 0 0 6px; font-size: 15px;">Kesimpulan Inspeksi:</h3>
        <div style="margin: 0; font-size: 14px; line-height: 1.8;">{!! $inspection->notes !!}</div>
    </div>
    @endif

    @foreach($menu_points->groupBy('inspection_point.component.name') as $componentName => $points)
    @php
        $hasData = false;
        if ($componentName == 'Interior (Validasi Banjir)' && $flooded == 'yes') {
            $hasData = true;
        } elseif ($componentName == 'Rangka (Validasi Tabrak)' && $collision == 'yes') {
            $hasData = true;
        } elseif ($componentName == 'Foto Kendaraan') {
            $hasData = $points->flatMap(fn($p) => $p->inspection_point->images)->isNotEmpty();
        } else {
            foreach ($points as $point) {
                $result = $point->inspection_point->results->first();
                $hasResult = $result && (!empty($result->status) || !empty($result->note));
                $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;
                if ($hasResult || $hasImage) {
                    $hasData = true;
                    break;
                }
            }
        }
    @endphp

    @if($hasData)

        @if(in_array($componentName, ['Dokumen', 'Foto Kendaraan', 'Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)']))
            <div style="page-break-before: always;"></div>
        @endif
        <div class="section-box" style="border: 1px solid #0d98d8; border-radius: 10px 10px 0 0; margin-bottom: 20px; overflow: hidden;">
            <div style="font-size: 16px; font-weight: bold; background: #0d98d8; padding: 8px; border-bottom: 1px solid #07a9e9; color: white; position: relative;">
                {{ $componentName ?? 'Tanpa Komponen' }}
            </div>

            <div style="padding: 10px;">
                @if ($componentName == 'Interior (Validasi Banjir)')
                    <div style="text-align: center; margin-bottom: 15px;">
                        @if ($flooded === 'yes')
                            <img src="{{ public_path('images/icons/banjir.png') }}" alt="Banjir" style="width: 80px; height: 80px;">
                            <p style="font-weight: bold; font-size: 15px; color: #dc3545;">Bekas Banjir</p>
                        @else
                            <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman" style="width: 80px; height: 80px;">
                            <p style="font-weight: bold; font-size: 15px; color: #28a745;">Bebas Banjir</p>
                        @endif
                    </div>
                @endif
                @if ($componentName == 'Rangka (Validasi Tabrak)')
                    <div style="text-align: center; margin-bottom: 15px;">
                        @if ($collision == 'yes')
                            @php
                                $collisionImage = public_path('images/icons/ringan.png');
                                $collisionText = 'Tabrak Ringan';
                                $collisionColor = '#ffc107';
                                if ($collisionSeverity === 'moderate') {
                                    $collisionImage = public_path('images/icons/beruntun.png');
                                    $collisionText = 'Tabrak Beruntun';
                                    $collisionColor = '#fd7e14';
                                } elseif ($collisionSeverity === 'heavy') {
                                    $collisionImage = public_path('images/icons/berat.png');
                                    $collisionText = 'Tabrak Berat';
                                    $collisionColor = '#dc3545';
                                }
                            @endphp
                            <img src="{{ $collisionImage }}" alt="Tabrak" style="width: 80px; height: 80px;">
                            <p style="font-weight: bold; font-size: 15px; color: {{ $collisionColor }};">{{ $collisionText }}</p>
                        @else
                            <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman" style="width: 80px; height: 80px;">
                            <p style="font-weight: bold; font-size: 15px; color: #28a745;">Bebas Tabrak</p>
                        @endif
                    </div>
                @endif

                @if($componentName == 'Foto Kendaraan')
                    @php
                        $allCarImages = [];
                        foreach($points as $point) {
                            if($point->inspection_point->images) {
                                $allCarImages = array_merge($allCarImages, $point->inspection_point->images->all());
                            }
                        }
                        $columns = 4; // Menggunakan 4 kolom seperti kode Anda
                        $imageChunks = array_chunk($allCarImages, $columns);
                    @endphp
                    @if(count($imageChunks) > 0)
                        @foreach($imageChunks as $chunk)
                            <table class="image-table" style="margin-bottom: 10px;">
                                <tr>
                                    @foreach($chunk as $img)
                                        <td style="width: 25%;">
                                            @if($img->image_path && file_exists(public_path($img->image_path)))
                                                <div class="image-container">
                                                    <img src="{{ public_path($img->image_path) }}" alt="Foto Kendaraan">
                                                </div>
                                            @else
                                                <div class="image-placeholder">
                                                    <span>Gambar tidak ditemukan</span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                    @for($i = count($chunk); $i < $columns; $i++)
                                        <td style="width: 25%;">
                                            <div class="image-placeholder">
                                                <span></span>
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                            </table>
                        @endforeach
                    @endif
                @else
                    @foreach($points as $point)
                        @php
                            $result = $point->inspection_point->results->first();
                            $hasResult = $result && (!empty($result->status) || !empty($result->note));
                            $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;
                            
                            $inputType = $point->input_type ?? '';
                            $selected = $result->status ?? null;
                            $settings = $point->settings ?? [];
                            $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $selected);
                            $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                            $showTextarea = $selectedOption['settings']['show_textarea'] ?? false;
                            $showImages = (in_array($inputType, ['image', 'imageTOradio']) || ($inputType === 'radio' && $showImageUpload)) && $hasImage;
                            $showNote = !empty($result->note);
                            $isRadioType = in_array($inputType, ['radio', 'imageTOradio']);
                            $showNoteForRadio = $isRadioType && $showTextarea;
                            
                            if (!$hasResult && !$showImages) {
                                continue;
                            }

                            $statusColor = '#ffc107'; // default for warning
                            if (in_array(strtolower($selected), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                $statusColor = '#28a745';
                            } elseif (in_array(strtolower($selected), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                $statusColor = '#dc3545';
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
                        
                        <div style="margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 35%; padding: 4px; vertical-align: top; font-weight: bold; font-size: 14px;">{{ $point->inspection_point->name ?? '-' }}</td>
                                    <td style="width: 20%; padding: 4px; vertical-align: top; font-size: 14px;">
                                        @if($isRadioType && !empty($result->status))
                                            <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: {{ $statusColor }};">{{ $result->status }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 4px; vertical-align: top; font-size: 14px;">
                                        @if ($showNote && (!$isRadioType || $showNoteForRadio) && !$showImages)
                                            <span style="font-style: italic; color: #555; font-size: 13px;">{{ $formattedNote }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            
                            @if($showImages)
                                <div style="margin-top: 10px;">
                                    <table class="image-table">
                                        @php
                                            $images = $point->inspection_point->images;
                                            $columns = 5;
                                            $imageChunks = $images->count() > 0 ? array_chunk($images->all(), $columns) : [[]];
                                        @endphp
                                        @foreach($imageChunks as $chunk)
                                            <tr>
                                                @foreach($chunk as $img)
                                                    <td style="width: 20%;">
                                                        @if($img->image_path && file_exists(public_path($img->image_path)))
                                                            <div class="image-container">
                                                                <img src="{{ public_path($img->image_path) }}" alt="image">
                                                            </div>
                                                        @else
                                                            <div class="image-placeholder">
                                                                <span>Gambar tidak ditemukan</span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                @for ($i = count($chunk); $i < $columns; $i++)
                                                    <td style="width: 20%;">
                                                        <div class="image-placeholder">
                                                            <span></span>
                                                        </div>
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                           

                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 4px; vertical-align: top; font-size: 14px;">
                                        @if ($showNote && (!$isRadioType || $showNoteForRadio))
                                            <span style="font-style: italic; color: #555; font-size: 13px;">{{ $formattedNote }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                             @endif

                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif
    @endforeach

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr>
            <td style="width: 50%;">
                <p style="margin: 0; font-size: 13px;">Laporan ini dibuat secara otomatis.</p>
            </td>
            <td style="width: 50%; text-align: right;">
                <p style="margin: 0; font-size: 13px;">Terima kasih telah menggunakan layanan kami.</p>
            </td>
        </tr>
    </table>
</body>
</html>