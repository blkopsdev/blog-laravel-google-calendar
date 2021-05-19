<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "Admin",
            'email' => 'admin@dev.com',
            'password' => bcrypt('admin123'),
            'created_at' => date("Y-m-d H:i:s")
        ]);
    }
}
