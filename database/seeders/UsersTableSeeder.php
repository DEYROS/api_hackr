<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Insérer l'utilisateur
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Adrien PERROT',
            'email' => 'perrotadrien@yahoo.com',
            'email_verified_at' => null,
            'password' => '$2y$12$8O19hRhQM9HTRkg5gyt7n.ihRbXzrESjOLKTEjURPfS9ERgdS/kwS', // Assurez-vous que c'est le bon hash
            'admin' => 1,
            'created_at' => '2024-10-11 09:25:05',
            'updated_at' => '2024-10-11 09:25:05',
        ]);

        // Insérer les fonctionnalités associées
        $userFunctionalities = [];
        for ($i = 1; $i <= 10; $i++) {
            $userFunctionalities[] = [
                'id' => $i, // ID auto-incrémenté
                'user_id' => 1,
                'functionality_id' => $i,
            ];
        }

        DB::table('user_functionalities')->insert($userFunctionalities);
    }
}
