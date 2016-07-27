<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            [
                'id' => 'academic_year',
                'value' => '2016 - 2017'
            ],

            [
                'id' => 'email_delivery_body',
                'value' => 'This is an automated email. The attached file is a grading sheet uploaded by an instructor/professor.'
            ],

            [
                'id' => 'email_delivery_subject',
                'value' => 'Grading Sheet - STI College Novaliches'
            ],

            [
                'id' => 'email_delivery_recipient_email',
                'value' => null
            ],

            [
                'id' => 'email_delivery_recipient_name',
                'value' => null
            ],

            [
                'id' => 'google_access_token',
                'value' => null
            ],

            [
                'id' => 'google_refresh_token',
                'value' => null
            ],

            [
                'id' => 'prelim_grade_deadline',
                'value' => '2016-01-01 12:00:00'
            ],
            
            [
                'id' => 'midterm_grade_deadline',
                'value' => '2016-01-01 12:00:00'
            ],
            
            [
                'id' => 'prefinal_grade_deadline',
                'value' => '2016-01-01 12:00:00'
            ],
            
            [
                'id' => 'final_grade_deadline',
                'value' => '2016-01-01 12:00:00'
            ],
            
            [
                'id' => 'period',
                'value' => 'PRELIM'
            ],
            
            [
                'id' => 'semester',
                'value' => '1st semester'
            ]
        ]);
    }
}
