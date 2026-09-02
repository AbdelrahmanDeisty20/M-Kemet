<?php

namespace Database\Seeders;

use App\Models\Qualification;
use Illuminate\Database\Seeder;

class QualificationSeeder extends Seeder
{
    public function run(): void
    {
        $qualifications = [
            [
                'name_ar'    => 'ثانوية عامة / ما يعادلها',
                'name_en'    => 'High School / Equivalent',
                'code'       => 'high_school',
                'sort_order' => 1,
                'is_active'  => true,
            ],
            [
                'name_ar'    => 'دبلوم فوق متوسط / فني',
                'name_en'    => 'Diploma / Intermediate Degree',
                'code'       => 'diploma',
                'sort_order' => 2,
                'is_active'  => true,
            ],
            [
                'name_ar'    => 'بكالوريوس / ليسانس',
                'name_en'    => 'Bachelor Degree',
                'code'       => 'bachelor',
                'sort_order' => 3,
                'is_active'  => true,
            ],
            [
                'name_ar'    => 'دبلوم دراسات عليا',
                'name_en'    => 'Postgraduate Diploma',
                'code'       => 'postgraduate_diploma',
                'sort_order' => 4,
                'is_active'  => true,
            ],
            [
                'name_ar'    => 'درجة الماجستير',
                'name_en'    => 'Master Degree',
                'code'       => 'master',
                'sort_order' => 5,
                'is_active'  => true,
            ],
            [
                'name_ar'    => 'درجة الدكتوراه (PhD)',
                'name_en'    => 'Doctorate / PhD',
                'code'       => 'doctorate',
                'sort_order' => 6,
                'is_active'  => true,
            ],
            [
                'name_ar'    => 'شهادة مهنية متخصصة',
                'name_en'    => 'Professional Certification',
                'code'       => 'professional_cert',
                'sort_order' => 7,
                'is_active'  => true,
            ],
        ];

        foreach ($qualifications as $qualification) {
            Qualification::updateOrCreate(
                ['code' => $qualification['code']],
                $qualification
            );
        }

        $this->command->info('Qualifications Seeded Successfully!');
    }
}
