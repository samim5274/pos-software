<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some random categories, subcategories, brands, and a vendor
        $categories = ProductCategory::all();
        $subcategories = ProductSubCategory::all();
        $brands = Brand::all();

        if(!$categories->count() || !$subcategories->count() || !$brands->count()){
            $this->command->warn('Make sure categories, subcategories, brands and vendors exist.');
            return;
        }

        for ($i = 1; $i <= 500; $i++) {
            $category = $categories->random();
            $subcategory = $subcategories->where('category_id', $category->id)->random();
            $brand = $brands->random();

            $name = "Sample Product {$i}";
            $slug = Str::slug($name);

            $purchase_price = rand(100, 2000);
            $price = rand(100, 2000);

            // 10% - 50%
            $discount = rand(0, (int)($price * 0.5));

            $discountPrice = $price - $discount;

            $product = Product::create([
                'name'             => $name,
                'slug'             => $slug,
                'sku'              => 'SKU-' . Str::upper(Str::random(6)),
                'category_id'      => $category->id,
                'subcategory_id'   => $subcategory->id,
                'brand_id'         => $brand->id,
                'purchase_price'   => $purchase_price,
                'price'            => $price,
                'discount'         => $discountPrice,
                'stock_quantity'   => rand(5, 50),
                'min_stock'        => 5,
                'is_active'        => 1,
            ]);

        }

        $this->command->info("500 sample products inserted successfully!");

        // php artisan db:seed --class=ProductSeeder
    }
}
