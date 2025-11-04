<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'id' => config('app.root_permission_id', 42069),
                'name' => 'root',
                'slug' => 'root',
                'routes' => ['*'],
                'description' => 'Root user with all permissions'
            ],
            [
                'id' => 2,
                'name' => 'user',
                'slug' => 'user',
                'routes' => ['user.'],
                'description' => 'Access to user management'
            ],
            [
                'id' => 3,
                'name' => 'permission',
                'slug' => 'permission',
                'routes' => ['permission.'],
                'description' => 'Access to permission management'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
