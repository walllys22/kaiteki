<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('permission_role')->delete();
        
        // Root
        $role = Role::where('name', 'admin')->firstOrFail();
        $permissions = Permission::all();
        $role->permissions()->sync($permissions->pluck('id')->all());



        $role = Role::where('name', 'administrador')->firstOrFail();
        $permissions = Permission::whereRaw('table_name = "admin" or
                                            `key` = "add_egressdonor" or


                                            table_name = "people" or
                                            table_name = "roles" or
                                            table_name = "users" or
                                            table_name = "settings" or
                                            
                                            `key` = "browse_clear-cache"')->get();
        $role->permissions()->sync($permissions->pluck('id')->all());
    }
}
