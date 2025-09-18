<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"Admin","guard_name":"web","permissions":[
        "access dashboard",
        "view inspections",
        "start inspections",
        "create inspections",
        "cancel inspections",
        "store inspection results",
        "update vehicle details",
        "update conclusion",
        "upload images",
        "delete images",
        "final submit inspections",
        "review inspections",
        "download pdf reports",
        "send email reports",
        "view cars",
        "create cars",
        "manage brands",
        "manage models","manage types","manage car details","view bantuan","view coordinator dashboard","assign inspections","update inspection status"]},{"name":"coordinator","guard_name":"web","permissions":["access dashboard","view inspections","start inspections","create inspections","cancel inspections","store inspection results","update vehicle details","update conclusion","upload images","delete images","final submit inspections","review inspections","download pdf reports","send email reports","view cars","create cars","manage brands","manage models","manage types","manage car details","view bantuan"]},{"name":"inspector","guard_name":"web","permissions":["access dashboard","view inspections","start inspections","create inspections","cancel inspections","store inspection results","update vehicle details","update conclusion","upload images","delete images","final submit inspections","review inspections","download pdf reports","send email reports","view cars","create cars","manage brands","manage models","manage types","manage car details","view bantuan"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
