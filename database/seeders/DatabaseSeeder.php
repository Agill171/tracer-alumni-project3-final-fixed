<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator Tracer Alumni',
            'email' => 'admin@traceralumni.test',
        ]);
    }
}
