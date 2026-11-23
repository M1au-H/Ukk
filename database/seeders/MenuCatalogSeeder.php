<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Kategori (6 kategori, urutan sort_order 1 - 6)
            $categoriesOrder = [
                'Appetizer' => 1,
                'Soup' => 2,
                'Main Course' => 3,
                'Side Dish' => 4,
                'Beverage' => 5, // Mapping dari 'BEVERAGES' di sumber
                'Dessert' => 6,
            ];

            $categoryModels = [];
            foreach ($categoriesOrder as $name => $sortOrder) {
                $category = Category::firstOrCreate(['name' => $name]);
                $category->sort_order = $sortOrder;
                $category->save();
                $categoryModels[$name] = $category;
            }

            // 2. Data Menu (24 item)
            $catalog = [
                // 1. APPETIZER
                [
                    'category' => 'Appetizer',
                    'name' => 'Crispy Chicken Wings',
                    'description' => 'Sayap ayam goreng renyah dengan bumbu gurih dan saus pilihan.',
                    'price' => 32000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Pilihan Rasa',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Original', 'extra_price' => 0],
                                ['label' => 'Spicy', 'extra_price' => 0],
                                ['label' => 'Extra Spicy', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Wings', 'extra_price' => 15000],
                                ['label' => 'Cheese Sauce', 'extra_price' => 7000],
                                ['label' => 'BBQ Sauce', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Salt', 'extra_price' => 0],
                                ['label' => 'No Sauce', 'extra_price' => 0],
                                ['label' => 'No Sesame', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Appetizer',
                    'name' => 'Mozzarella Cheese Sticks',
                    'description' => 'Keju mozzarella berlapis tepung roti yang digoreng hingga renyah di luar dan meleleh di dalam.',
                    'price' => 28000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Cheese', 'extra_price' => 10000],
                                ['label' => 'Marinara Sauce', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Chili Flakes', 'extra_price' => 0],
                                ['label' => 'No Sauce', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Appetizer',
                    'name' => 'Loaded French Fries',
                    'description' => 'Kentang goreng renyah dengan taburan parsley dan saus keju.',
                    'price' => 25000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Cheese', 'extra_price' => 7000],
                                ['label' => 'Beef Bacon', 'extra_price' => 10000],
                                ['label' => 'Sausage', 'extra_price' => 8000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Salt', 'extra_price' => 0],
                                ['label' => 'No Cheese Sauce', 'extra_price' => 0],
                                ['label' => 'No Parsley', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Appetizer',
                    'name' => 'Chicken Caesar Salad',
                    'description' => 'Selada segar, potongan ayam panggang, croutons, parmesan, dan saus Caesar.',
                    'price' => 35000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Chicken', 'extra_price' => 12000],
                                ['label' => 'Avocado', 'extra_price' => 10000],
                                ['label' => 'Parmesan', 'extra_price' => 6000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Dressing on Side', 'extra_price' => 0],
                                ['label' => 'No Croutons', 'extra_price' => 0],
                                ['label' => 'No Cheese', 'extra_price' => 0],
                                ['label' => 'No Onion', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],

                // 2. SOUP
                [
                    'category' => 'Soup',
                    'name' => 'Creamy Mushroom Soup',
                    'description' => 'Sup jamur dengan tekstur creamy, susu, dan aroma herbs.',
                    'price' => 29000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Garlic Bread', 'extra_price' => 8000],
                                ['label' => 'Extra Mushroom', 'extra_price' => 7000],
                                ['label' => 'Cheese', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Cream', 'extra_price' => 0],
                                ['label' => 'No Pepper', 'extra_price' => 0],
                                ['label' => 'No Parsley', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Soup',
                    'name' => 'French Onion Soup',
                    'description' => 'Sup bawang bombai karamel dengan kaldu gurih, roti panggang, dan keju.',
                    'price' => 31000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Cheese', 'extra_price' => 7000],
                                ['label' => 'Extra Toast', 'extra_price' => 6000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Onion', 'extra_price' => 0],
                                ['label' => 'No Cheese', 'extra_price' => 0],
                                ['label' => 'No Toast', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Soup',
                    'name' => 'Chicken Corn Soup',
                    'description' => 'Sup ayam dan jagung manis dengan kuah lembut dan gurih.',
                    'price' => 27000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Chicken', 'extra_price' => 10000],
                                ['label' => 'Extra Corn', 'extra_price' => 5000],
                                ['label' => 'Egg', 'extra_price' => 6000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Carrot', 'extra_price' => 0],
                                ['label' => 'No Pepper', 'extra_price' => 0],
                                ['label' => 'Less Salt', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],

                // 3. MAIN COURSE
                [
                    'category' => 'Main Course',
                    'name' => 'Grilled Beef Steak',
                    'description' => 'Daging sapi panggang dengan mashed potato, sayuran, dan saus black pepper.',
                    'price' => 115000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Tingkat Kematangan',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Rare', 'extra_price' => 0],
                                ['label' => 'Medium Rare', 'extra_price' => 0],
                                ['label' => 'Medium', 'extra_price' => 0],
                                ['label' => 'Medium Well', 'extra_price' => 0],
                                ['label' => 'Well Done', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Side Dish',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Mashed Potato', 'extra_price' => 0],
                                ['label' => 'French Fries', 'extra_price' => 0],
                                ['label' => 'Rice', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Steak 100g', 'extra_price' => 45000],
                                ['label' => 'Egg', 'extra_price' => 8000],
                                ['label' => 'Mushroom Sauce', 'extra_price' => 8000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Vegetables', 'extra_price' => 0],
                                ['label' => 'No Sauce', 'extra_price' => 0],
                                ['label' => 'Less Butter', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Main Course',
                    'name' => 'Chicken Katsu Curry Rice',
                    'description' => 'Ayam katsu renyah dengan nasi putih dan saus kari Jepang yang gurih.',
                    'price' => 48000,
                    'supports_spicy_level' => true,
                    'options' => [
                        [
                            'name' => 'Tingkat Kepedasan',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Mild', 'extra_price' => 0],
                                ['label' => 'Medium', 'extra_price' => 0],
                                ['label' => 'Hot', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Katsu', 'extra_price' => 18000],
                                ['label' => 'Egg', 'extra_price' => 7000],
                                ['label' => 'Cheese', 'extra_price' => 8000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Rice', 'extra_price' => 0],
                                ['label' => 'No Carrot', 'extra_price' => 0],
                                ['label' => 'No Onion', 'extra_price' => 0],
                                ['label' => 'No Curry Sauce', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Main Course',
                    'name' => 'Chicken Alfredo Pasta',
                    'description' => 'Fettuccine dengan ayam panggang, saus krim, parmesan, dan herbs.',
                    'price' => 52000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Pilihan Pasta',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Fettuccine', 'extra_price' => 0],
                                ['label' => 'Spaghetti', 'extra_price' => 0],
                                ['label' => 'Penne', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Chicken', 'extra_price' => 12000],
                                ['label' => 'Extra Cheese', 'extra_price' => 8000],
                                ['label' => 'Mushroom', 'extra_price' => 7000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Cream', 'extra_price' => 0],
                                ['label' => 'No Garlic', 'extra_price' => 0],
                                ['label' => 'No Mushroom', 'extra_price' => 0],
                                ['label' => 'No Cheese', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Main Course',
                    'name' => 'Classic Beef Burger',
                    'description' => 'Burger dengan beef patty, cheddar, selada, tomat, bawang bombai, dan burger sauce.',
                    'price' => 58000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Pilihan Patty',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Single', 'extra_price' => 0],
                                ['label' => 'Double', 'extra_price' => 0], // Sesuai aturan 6: Double tidak ada harga tambahan tertulis di sumber, extra_price=0
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Patty', 'extra_price' => 20000],
                                ['label' => 'Extra Cheese', 'extra_price' => 7000],
                                ['label' => 'Egg', 'extra_price' => 7000],
                                ['label' => 'Beef Bacon', 'extra_price' => 12000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Onion', 'extra_price' => 0],
                                ['label' => 'No Tomato', 'extra_price' => 0],
                                ['label' => 'No Lettuce', 'extra_price' => 0],
                                ['label' => 'No Sauce', 'extra_price' => 0],
                                ['label' => 'No Pickles', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Main Course',
                    'name' => 'Grilled Salmon Rice',
                    'description' => 'Salmon panggang dengan nasi, sayuran, lemon butter sauce, dan taburan herbs.',
                    'price' => 85000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Side Dish',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Rice', 'extra_price' => 0],
                                ['label' => 'Mashed Potato', 'extra_price' => 0],
                                ['label' => 'French Fries', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Salmon 100g', 'extra_price' => 35000],
                                ['label' => 'Avocado', 'extra_price' => 10000],
                                ['label' => 'Egg', 'extra_price' => 7000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Lemon', 'extra_price' => 0],
                                ['label' => 'No Vegetables', 'extra_price' => 0],
                                ['label' => 'Sauce on Side', 'extra_price' => 0],
                                ['label' => 'Less Butter', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],

                // 4. SIDE DISH
                [
                    'category' => 'Side Dish',
                    'name' => 'Garlic Bread',
                    'description' => 'Roti panggang dengan olesan mentega bawang putih dan parsley.',
                    'price' => 18000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Cheese', 'extra_price' => 6000],
                                ['label' => 'Extra Garlic Butter', 'extra_price' => 4000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Butter', 'extra_price' => 0],
                                ['label' => 'No Garlic', 'extra_price' => 0],
                                ['label' => 'No Parsley', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Side Dish',
                    'name' => 'Mashed Potato',
                    'description' => 'Kentang tumbuk lembut dengan butter dan sedikit susu.',
                    'price' => 20000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Gravy', 'extra_price' => 5000],
                                ['label' => 'Cheese', 'extra_price' => 6000],
                                ['label' => 'Extra Butter', 'extra_price' => 4000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Butter', 'extra_price' => 0],
                                ['label' => 'No Milk', 'extra_price' => 0],
                                ['label' => 'No Pepper', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Side Dish',
                    'name' => 'Crispy Onion Rings',
                    'description' => 'Bawang bombai berlapis tepung renyah dengan saus cocolan.',
                    'price' => 23000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Cheese Dip', 'extra_price' => 6000],
                                ['label' => 'BBQ Dip', 'extra_price' => 5000],
                                ['label' => 'Extra Portion', 'extra_price' => 12000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Seasoning', 'extra_price' => 0],
                                ['label' => 'No Dip Sauce', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],

                // 5. BEVERAGE
                [
                    'category' => 'Beverage',
                    'name' => 'Iced Lemon Tea',
                    'description' => 'Teh dingin dengan perasan lemon segar.',
                    'price' => 18000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Tingkat Gula',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => '0%', 'extra_price' => 0],
                                ['label' => '25%', 'extra_price' => 0],
                                ['label' => '50%', 'extra_price' => 0],
                                ['label' => '75%', 'extra_price' => 0],
                                ['label' => '100%', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Lemon', 'extra_price' => 4000],
                                ['label' => 'Honey', 'extra_price' => 5000],
                                ['label' => 'Mint', 'extra_price' => 3000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Sugar', 'extra_price' => 0],
                                ['label' => 'No Ice', 'extra_price' => 0],
                                ['label' => 'No Lemon', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Beverage',
                    'name' => 'Iced Caramel Latte',
                    'description' => 'Espresso, susu segar, dan sirup karamel.',
                    'price' => 28000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Pilihan Susu',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Full Cream', 'extra_price' => 0],
                                ['label' => 'Oat Milk', 'extra_price' => 8000],
                                ['label' => 'Almond Milk', 'extra_price' => 8000],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Shot', 'extra_price' => 8000],
                                ['label' => 'Caramel Drizzle', 'extra_price' => 5000],
                                ['label' => 'Whipped Cream', 'extra_price' => 6000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Sugar', 'extra_price' => 0],
                                ['label' => 'No Whipped Cream', 'extra_price' => 0],
                                ['label' => 'No Caramel', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Beverage',
                    'name' => 'Strawberry Smoothie',
                    'description' => 'Smoothie stroberi segar dengan susu dan tekstur lembut.',
                    'price' => 26000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Strawberry', 'extra_price' => 7000],
                                ['label' => 'Yogurt', 'extra_price' => 6000],
                                ['label' => 'Whipped Cream', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Sugar', 'extra_price' => 0],
                                ['label' => 'No Milk', 'extra_price' => 0],
                                ['label' => 'No Whipped Cream', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Beverage',
                    'name' => 'Iced Chocolate',
                    'description' => 'Minuman cokelat dengan susu segar dan cokelat premium.',
                    'price' => 25000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Chocolate', 'extra_price' => 6000],
                                ['label' => 'Chocolate Chips', 'extra_price' => 5000],
                                ['label' => 'Marshmallow', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Less Sugar', 'extra_price' => 0],
                                ['label' => 'No Ice', 'extra_price' => 0],
                                ['label' => 'No Whipped Cream', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Beverage',
                    'name' => 'Fresh Orange Juice',
                    'description' => 'Jus jeruk segar dengan rasa manis dan asam alami.',
                    'price' => 22000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Orange', 'extra_price' => 7000],
                                ['label' => 'Honey', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Sugar', 'extra_price' => 0],
                                ['label' => 'No Ice', 'extra_price' => 0],
                                ['label' => 'Less Pulp', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],

                // 6. DESSERT
                [
                    'category' => 'Dessert',
                    'name' => 'Chocolate Lava Cake',
                    'description' => 'Kue cokelat hangat dengan bagian tengah cokelat leleh, disajikan dengan es krim vanilla.',
                    'price' => 32000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Ice Cream', 'extra_price' => 10000],
                                ['label' => 'Chocolate Sauce', 'extra_price' => 5000],
                                ['label' => 'Almond', 'extra_price' => 6000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Ice Cream', 'extra_price' => 0],
                                ['label' => 'No Chocolate Sauce', 'extra_price' => 0],
                                ['label' => 'No Almond', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Dessert',
                    'name' => 'New York Cheesecake',
                    'description' => 'Cheesecake lembut dengan base biskuit renyah dan saus stroberi.',
                    'price' => 30000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Strawberry Sauce', 'extra_price' => 5000],
                                ['label' => 'Blueberry', 'extra_price' => 6000],
                                ['label' => 'Whipped Cream', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Sauce', 'extra_price' => 0],
                                ['label' => 'No Whipped Cream', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Dessert',
                    'name' => 'Belgian Waffle',
                    'description' => 'Waffle hangat dengan tekstur renyah di luar dan lembut di dalam, disajikan bersama es krim.',
                    'price' => 29000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Pilihan Topping',
                            'selection_type' => 'single',
                            'is_required' => true,
                            'values' => [
                                ['label' => 'Chocolate', 'extra_price' => 0],
                                ['label' => 'Strawberry', 'extra_price' => 0],
                                ['label' => 'Caramel', 'extra_price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Waffle', 'extra_price' => 15000],
                                ['label' => 'Banana', 'extra_price' => 6000],
                                ['label' => 'Chocolate Chips', 'extra_price' => 5000],
                                ['label' => 'Ice Cream', 'extra_price' => 10000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Syrup', 'extra_price' => 0],
                                ['label' => 'No Butter', 'extra_price' => 0],
                                ['label' => 'No Ice Cream', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'category' => 'Dessert',
                    'name' => 'Classic Tiramisu',
                    'description' => 'Dessert berlapis ladyfinger, mascarpone cream, dan taburan bubuk kakao.',
                    'price' => 34000,
                    'supports_spicy_level' => false,
                    'options' => [
                        [
                            'name' => 'Add-on',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'Extra Mascarpone', 'extra_price' => 8000],
                                ['label' => 'Cocoa Powder', 'extra_price' => 3000],
                                ['label' => 'Chocolate Shavings', 'extra_price' => 5000],
                            ],
                        ],
                        [
                            'name' => 'Less / Remove',
                            'selection_type' => 'multiple',
                            'is_required' => false,
                            'values' => [
                                ['label' => 'No Cocoa', 'extra_price' => 0],
                                ['label' => 'Less Cream', 'extra_price' => 0],
                            ],
                        ],
                    ],
                ],
            ];

            // 3. Proses Setiap Menu
            foreach ($catalog as $item) {
                $category = $categoryModels[$item['category']];

                $menu = Menu::where('category_id', $category->id)
                    ->where('name', $item['name'])
                    ->first();

                if ($menu) {
                    $menu->update([
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'supports_spicy_level' => $item['supports_spicy_level'],
                    ]);
                } else {
                    $menu = Menu::create([
                        'category_id' => $category->id,
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'supports_spicy_level' => $item['supports_spicy_level'],
                        'image_url' => null,
                        'is_active' => true,
                        'is_available' => true,
                    ]);
                }

                // Hapus grup opsi lama lalu buat ulang dari data
                $menu->optionGroups()->delete();

                foreach ($item['options'] as $groupIndex => $groupData) {
                    $group = $menu->optionGroups()->create([
                        'name' => $groupData['name'],
                        'selection_type' => $groupData['selection_type'],
                        'is_required' => $groupData['is_required'],
                        'sort_order' => $groupIndex + 1,
                    ]);

                    foreach ($groupData['values'] as $valueIndex => $valData) {
                        $group->values()->create([
                            'label' => $valData['label'],
                            'extra_price' => $valData['extra_price'],
                            'sort_order' => $valueIndex + 1,
                        ]);
                    }
                }
            }
        });
    }
}
