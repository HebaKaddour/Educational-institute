<?php

namespace Database\Seeders;

use App\Models\EvaluationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvaluationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EvaluationType::insert([
[
        'name' => 'quiz',
        'label' => 'اختبار جزئي',
        'max_score' => 10,
        'uses_score' => true,
        'uses_status' => false,
        'max_count' => 50,
    ],
    [
        'name' => 'exam',
        'label' => 'اختبار شامل',
        'max_score' => 100,
        'uses_score' => true,
        'uses_status' => false,
        'max_count' => 10,
    ],
    [
        'name' => 'homework',
        'label' => 'واجب',
        'max_score' => null,
        'uses_score' => false,
        'uses_status' => true,
        'max_count' => 144,
    ],
        ]);
    }
}
