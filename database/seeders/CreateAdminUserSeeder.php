<?php
  
namespace Database\Seeders;

use App\Constants\Roles;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
  
class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Sara', 
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678')
        ]);
    
        $role = Role::where('name', Roles::ADMIN)->first();

        $permissions = Permission::all();
        $role->syncPermissions($permissions);
    
        $user->assignRole($role);
    }
}