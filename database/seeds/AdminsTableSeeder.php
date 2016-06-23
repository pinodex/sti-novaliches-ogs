<?php

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'username'      => 'admin',
            'password'      => bcrypt('stinova123'),
            'last_name'     => 'Admin',
            'first_name'    => 'STI',
            'middle_name'   => 'College'
        ]);
    }
}
