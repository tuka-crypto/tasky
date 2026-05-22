<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class adminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name'   => 'tuka',
            'last_name'    => 'mubark',
            'email' => 'ttuk9236@gmail.com',
            'password'     => Hash::make('123456'),
            'role_id'         => 1,
            'is_approved'  => true,
            'date_of_birth'=> '1990-01-01',
            'gender'=>'woman'
        ]);
    }
}
