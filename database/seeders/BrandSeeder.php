<?php

namespace Database\Seeders;

use App\Models\DataCar\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Toyota',
                'description' => 'Toyota adalah produsen mobil terkemuka asal Jepang yang dikenal dengan keandalan dan efisiensi bahan bakarnya.',
                'logo' => 'toyota.png',
                'is_active' => true
            ],
            [
                'name' => 'Honda',
                'description' => 'Honda adalah manufacturer mobil dan motor terkenal dari Jepang yang inovatif dalam teknologi mesin.',
                'logo' => 'honda.png',
                'is_active' => true
            ],
            [
                'name' => 'Suzuki',
                'description' => 'Suzuki dikenal dengan mobil-mobil compact dan irit bahan bakar yang populer di pasar Indonesia.',
                'logo' => 'suzuki.png',
                'is_active' => true
            ],
            [
                'name' => 'Daihatsu',
                'description' => 'Daihatsu spesialis dalam memproduksi mobil-mobil kecil dan LCGC yang ekonomis.',
                'logo' => 'daihatsu.png',
                'is_active' => true
            ],
            [
                'name' => 'Mitsubishi',
                'description' => 'Mitsubishi dikenal dengan mobil SUV dan pickup yang tangguh serta tahan lama.',
                'logo' => 'mitsubishi.png',
                'is_active' => true
            ],
            [
                'name' => 'Nissan',
                'description' => 'Nissan produsen mobil global dengan teknologi canggih dan desain yang elegan.',
                'logo' => 'nissan.png',
                'is_active' => true
            ],
            [
                'name' => 'Hyundai',
                'description' => 'Hyundai brand Korea Selatan yang menawarkan mobil dengan desain modern dan fitur lengkap.',
                'logo' => 'hyundai.png',
                'is_active' => true
            ],
            [
                'name' => 'KIA',
                'description' => 'KIA dikenal dengan desain yang stylish dan harga yang kompetitif di pasar mobil.',
                'logo' => 'kia.png',
                'is_active' => true
            ],
            [
                'name' => 'Wuling',
                'description' => 'Wuling brand China yang populer dengan mobil MPV dan EV yang terjangkau.',
                'logo' => 'wuling.png',
                'is_active' => true
            ],
            [
                'name' => 'Mercedes-Benz',
                'description' => 'Mercedes-Benz brand luxury Jerman yang simbol prestise dan teknologi canggih.',
                'logo' => 'mercedes.png',
                'is_active' => true
            ],
            [
                'name' => 'BMW',
                'description' => 'BMW brand mobil sport dan luxury dari Jerman yang terkenal dengan performa tinggi.',
                'logo' => 'bmw.png',
                'is_active' => true
            ],
            [
                'name' => 'Audi',
                'description' => 'Audi brand luxury Jerman dengan teknologi Quattro dan desain yang futuristik.',
                'logo' => 'audi.png',
                'is_active' => true
            ]
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['name' => $brand['name']],
                $brand
            );
        }

        $this->command->info('Brands seeded successfully!');
    }
}