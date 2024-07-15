<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductLang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'category_id' => 1,
                'activated' => 1,
                'status_id' => 1,
                'quantity' => 10,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'langs' => [
                    [
                        'product_id' => 1,
                        'lang_id' => 1,
                        'name' => 'Codasul pH',
                        'url' => 'Codasul pH'
                    ],
                    [
                        'product_id' => 1,
                        'lang_id' => 2,
                        'name' => 'محصول ۱',
                        'url' => 'محصول ۱'
                    ]
                ],
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'activated' => 1,
                'status_id' => 1,
                'quantity' => 10,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'langs' => [
                    [
                        'product_id' => 2,
                        'lang_id' => 1,
                        'name' => 'Coda Hort Plus',
                        'url' => 'Coda Hort Plus'
                    ],
                    [
                        'product_id' => 2,
                        'lang_id' => 2,
                        'name' => 'محصول ۲',
                        'url' => 'محصول ۲'
                    ]
                ],
            ],
            [
                'id' => 3,
                'category_id' => 2,
                'activated' => 1,
                'status_id' => 1,
                'quantity' => 10,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'langs' => [
                    [
                        'product_id' => 3,
                        'lang_id' => 1,
                        'name' => 'Codasal Premium',
                        'url' => 'Codasal Premium'
                    ],
                    [
                        'product_id' => 3,
                        'lang_id' => 2,
                        'name' => 'محصول ۳',
                        'url' => 'محصول ۳'
                    ]
                ],
            ],
        ];

        foreach ($data as $product)
        {
            $product2 = $product ?? [];
            unset($product['langs']);
            Product::create($product);
            foreach ($product2['langs'] as $product_lang) {
                ProductLang::create($product_lang);
            }
        }

    }
}
