<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interests = ['Reading', 'Cooking', 'Watching TV', 'Basketball'];
        
        foreach ($interests as $interest) {
            DB::table('interests')->insert([
                'name' => $interest,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
