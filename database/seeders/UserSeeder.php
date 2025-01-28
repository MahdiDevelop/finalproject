<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // بررسی وجود کاربر در جدول
        if (User::count() == 0) {
            // ایجاد کاربر پیش‌فرض
            User::create([
                'name' => 'Default User',
                'category' => 'admin',
                'password' => Hash::make('password'), // رمز عبور
            ]);
        }
    }
}
