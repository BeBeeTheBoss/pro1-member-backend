<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'emp_code' => '000-000583',
                'name' => 'BeBee',
                'password' => bcrypt('bebee7121'),
                'role' => 'Senior SD Assistant',
                'department' => 'System Development',
            ]
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        }
    }
}
