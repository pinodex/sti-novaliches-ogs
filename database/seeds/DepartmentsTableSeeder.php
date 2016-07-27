<?php

use Illuminate\Database\Seeder;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('departments')->insert([
            ['name' => 'Information Technology'],
            ['name' => 'Accounting Technology'],
            ['name' => 'General Education'],
            ['name' => 'Hotel and Restaurant Management'],
            ['name' => 'Business Management'],
        ]);
    }
}
