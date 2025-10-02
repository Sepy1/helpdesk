<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $c1 = Category::create(['name'=>'Core Banking System', 'slug'=>'CBS', 'description'=>'Masalah Core Banking System']);
        Subcategory::create(['category_id'=>$c1->id, 'name'=>'Pergantian User', 'slug'=>'pergantian user']);
        Subcategory::create(['category_id'=>$c1->id, 'name'=>'Koreksi Master', 'slug'=>'koreksi master']);
        Subcategory::create(['category_id'=>$c1->id, 'name'=>'Koreksi Input', 'slug'=>'koreksi input']);
        Subcategory::create(['category_id'=>$c1->id, 'name'=>'Manajemen User dan Menu', 'slug'=>'manajemen user']);

        $c2 = Category::create(['name'=>'Layanan Digital', 'slug'=>'Layanan Digital', 'description'=>'Masalah Layanan Digital']);
        Subcategory::create(['category_id'=>$c2->id, 'name'=>'VA', 'slug'=>'Virtual Account']);
        Subcategory::create(['category_id'=>$c2->id, 'name'=>'QRIS', 'slug'=>'Merchant QRIS']);
        Subcategory::create(['category_id'=>$c2->id, 'name'=>'Branchless', 'slug'=>'Branchless']);

        $c3 = Category::create(['name'=>'Network', 'slug'=>'Jaringan & Server', 'description'=>'Masalah Jaringan dan Server']);
        Subcategory::create(['category_id'=>$c3->id, 'name'=>'Koneksi CBS', 'slug'=>'koneksi core banking system']);
        Subcategory::create(['category_id'=>$c3->id, 'name'=>'Koneksi Internet', 'slug'=>'koneksi internet']);
        Subcategory::create(['category_id'=>$c3->id, 'name'=>'Server', 'slug'=>'server report dan webtransaksi']);

        $c4 = Category::create(['name'=>'Hardware / Software', 'slug'=>'hardware', 'description'=>'kerusakan perangkat keras']);
        Subcategory::create(['category_id'=>$c4->id, 'name'=>'PC/Laptop', 'slug'=>'pc-laptop']);
        Subcategory::create(['category_id'=>$c4->id, 'name'=>'Printer', 'slug'=>'printer']);
         Subcategory::create(['category_id'=>$c4->id, 'name'=>'Perangkat Lainnya', 'slug'=>'hardware lainnya']);
          Subcategory::create(['category_id'=>$c4->id, 'name'=>'Software OS', 'slug'=>'software os']);
           Subcategory::create(['category_id'=>$c4->id, 'name'=>'Aplikasi OS', 'slug'=>'aplikasi os']);
            Subcategory::create(['category_id'=>$c4->id, 'name'=>'Aplikasi Lainnya', 'slug'=>'aplikasi lainnya']);

        $c5 = Category::create(['name'=>'Lainnya', 'slug'=>'Lainnya', 'description'=>'lainnya']);
        Subcategory::create(['category_id'=>$c5->id, 'name'=>'Permintaan Data', 'slug'=>'permintaan data']);
        Subcategory::create(['category_id'=>$c5->id, 'name'=>'Lainnya', 'slug'=>'lainnya']);
    }
}
