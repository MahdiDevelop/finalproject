<?php

namespace Database\Seeders;
use App\Models\Settings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class settingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        if (Settings::count() == 0) {
            // ایجاد کاربر پیش‌فرض
            Settings::create([
                'language' => 'English',
                'date' => 'English',
                'company_pic'=>'null',
                'company_name'=>'Company Name',
                'description'=>'description',
                'address'=>'address',
                'phone'=>'phone',
                'email'=>'exaple@example.com',
            ]);
        }
    }
}
