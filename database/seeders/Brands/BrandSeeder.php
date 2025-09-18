<?php

namespace Database\Seeders\Brands;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // Dapatkan semua folder merek
        $brandFolders = File::directories(database_path('seeders/Brands'));
        
        foreach ($brandFolders as $brandFolder) {
            $brandName = basename($brandFolder);
            
            // Dapatkan semua file seeder model untuk merek ini
            $modelSeeders = File::files($brandFolder);
            
            foreach ($modelSeeders as $modelSeeder) {
                $className = pathinfo($modelSeeder, PATHINFO_FILENAME);
                $seederClass = "Database\\Seeders\\Brands\\{$brandName}\\{$className}";
                
                $this->call($seederClass);
            }
        }
    }
}