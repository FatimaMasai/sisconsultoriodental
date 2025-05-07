<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Fátima Chamo Masai',
            'email' => 'fatima@gmail.com',
            'password' => bcrypt('password'),
        ])->assignRole('Admin');
        
        User::factory(99)->create();

    }
}
