<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create_student','update_student','delete_student','list_students','serach_student',
            'create_subject' ,'delete_subject', 'ubdate_subject','show_subjects',
            'create_techer' , 'delete_teacher',
            'write_note','update_note','delete_note','show_note',
            'print_reports',
            'create_attendance' , 'update_attendance','delete_attendance','show_attendance',
            'create_Subscription' , 'update_Subscription' ,'delete_Subscription' , 'show_Subscription',
             'create_Evaluation' , 'update_Evaluation' , 'delete_Evaluation' , 'show_Evaluation',
             'send_whatsApp'

        ];

        foreach ($permissions as $permission){
            Permission::firstOrCreate(['name'=> $permission ,'guard_name' => 'api' ]);
        }

        $admin = Role::firstOrCreate(['name'=> 'admin','guard_name' => 'api']);
        $teacher = Role::firstOrCreate(['name' => 'teacher','guard_name' => 'api']);

        $admin->givePermissionTo($permissions);
        $teacher->givePermissionTo
        (['create_Evaluation' ,
         'update_Evaluation' ,
         'delete_Evaluation' ,
         'show_Evaluation',
         'list_students',
         'create_attendance' ,
         'update_attendance',
         'delete_attendance',
         'show_attendance',]);
    }
}
