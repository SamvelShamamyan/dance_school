<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use Spatie\Permission\Models\Role;


class UsersTableSeeder extends Seeder
{
    public function run()
    {

         $user = User::create([
            'first_name' => 'Super Admin',
            'last_name' => 'Super Admin',
            'father_name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('12345'), 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->assignRole('super-admin');
    }
}
