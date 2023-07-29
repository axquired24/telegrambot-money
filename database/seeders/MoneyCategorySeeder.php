<?php

namespace Database\Seeders;

use App\Models\MoneyCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MoneyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenses = [
            ['Harian', '#c92239'],
            ['Hiburan', '#c96d22'],
            ['Gift', '#7922c9'],
            ['Minus', '#c922ae'],
        ];

        $incomes = [
            ['ATM', '#3cc94d'],
            ['Istri/Suami', '#70c93c'],
        ];

        foreach ($expenses as $expense) {
            MoneyCategory::create([
                'color' => $expense[1],
                'name' => $expense[0],
                'is_expense' => true
            ]);
        }

        foreach ($incomes as $income) {
            MoneyCategory::create([
                'color' => $income[1],
                'name' => $income[0],
                'is_expense' => false
            ]);
        }
    }
}
