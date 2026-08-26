<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds for all Arab countries.
     */
    public function run(): void
    {
        $countries = [
            ['name_ar' => 'مصر', 'name_en' => 'Egypt', 'code' => 'EG', 'is_active' => true],
            ['name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia', 'code' => 'SA', 'is_active' => true],
            ['name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates', 'code' => 'AE', 'is_active' => true],
            ['name_ar' => 'الكويت', 'name_en' => 'Kuwait', 'code' => 'KW', 'is_active' => true],
            ['name_ar' => 'قطر', 'name_en' => 'Qatar', 'code' => 'QA', 'is_active' => true],
            ['name_ar' => 'البحرين', 'name_en' => 'Bahrain', 'code' => 'BH', 'is_active' => true],
            ['name_ar' => 'سلطنة عمان', 'name_en' => 'Oman', 'code' => 'OM', 'is_active' => true],
            ['name_ar' => 'الأردن', 'name_en' => 'Jordan', 'code' => 'JO', 'is_active' => true],
            ['name_ar' => 'العراق', 'name_en' => 'Iraq', 'code' => 'IQ', 'is_active' => true],
            ['name_ar' => 'لبنان', 'name_en' => 'Lebanon', 'code' => 'LB', 'is_active' => true],
            ['name_ar' => 'فلسطين', 'name_en' => 'Palestine', 'code' => 'PS', 'is_active' => true],
            ['name_ar' => 'سوريا', 'name_en' => 'Syria', 'code' => 'SY', 'is_active' => true],
            ['name_ar' => 'اليمن', 'name_en' => 'Yemen', 'code' => 'YE', 'is_active' => true],
            ['name_ar' => 'السودان', 'name_en' => 'Sudan', 'code' => 'SD', 'is_active' => true],
            ['name_ar' => 'ليبيا', 'name_en' => 'Libya', 'code' => 'LY', 'is_active' => true],
            ['name_ar' => 'تونس', 'name_en' => 'Tunisia', 'code' => 'TN', 'is_active' => true],
            ['name_ar' => 'الجزائر', 'name_en' => 'Algeria', 'code' => 'DZ', 'is_active' => true],
            ['name_ar' => 'المغرب', 'name_en' => 'Morocco', 'code' => 'MA', 'is_active' => true],
            ['name_ar' => 'موريتانيا', 'name_en' => 'Mauritania', 'code' => 'MR', 'is_active' => true],
            ['name_ar' => 'الصومال', 'name_en' => 'Somalia', 'code' => 'SO', 'is_active' => true],
            ['name_ar' => 'جيبوتي', 'name_en' => 'Djibouti', 'code' => 'DJ', 'is_active' => true],
            ['name_ar' => 'جزر القمر', 'name_en' => 'Comoros', 'code' => 'KM', 'is_active' => true],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}
