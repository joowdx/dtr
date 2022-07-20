<?php

namespace Database\Seeders;

use App\Models\Scanner;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ScannerSeeder::class,
        ]);

        User::all()->each(fn ($e) => $e->scanners()->sync(Scanner::all()->map->id));
    }
}
