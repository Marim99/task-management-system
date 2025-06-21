<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('statuses')->insert([
            ['name' => 'pending', 'label' => 'Pending', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'completed', 'label' => 'Completed', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'canceled', 'label' => 'Canceled', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
