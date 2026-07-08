<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'super-admin']);

        // Berikan ke User pertama (Ganti ID sesuai user Anda)
        $user = User::find(11);
        $user->assignRole($role);
    }
}
