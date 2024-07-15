<?php

namespace Database\Seeders;

use App\Models\CategoriesLang;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('products_lang')->delete(); 
        // DB::table('products')->delete(); 
        // DB::table('categories_lang')->delete(); 
        // DB::table('categories')->delete(); 
        
        $data = [
            [
                'id' => 1,
                'parent_id' => 0,
                'name' => 'cat1',
                'url' => 'cat1',
                'level' => '1',
                'homepage_position' => 'MegaMenu1',
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'langs' => [
                    [
                        'category_id' => 1,
                        'lang_id' => 1,
                        'home_title' => 'cat1',
                        'title' => 'cat1',
                        'url' => 'cat1'
                    ],
                    [
                        'category_id' => 1,
                        'lang_id' => 2,
                        'home_title' => 'دسته ۱',
                        'title' => 'دسته ۱',
                        'url' => 'دسته ۱'
                    ]
                ],
            ],
            [
                'id' => 2,
                'parent_id' => 0,
                'name' => 'cat2',
                'url' => 'cat2',
                'home_show' => 1,
                'level' => '1',
                'homepage_position' => 'MegaMenu2',
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'langs' => [
                    [
                        'category_id' => 2,
                        'lang_id' => 1,
                        'home_title' => 'cat2',
                        'title' => 'cat2',
                        'url' => 'cat2'
                    ],
                    [
                        'category_id' => 2,
                        'lang_id' => 2,
                        'home_title' => 'دسته ۲',
                        'title' => 'دسته ۲',
                        'url' => 'دسته ۲'
                    ]
                ],
            ],
        ];

        foreach ($data as $category)
        {
            $category2 = $category;
            unset($category['langs']);
            Category::create($category);
            foreach ($category2['langs'] as $category_lang) {
                CategoriesLang::create($category_lang);
            }
        }
    }
}
