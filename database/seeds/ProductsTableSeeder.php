<?php

use Illuminate\Database\Seeder;
use App\Product;

class ProductsTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Product::create([
            'name' => 'Bánh dày giò',
            'price' => '10000',
            'category' => 1,
            'description' => 'banh day thom ngon',
            'img' => 'img1.jpg'
        ]);
        Product::create([
            'name' => 'Bánh Mỳ chả',
            'price' => '12000',
            'category' => 1,
            'description' => 'banh mỳ thom ngon',
            'img' => 'img2.jpg'
        ]);
        
    }

}
