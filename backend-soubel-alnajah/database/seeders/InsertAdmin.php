<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InsertAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::firstOrCreate(['name' => 'admin']);

        $admin = User::query()->updateOrCreate([
            'email' => env('SITE_ADMIN_EMAIL', 'admin@sobolnajah.com'),
        ], [
            'name' => [
                'fr' => env('SITE_ADMIN_NAME_FR', 'Site Admin'),
                'ar' => env('SITE_ADMIN_NAME_AR', 'مسؤول الموقع'),
                'en' => env('SITE_ADMIN_NAME_EN', 'Site Admin'),
            ],
            'password' => Hash::make((string) env('SITE_ADMIN_PASSWORD', '12345678')),
            'must_change_password' => filter_var(env('SITE_ADMIN_FORCE_PASSWORD_CHANGE', false), FILTER_VALIDATE_BOOL),
        ]);

        if (!$admin->hasRole('admin')) {
            $admin->attachRole('admin');
        }
    }
}
