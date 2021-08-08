<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attraction;
use App\Models\User;

class AttractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Attraction::factory()->count(10)->create();
        $users = User::factory()
                ->count(4)
                ->create()->pluck('id');
        foreach (Attraction::all() as $attraction) {
            
            $attraction->users()->attach($users);
        }
    }
}
