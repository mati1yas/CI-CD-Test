<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Schema::hasTable('roles') && Schema::hasTable('users')) {
            // Check if the role 'super admin' exists, and if not, create it
            if (!Role::where('name', 'super admin')->exists()) {
                Role::create(['name' => 'super admin']);
            }

            // Find a user by a specific email (adjust this to your need)
            $superAdminEmail = 'matiyassseifu@gmail.com';
            $superAdminUser = User::where('email', $superAdminEmail)->first();

            if(!$superAdminUser) 
                 $user = User::create([
                'name' => "Matiyas Seifu",
                'email' => "matiyassseifu@gmail.com",
                'password' => bcrypt(env('SUPER_ADMIN_PASSWORD')),
            ]);

            // Assign the 'super admin' role to this user if they exist
            if ($superAdminUser && !$superAdminUser->hasRole('super admin')) {
                $superAdminUser->assignRole('super admin');
            }

            // CREATE ANOTHER SUPER ADMIN USER . 
            $superAdminEmail = "nmengesha@et-actionagainsthunger.org";
            $superAdminUser = User::where('email', $superAdminEmail)->first();

            

            if(!$superAdminUser) 
                 $user = User::create([
                'name' => "Nebiyu Esayas Mengesha",
                'email' => "nmengesha@et-actionagainsthunger.org",
                'password' => bcrypt(env('SUPER_ADMIN_PASSWORD')),
            ]);
            

            // Assign the 'super admin' role to this user if they exist
            if ($superAdminUser && !$superAdminUser->hasRole('super admin')) {
                $superAdminUser->assignRole('super admin');
            }
        }
    }
}
