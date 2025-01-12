<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FunctionalitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('functionalities')->insert([
            ['id' => 1, 'name' => 'checkpassword'],
            ['id' => 2, 'name' => 'secure_password_generator'],
            ['id' => 3, 'name' => 'verif_email'],
            ['id' => 4, 'name' => 'ddos'],
            ['id' => 5, 'name' => 'spam_mail'],
            ['id' => 6, 'name' => 'crawler'],
            ['id' => 7, 'name' => 'random_image'],
            ['id' => 8, 'name' => 'domains'],
            ['id' => 9, 'name' => 'fake_identity'],
            ['id' => 10, 'name' => 'phishing'],
        ]);
    }
}
