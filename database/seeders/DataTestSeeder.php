<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DataTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'id' => 11,
                'name' => 'Admin',
                'slug' => 'admin',
                'phone' => '+84912356752',
                'email' => 'Admin@mail0.com',
                'password' => Hash::make('123@123a'),
            ],
            [
                'id' => 12,
                'name' => 'Admin',
                'slug' => 'admin',
                'phone' => '+84912345674',
                'email' => 'Admin@mail1.com',
                'password' => Hash::make('123@123a'),
            ],
            [
                'id' => 13,
                'name' => 'Admin',
                'slug' => 'admin',
                'phone' => '+84912345671',
                'email' => 'Admin@mail2.com',
                'password' => Hash::make('123@123a'),
            ],
            [
                'id' => 14,
                'name' => 'Admin',
                'slug' => 'admin',
                'phone' => '+84912345271',
                'email' => 'Admin@mail3.com',
                'password' => Hash::make('123@123a'),
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
        };
    }
}
