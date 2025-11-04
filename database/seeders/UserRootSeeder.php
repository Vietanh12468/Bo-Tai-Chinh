<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRootSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'id' => 42069,
            'name' => 'Admin',
            'slug' => 'admin',
            'phone' => '+84912345678',
            'email' => 'Admin@mail.com',
            'password' => Hash::make('123@123a'),
        ]);

        $user->permissions()->sync([config('app.root_permission_id')]); // assign root permission
    }
}
