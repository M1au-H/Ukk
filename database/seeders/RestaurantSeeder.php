<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Restoran',
            'email' => 'admin@restoran.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Staff Dapur',
            'email' => 'kitchen@restoran.test',
            'password' => Hash::make('password'),
            'role' => 'kitchen',
        ]);

        foreach (range(1, 5) as $number) {
            DiningTable::create([
                'table_number' => (string) $number,
                'table_token' => DiningTable::generateToken(),
                'is_active' => true,
            ]);
        }

        $mainCourse = Category::create(['name' => 'Main Course', 'sort_order' => 1]);
        $beverage = Category::create(['name' => 'Beverage', 'sort_order' => 2]);

        $nasiGoreng = Menu::create([
            'category_id' => $mainCourse->id,
            'name' => 'Nasi Goreng Spesial',
            'description' => 'Nasi goreng dengan ayam, telur, dan sayuran.',
            'price' => 25000,
            'is_active' => true,
            'is_available' => true,
            'supports_spicy_level' => true,
        ]);

        $spicyGroup = $nasiGoreng->optionGroups()->create([
            'name' => 'Level Pedas',
            'selection_type' => 'single',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        foreach (['Tidak Pedas', 'Sedang', 'Pedas', 'Sangat Pedas'] as $i => $label) {
            $spicyGroup->values()->create(['label' => $label, 'extra_price' => 0, 'sort_order' => $i]);
        }

        $toppingGroup = $nasiGoreng->optionGroups()->create([
            'name' => 'Topping',
            'selection_type' => 'multiple',
            'is_required' => false,
            'sort_order' => 2,
        ]);
        $toppingGroup->values()->create(['label' => 'Telur', 'extra_price' => 3000, 'sort_order' => 1]);
        $toppingGroup->values()->create(['label' => 'Sosis', 'extra_price' => 5000, 'sort_order' => 2]);

        Menu::create([
            'category_id' => $mainCourse->id,
            'name' => 'Steak',
            'description' => 'Steak sirloin dengan saus lada hitam.',
            'price' => 65000,
            'is_active' => true,
            'is_available' => true,
            'supports_spicy_level' => false,
        ]);

        Menu::create([
            'category_id' => $beverage->id,
            'name' => 'Es Teh',
            'description' => 'Teh manis dingin.',
            'price' => 8000,
            'is_active' => true,
            'is_available' => false,
            'supports_spicy_level' => false,
        ]);
    }
}
