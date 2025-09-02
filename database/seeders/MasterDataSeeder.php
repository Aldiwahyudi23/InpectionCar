<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataInspection\Categorie;
use App\Models\DataInspection\Component;
use App\Models\DataInspection\InspectionPoint;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
            // Create default categories
        $categories = [
            ['name' => 'Mo', 'order' => 1],
            ['name' => 'Aldi Wahyudi', 'order' => 2],
        ];

        $categoryMap = [];
        foreach ($categories as $category) {
            $cat = Categorie::firstOrCreate(['name' => $category['name']], $category);
            $categoryMap[$category['name']] = $cat->id;
        }

        // ==================================
        // Create AppMenu khusus kategori "Mo"
        // ==================================
        if (isset($categoryMap['Mo'])) {
            $appMenus = [
                ['name' => 'Dokumen', 'input_type' => 'menu', 'order' => 1],
                ['name' => 'Foto', 'input_type' => 'menu', 'order' => 2],
                ['name' => 'Mesin', 'input_type' => 'menu', 'order' => 3],
                ['name' => 'Eksterior', 'input_type' => 'menu', 'order' => 4],
                ['name' => 'Interior', 'input_type' => 'menu', 'order' => 5],
                ['name' => 'Kerusakan', 'input_type' => 'damage', 'order' => 6],
            ];

            foreach ($appMenus as $menu) {
                \App\Models\DataInspection\AppMenu::firstOrCreate(
                    [
                        'category_id' => $categoryMap['Mo'],
                        'name' => $menu['name'],
                    ],
                    [
                        'input_type' => $menu['input_type'],
                        'order' => $menu['order'],
                        'is_active' => true,
                    ]
                );
            }
        }


        // Create default components
        $components = [
            ['name' => 'Dokumen', 'description' => 'Bagian dokumen kendaraan'],
            ['name' => 'Foto Kendaraan', 'description' => 'Dokumentasi foto kendaraan'],
            ['name' => 'Eksterior', 'description' => 'Bagian luar kendaraan'],
            ['name' => 'Interior', 'description' => 'Bagian dalam kendaraan'],
            ['name' => 'Mesin', 'description' => 'Komponen mesin kendaraan'],
            ['name' => 'Transmisi', 'description' => 'Sistem transmisi kendaraan'],
            ['name' => 'Kelistrikan', 'description' => 'Sistem kelistrikan kendaraan'],
            ['name' => 'Rangka (Validasi Tabrak)', 'description' => 'Struktur rangka kendaraan'],
            ['name' => 'Interior (Validasi Banjir)', 'description' => 'Cek interior terkait banjir'],
            ['name' => 'Kaki Kaki', 'description' => 'Suspensi, roda, rem'],
            ['name' => 'Chassis', 'description' => 'Struktur dasar kendaraan'],
        ];

        $componentMap = [];
        foreach ($components as $component) {
            $comp = Component::firstOrCreate(['name' => $component['name']], $component);
            $componentMap[$component['name']] = $comp->id;
        }

        // =========================
        // Create Inspection Points
        // =========================
        $inspectionPoints = [
           // Dokumen
            'Dokumen' => [
                ['name' => 'STNK', 'description' => 'Surat Tanda Nomor Kendaraan', 'order' => 1],
                ['name' => 'BPKB Asli', 'description' => 'Cek keaslian BPKB', 'order' => 3],
                ['name' => 'BPKB Halaman 1', 'description' => 'Halaman pertama BPKB', 'order' => 4],
                ['name' => 'BPKB Halaman 2', 'description' => 'Halaman kedua BPKB', 'order' => 5],
                ['name' => 'BPKB Halaman 3', 'description' => 'Halaman ketiga BPKB', 'order' => 6],
                ['name' => 'BPKB Halaman 4', 'description' => 'Halaman keempat BPKB', 'order' => 7],
                ['name' => 'No Rangka', 'description' => 'Nomor rangka kendaraan', 'order' => 8],
                ['name' => 'No Mesin', 'description' => 'Nomor mesin kendaraan', 'order' => 9],
                ['name' => 'Pajak Tahunan', 'description' => 'Cek pajak tahunan kendaraan', 'order' => 11],
                ['name' => 'Pajak 5 Tahunan', 'description' => 'Cek pajak 5 tahunan kendaraan', 'order' => 12],
                ['name' => 'Faktur', 'description' => 'Faktur kendaraan', 'order' => 13],
                ['name' => 'NIK Pemilik', 'description' => 'Nomor Induk Kependudukan pemilik kendaraan', 'order' => 14],
                ['name' => 'Form A', 'description' => 'Formulir A (jika ada)', 'order' => 15],
                ['name' => 'SPh Perusahaan', 'description' => 'Surat Pengesahan perusahaan (jika atas nama PT)', 'order' => 16],
                ['name' => 'Buku Service', 'description' => 'Surat Pengesahan perusahaan (jika atas nama PT)', 'order' => 16],
            ],

            // Foto Kendaraan
            'Foto Kendaraan' => [
                ['name' => 'Depan Kanan', 'description' => 'tampak depan kanan kendaraan', 'order' => 1],
                ['name' => 'Depan', 'description' => 'tampak depan kendaraan', 'order' => 2],
                ['name' => 'Depan Kiri', 'description' => 'tampak depan kiri kendaraan', 'order' => 3],
                ['name' => 'Belakang Kanan', 'description' => 'tampak belakang kanan kendaraan', 'order' => 4],
                ['name' => 'Belakang', 'description' => 'tampak belakang kendaraan', 'order' => 5],
                ['name' => 'Belakang Kiri', 'description' => 'tampak belakang kiri kendaraan', 'order' => 6],
                ['name' => 'Bagasi Terbuka', 'description' => 'tampak belakang kiri kendaraan', 'order' => 7],
                ['name' => 'Dashboard', 'description' => 'interior dashboard', 'order' => 8],
                ['name' => 'Interior', 'description' => 'keseluruhan interior kendaraan', 'order' => 9],
                ['name' => 'Kap Mesin Terbuka ', 'description' => 'nomor fisik mesin kendaraan', 'order' => 10],
                ['name' => 'No Fisik Mesin', 'description' => 'nomor fisik mesin kendaraan', 'order' => 11],
                ['name' => 'No Fisik Rangka', 'description' => 'nomor fisik rangka kendaraan', 'order' => 12],
            ],


            // Eksterior
            'Eksterior' => [
                ['name' => 'Kaca Depan', 'description' => 'Kondisi grill depan', 'order' => 1],
                ['name' => 'Grill', 'description' => 'Kondisi grill depan', 'order' => 2],
                ['name' => 'Bemper Depan', 'description' => 'Kondisi bemper depan', 'order' => 3],
                ['name' => 'Lampu Depan', 'description' => 'Lampu depan kiri & kanan', 'order' => 4],
                ['name' => 'Kap Mesin', 'description' => 'Kondisi kap mesin', 'order' => 5],
                ['name' => 'Fender Kiri', 'description' => 'Kondisi grill depan', 'order' => 6],
                ['name' => 'Spion Kiri', 'description' => 'Kondisi spion kiri', 'order' => 7],
                ['name' => 'Pintu Depan Kiri', 'description' => 'Kondisi pintu depan kiri', 'order' => 8],
                ['name' => 'Pintu Belakang Kiri', 'description' => 'Kondisi pintu belakang kiri', 'order' => 9],
                ['name' => 'Quarter Kiri', 'description' => 'Kondisi grill depan', 'order' => 10],
                ['name' => 'Lisplang Kiri', 'description' => 'Kondisi grill depan', 'order' => 11],
                ['name' => 'Bemper Belakang', 'description' => 'Kondisi bemper belakang', 'order' => 12],
                ['name' => 'Lampu Belakang', 'description' => 'Lampu belakang kiri & kanan', 'order' => 13],
                ['name' => 'Bagasi', 'description' => 'Kondisi bagasi', 'order' => 14],
                ['name' => 'Spoiler', 'description' => 'Kondisi grill depan', 'order' => 15],
                ['name' => 'Lisplang Kanan', 'description' => 'Kondisi grill depan', 'order' => 16],
                ['name' => 'Quarter Kanan', 'description' => 'Kondisi grill depan', 'order' => 17],
                ['name' => 'Pintu Belakang Kanan', 'description' => 'Kondisi pintu belakang kanan', 'order' => 18],
                ['name' => 'Pintu Depan Kanan', 'description' => 'Kondisi pintu depan kanan', 'order' => 19],
                ['name' => 'Spion Kanan', 'description' => 'Kondisi spion kanan', 'order' => 20],
                ['name' => 'Fender Kanan', 'description' => 'Kondisi grill depan', 'order' => 21],
                ['name' => 'Atap', 'description' => 'Kondisi atap mobil', 'order' => 22],
            ],

            // Interior
            'Interior' => [
                ['name' => 'Dashboard', 'description' => 'Kondisi dashboard', 'order' => 1],
                ['name' => 'Setir', 'description' => 'Kondisi setir & tombol', 'order' => 2],
                ['name' => 'Kursi', 'description' => 'Kondisi kursi depan', 'order' => 3],
                ['name' => 'Plafon', 'description' => 'Kondisi plafon', 'order' => 6],
                ['name' => 'Door Trim', 'description' => 'Kondisi door trim', 'order' => 7],
                ['name' => 'Karpet', 'description' => 'Kondisi karpet interior', 'order' => 8],
            ],

           // Mesin
            'Mesin' => [
                ['name' => 'Oli Mesin', 'description' => 'Pemeriksaan level dan kondisi oli mesin', 'order' => 1],
                ['name' => 'Mesin', 'description' => 'Pemeriksaan level dan kondisi oli mesin', 'order' => 1],
                ['name' => 'Selang Mesin', 'description' => 'Selang dan klem mesin untuk kebocoran', 'order' => 3],
                ['name' => 'Aki', 'description' => 'Kondisi baterai kendaraan dan terminal', 'order' => 4],
                ['name' => 'Filter Udara', 'description' => 'Kondisi filter udara mesin', 'order' => 5],
                ['name' => 'Busi', 'description' => 'Kondisi dan jarak busi', 'order' => 6],
                ['name' => 'Timing Belt', 'description' => 'Kondisi timing belt dan tensioner', 'order' => 7],
                ['name' => 'Fan Belt', 'description' => 'Kondisi dan ketegangan fan belt', 'order' => 8],
                ['name' => 'Filter Bahan Bakar', 'description' => 'Kondisi filter bahan bakar', 'order' => 9],
                ['name' => 'Injektor/Karburator', 'description' => 'Kondisi sistem pengabutan', 'order' => 10],
                ['name' => 'Knalpot', 'description' => 'Kondisi sistem pembuangan', 'order' => 11],
                ['name' => 'Mounting Mesin', 'description' => 'Kondisi mounting mesin', 'order' => 12],
                // Untuk sistem pendingin
                ['name' => 'Radiator', 'description' => 'Kondisi radiator dan cairan pendingin', 'order' => 2],
                ['name' => 'Selang Radiator', 'description' => 'Kondisi selang atas, bawah, dan heater untuk kebocoran', 'order' => 3],
                ['name' => 'Tutup Radiator', 'description' => 'Kondisi dan fungsi pressure cap radiator', 'order' => 4],
                ['name' => 'Water Pump', 'description' => 'Kondisi dan kebocoran water pump', 'order' => 5],
                ['name' => 'Thermostat', 'description' => 'Fungsi thermostat dalam mengatur sirkulasi coolant', 'order' => 6],
                ['name' => 'Coolant Reservoir', 'description' => 'Level dan kondisi cairan coolant di reservoir', 'order' => 7],
                ['name' => 'Kipas Radiator', 'description' => 'Fungsi motor kipas radiator dan sensor suhu', 'order' => 8]
            ],

            // Transmisi
            'Transmisi' => [
                ['name' => 'Kopling (Manual)', 'description' => 'Kondisi pedal kopling dan performa', 'order' => 1],
                ['name' => 'Tuas Persneling', 'description' => 'Kondisi tuas persneling dan perpindahan gigi', 'order' => 2],
                ['name' => 'ATF (Oli Transmisi)', 'description' => 'Kondisi dan level oli transmisi otomatis', 'order' => 3],
                ['name' => 'Gardan', 'description' => 'Kondisi dan level oli gardan', 'order' => 4],
                ['name' => 'CV Joint', 'description' => 'Kondisi CV joint dan boot', 'order' => 5],
                ['name' => 'Universal Joint', 'description' => 'Kondisi universal joint propeller shaft', 'order' => 6],
                ['name' => 'Kabel Kopling', 'description' => 'Kondisi kabel kopling dan adjustmen', 'order' => 7],
                ['name' => 'Transmisi Mounting', 'description' => 'Kondisi mounting transmisi', 'order' => 9],
                ['name' => 'Transmisi Matic', 'description' => 'Kondisi sistem hidrolik transmisi otomatis', 'order' => 10],
                ['name' => 'Kontrol Transmisi Elektronik (Triptonic/Paddle)', 'description' => 'Pemeriksaan fungsi paddle shift atau mode triptonic pada setir', 'order' => 11],
            ],
           // Kelistrikan
                'Kelistrikan' => [
                    ['name' => 'Lampu Utama (Headlamp)', 'description' => 'Fungsi lampu jauh (high beam) dan dekat (low beam)', 'order' => 1],
                    ['name' => 'Lampu Sein (Turn Signal)', 'description' => 'Fungsi lampu sein depan, belakang, dan side mirror', 'order' => 2],
                    ['name' => 'Lampu Rem (Stop Lamp)', 'description' => 'Fungsi lampu rem dan lampu stop (brake light)', 'order' => 3],
                    ['name' => 'Lampu Mundur (Reverse Light)', 'description' => 'Fungsi lampu putih mundur', 'order' => 4],
                    ['name' => 'Lampu Penerangan Plat Nomor', 'description' => 'Fungsi lampu penerang plat nomor belakang', 'order' => 5],
                    ['name' => 'Lampu Kabut (Fog Lamp)', 'description' => 'Fungsi lampu kabut depan dan belakang', 'order' => 6],
                    ['name' => 'Lampu Interior & Dome', 'description' => 'Fungsi lampu kabin, dome, dan pintu', 'order' => 7],
                    ['name' => 'Lampu Bagasi', 'description' => 'Fungsi lampu penerang bagasi', 'order' => 8],
                    ['name' => 'Wiper & Washer', 'description' => 'Fungsi motor wiper, blade, dan sprayer washer', 'order' => 9],
                    ['name' => 'Klakson', 'description' => 'Fungsi dan volume suara klakson', 'order' => 10],
                    ['name' => 'Power Window', 'description' => 'Fungsi naik-turun semua power window dan master switch', 'order' => 11],
                    ['name' => 'Central Locking', 'description' => 'Fungsi door lock actuator dan remote keyless', 'order' => 12],
                    ['name' => 'Power Mirror', 'description' => 'Fungsi adjust dan fold (jika ada) kaca spion elektrik', 'order' => 13],
                    ['name' => 'Head Unit & Audio System', 'description' => 'Fungsi head unit, speaker, antenna, dan input audio', 'order' => 14],
                    ['name' => 'Power Outlet (Cigar Lighter)', 'description' => 'Fungsi stop kontak listrik 12V', 'order' => 15],
                    ['name' => 'Air Conditioner (AC) Elektrik', 'description' => 'Fungsi blower, kontrol panel, dan actuator mode pintu', 'order' => 16],
                    ['name' => 'Panel Instrumen (Dashboard Cluster)', 'description' => 'Fungsi semua indikator, speedometer, dan warning light', 'order' => 17],
                    ['name' => 'Sistem Pengisian (Charging)', 'description' => 'Tegangan output alternator', 'order' => 18],
                    ['name' => 'Sistem Starter', 'description' => 'Fungsi motor starter dan relay', 'order' => 19],
                    ['name' => 'Aki / Battery', 'description' => 'Tegangan aki, terminal, dan klem masa', 'order' => 20],
                    ['name' => 'Fuse & Relay Box', 'description' => 'Kondisi fisik dan kekencangan fuse dan relay', 'order' => 21],
                    ['name' => 'Wiring Harness', 'description' => 'Visual checking kerapihan dan keutuhan harness', 'order' => 22],
                    ['name' => 'ECU & Modul', 'description' => 'Pemeriksaan error code pada ECU, TCM, BCM, dll', 'order' => 23],
                    ['name' => 'Sensor-sensor', 'description' => 'Fungsi sensor utama (MAP, Crank, O2, dll)', 'order' => 24],
                    ['name' => 'Kabel Busi & Ignition Coil', 'description' => 'Kondisi visual dan resistansi kabel busi/coil', 'order' => 25],
                    ['name' => 'Alarm & Immobilizer', 'description' => 'Fungsi sistem keamanan dan kunci immobilizer', 'order' => 26],
                    ['name' => 'USB Port & Charger', 'description' => 'Fungsi pengisian daya via USB port', 'order' => 27],
                    ['name' => 'Konektivitas (Bluetooth, Hands-free)', 'description' => 'Fungsi pairing dan kualitas suara', 'order' => 28],
                    ['name' => 'Power Seat', 'description' => 'Fungsi adjustmen elektrik pada jok (jika ada)', 'order' => 29],
                    ['name' => 'Steering Switch', 'description' => 'Fungsi tombol pada kemudi (audio, cruise, telp)', 'order' => 30],
                    ['name' => 'Parking Sensor & Camera', 'description' => 'Fungsi sensor parkir dan kamera belakang', 'order' => 31],

                     // Sistem Refrigerasi
                    ['name' => 'Kompresor AC', 'description' => 'Kondisi, kebocoran, dan fungsi kompresor AC', 'order' => 1],
                    ['name' => 'Kondensor AC', 'description' => 'Kondisi dan kebersihan kondensor (depan radiator)', 'order' => 2],
                    ['name' => 'Evaporator AC', 'description' => 'Kondisi evaporator dan housingnya di dalam kabin', 'order' => 3],
                    ['name' => 'Receiver Drier / Accumulator', 'description' => 'Kondisi dan fungsi tabung dryer/accumulator', 'order' => 4],
                    ['name' => 'Expansion Valve / Orifice Tube', 'description' => 'Fungsi katup ekspansi', 'order' => 5],
                    ['name' => 'Selang & O-ring AC', 'description' => 'Pemeriksaan kebocoran pada selang dan sambungan AC', 'order' => 6],
                    ['name' => 'Refrigerant (Freon)', 'description' => 'Tekanan dan jumlah refrigerant', 'order' => 7],
                    
                    // Sistem Kelistrikan & Kontrol
                    ['name' => 'Blower Motor', 'description' => 'Kecepatan dan fungsi motor blower', 'order' => 8],
                    ['name' => 'Panel Kontrol AC', 'description' => 'Fungsi semua tombol, mode, dan pengatur suhu', 'order' => 9],
                    ['name' => 'Actuator Blend Door', 'description' => 'Fungsi actuator pengatur arah dan suhu udara', 'order' => 10],
                    ['name' => 'Sensor Suhu Kabin', 'description' => 'Fungsi sensor suhu untuk AC otomatis', 'order' => 11],
                ],

            // Rangka (Tabrak)
            'Rangka (Validasi Tabrak)' => [
                ['name' => 'Bulkhead', 'description ' => 'Struktur bulkhead', 'order' => 1],
                ['name' => 'Bulkhead Kanan', 'description ' => 'Struktur bulkhead', 'order' => 1],
                ['name' => 'Suport Kanan', 'description ' => 'Struktur bulkhead', 'order' => 1],
                ['name' => 'Bulkhead Kiri', 'description ' => 'Struktur bulkhead', 'order' => 1],
                ['name' => 'Suport Kiri', 'description ' => 'Struktur bulkhead', 'order' => 1],
                ['name' => 'Suport Bawah', 'description ' => 'Struktur bulkhead', 'order' => 1],
                ['name' => 'Cross Member', 'description ' => 'Struktur bulkhead', 'order' => 1],
                ['name' => 'Pilar A Kiri', 'description' => 'Kondisi pilar A kiri', 'order' => 2],
                ['name' => 'Pilar A Kanan', 'description' => 'Kondisi pilar A kanan', 'order' => 3],
                ['name' => 'Pilar B Kiri', 'description' => 'Kondisi pilar B kiri', 'order' => 4],
                ['name' => 'Pilar B Kanan', 'description' => 'Kondisi pilar B kanan', 'order' => 5],
                ['name' => 'Pilar C Kiri', 'description' => 'Kondisi pilar C kiri', 'order' => 6],
                ['name' => 'Pilar C Kanan', 'description' => 'Kondisi pilar C kanan', 'order' => 7],
            ],

            // Validasi Banjir
            'Interior (Validasi Banjir)' => [
                ['name' => 'Kolom Setir', 'description' => 'Cek karat & bekas air di kolom setir', 'order' => 1],
                ['name' => 'Kolong Jok', 'description' => 'Cek karat & lumpur di bawah jok', 'order' => 2],
                ['name' => 'Lighter', 'description' => 'Kondisi kabel & soket interior', 'order' => 3],
                ['name' => 'Klik Sabuk', 'description' => 'Kondisi kabel & soket interior', 'order' => 3],
                ['name' => 'Karpet Dasar', 'description' => 'Kondisi karpet dasar', 'order' => 4],
            ],

          // Kaki Kaki
'Kaki Kaki' => [
    // Kelompok: Ban & Roda
    ['name' => 'Roda Depan Kiri', 'description' => 'Check kedalaman alur, keausan, dan kerusakan Roda depan', 'order' => 2],
    ['name' => 'Roda Belakang Kiri', 'description' => 'Check kedalaman alur, keausan, dan kerusakan Roda belakang', 'order' => 3],
    ['name' => 'Roda Depan Kanan', 'description' => 'Check kedalaman alur, keausan, dan kerusakan Roda depan', 'order' => 2],
    ['name' => 'Roda Belakang Kanan', 'description' => 'Check kedalaman alur, keausan, dan kerusakan Roda belakang', 'order' => 3],
    
    // Kelompok: Suspensi
    ['name' => 'Shock Absorber Depan', 'description' => 'Kondisi, kebocoran oli, dan kekakuan shock depan', 'order' => 6],
    ['name' => 'Shock Absorber Belakang', 'description' => 'Kondisi, kebocoran oli, dan kekakuan shock belakang', 'order' => 7],
    ['name' => 'Upper & Lower Ball Joint', 'description' => 'Kondisi dan kelonggaran ball joint', 'order' => 9],
    ['name' => 'Bushing Arm (Wishbone/Trailing Arm)', 'description' => 'Kondisi dan keausan busing control arm', 'order' => 10],
    ['name' => 'Stabilizer Link (Sway Bar Link)', 'description' => 'Kondisi dan kelonggaran link stabilizer', 'order' => 11],
    ['name' => 'Bushing Stabilizer Bar', 'description' => 'Kondisi busing penahan stabilizer bar', 'order' => 12],
    ['name' => 'Strut Mount/Bearing', 'description' => 'Kondisi upper strut mount dan bearing', 'order' => 13],
    
    // Kelompok: Sistem Kemudi
    ['name' => 'Rack & Pinion Steering', 'description' => 'Kondisi, kebocoran, dan kelonggaran rack setir', 'order' => 14],
    ['name' => 'Power Steering Fluid', 'description' => 'Level dan kondisi oli power steering', 'order' => 15],
    ['name' => 'Tie Rod End', 'description' => 'Kondisi dan kelonggaran tie rod end dalam & luar', 'order' => 16],
    ['name' => 'Drag Link', 'description' => 'Kondisi dan kelonggaran drag link (jika ada)', 'order' => 17],
    ['name' => 'Idler Arm', 'description' => 'Kondisi dan kelonggaran idler arm (jika ada)', 'order' => 18],
    ['name' => 'Pompa Power Steering', 'description' => 'Kondisi dan kebocoran pompa power steering', 'order' => 19],
    ['name' => 'Selang Power Steering', 'description' => 'Kondisi dan kebocoran selang pressure & return', 'order' => 20],
    
    // Kelompok: Sistem Rem
    ['name' => 'Rem Depan', 'description' => 'Ketebalan dan kondisi kampas rem depan', 'order' => 21],
    ['name' => 'Rem Belakang', 'description' => 'Ketebalan dan kondisi kampas rem depan', 'order' => 21],
    ['name' => 'Brake Booster', 'description' => 'Fungsi dan kebocoran vacuum brake booster', 'order' => 26],
    ['name' => 'Master Cylinder Rem', 'description' => 'Kondisi dan kebocoran master silinder rem', 'order' => 27],
    ['name' => 'Selang Rem (Brake Hose)', 'description' => 'Kondisi, kebocoran, dan kekakuan selang rem', 'order' => 28],
    ['name' => 'Piringan Selak (Brake Padlet)', 'description' => 'Kondisi piringan selak rem belakang', 'order' => 29],
    ['name' => 'Brem Tangan (Parking Brake)', 'description' => 'Fungsi dan adjustment rem tangan', 'order' => 30],
    ['name' => 'Brake Fluid', 'description' => 'Level dan kondisi cairan rem (minyak rem)', 'order' => 31],
    
    // Kelompok: Roda & Propeller Shaft (4WD/RWD)
    ['name' => 'CV Joint & Boot', 'description' => 'Kondisi, kelonggaran, dan kebocoran boot CV joint', 'order' => 32],
    ['name' => 'Universal Joint (Propeller Shaft)', 'description' => 'Kondisi dan kelonggaran U-joint propeller shaft', 'order' => 33],
    ['name' => 'Carrier Bearing', 'description' => 'Kondisi carrier bearing propeller shaft', 'order' => 34],
    ['name' => 'Differential', 'description' => 'Kondisi, kebocoran oli, dan suara pada gardan', 'order' => 35],
],

            // Chassis
            'Chassis' => [
                ['name' => 'Long Member', 'description' => 'Struktur long member', 'order' => 1],
                ['name' => 'Cross Member', 'description' => 'Struktur cross member', 'order' => 2],
                ['name' => 'Underbody', 'description' => 'Kondisi bawah kendaraan', 'order' => 3],
            ],
        ];

        foreach ($inspectionPoints as $componentName => $points) {
            $componentId = $componentMap[$componentName] ?? null;
            if ($componentId) {
                foreach ($points as $point) {
                    InspectionPoint::firstOrCreate(
                        ['component_id' => $componentId, 'name' => $point['name']],
                        [
                            'description' => $point['description'],
                            'order' => $point['order'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        $this->command->info('Master data seeded successfully with inspection points!');
    }
}
