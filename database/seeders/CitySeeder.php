<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name_en' => 'Tehran', 'name_fa' => 'تهران'],
            ['name_en' => 'Isfahan', 'name_fa' => 'اصفهان'],
            ['name_en' => 'Tabriz', 'name_fa' => 'تبریز'],
            ['name_en' => 'Shiraz', 'name_fa' => 'شیراز'],
            ['name_en' => 'Mashhad', 'name_fa' => 'مشهد'],
            ['name_en' => 'Qom', 'name_fa' => 'قم'],
            ['name_en' => 'Karaj', 'name_fa' => 'کرج'],
            ['name_en' => 'Ahvaz', 'name_fa' => 'اهواز'],
            ['name_en' => 'Rasht', 'name_fa' => 'رشت'],
            ['name_en' => 'Urmia', 'name_fa' => 'ارومیه'],
            ['name_en' => 'Hamadan', 'name_fa' => 'همدان'],
            ['name_en' => 'Kerman', 'name_fa' => 'کرمان'],
            ['name_en' => 'Hamedan', 'name_fa' => 'همدان'],
            ['name_en' => 'Yazd', 'name_fa' => 'یزد'],
            ['name_en' => 'Bandar Abbas', 'name_fa' => 'بندر عباس'],
            ['name_en' => 'Arak', 'name_fa' => 'اراک'],
            ['name_en' => 'Ardabil', 'name_fa' => 'اردبیل'],
            ['name_en' => 'Zanjan', 'name_fa' => 'زنجان'],
            ['name_en' => 'Sari', 'name_fa' => 'ساری'],
            ['name_en' => 'Babol', 'name_fa' => 'بابل'],
            ['name_en' => 'Qazvin', 'name_fa' => 'قزوین'],
            ['name_en' => 'Sanandaj', 'name_fa' => 'سنندج'],
            ['name_en' => 'Ilam', 'name_fa' => 'ایلام'],
            ['name_en' => 'Birjand', 'name_fa' => 'بیرجند'],
            ['name_en' => 'Zahedan', 'name_fa' => 'زاهدان'],
            ['name_en' => 'Herat', 'name_fa' => 'هرات'],
            ['name_en' => 'Bam', 'name_fa' => 'بم'],
            ['name_en' => 'Qeshm', 'name_fa' => 'قشم'],
            ['name_en' => 'Chalus', 'name_fa' => 'چالوس'],
            ['name_en' => 'Gorgan', 'name_fa' => 'گرگان'],
            ['name_en' => 'Bojnurd', 'name_fa' => 'بجنورد'],
            ['name_en' => 'Abadan', 'name_fa' => 'آبادان'],
        ];

        foreach ($cities as $city) {
            City::create([
                'name_en' => $city['name_en'],
                'name_fa' => $city['name_fa'],
                'slug' => \Illuminate\Support\Str::slug($city['name_en']),
            ]);
        }
    }
}
