<?php

namespace Database\Seeders;

use App\Models\Term;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = [
            [
                'slug'       => 'terms-acceptance',
                'title_ar'   => 'القبول بالشروط والأحكام',
                'title_en'   => 'Acceptance of Terms & Conditions',
                'desc_ar'    => 'استخدامك لمنصة M-Kemet (أم كميت) المخصصة لتشغيل وتوظيف الكوادر والعمالة المصرية بالخارج ودول الخليج العربي يعني موافقتك الكاملة على هذه الشروط والأحكام. في حال عدم موافقتك على أي شرط، يُرجى التوقف عن استخدام المنصة.',
                'desc_en'    => 'By accessing and using the M-Kemet platform for connecting and employing Egyptian talent across GCC and international markets, you agree to be fully bound by these Terms and Conditions. If you do not agree, please refrain from using the platform.',
                'is_active'  => true,
                'sort_order' => 1,
            ],
            [
                'slug'       => 'account-registration',
                'title_ar'   => 'إنشاء الحساب ودقة البيانات المعروضة',
                'title_en'   => 'Account Registration & Data Accuracy',
                'desc_ar'    => 'يلتزم الباحثون عن العمل والشركات المشتركة بتقديم بيانات صحيحة ودقيقة، بما في ذلك المؤهلات التعليمية، سنوات الخبرة، المهن، المستندات الرسمية، والفحوصات المستندية. يتحمل المستخدم كافة التبعات القانونية عن أي بيانات مضللة.',
                'desc_en'    => 'Both job seekers and registering companies undertake to provide complete, accurate, and authentic data including qualifications, experience years, professions, and official documentation. Users hold full legal responsibility for false declarations.',
                'is_active'  => true,
                'sort_order' => 2,
            ],
            [
                'slug'       => 'employer-services',
                'title_ar'   => 'خدمات الشركات واستعراض مرشحي M-Kemet',
                'title_en'   => 'Employer Services & Candidate Discovery',
                'desc_ar'    => 'تمنح منصة M-Kemet الشركات المعتمدة صلاحية استعراض ملفات الباحثين عن العمل، ومعاينة مقاطع الفيديو التعريفية والمستندات، وإرسال طلبات التواصل وفق أحدث النظم والمعايير الخاصة بالتوظيف الخارجي.',
                'desc_en'    => 'M-Kemet grants verified employers authorization to browse candidate profiles, inspect video introductions, review documentations, and submit contact requests in adherence to cross-border recruitment rules.',
                'is_active'  => true,
                'sort_order' => 3,
            ],
            [
                'slug'       => 'candidate-obligations',
                'title_ar'   => 'التزامات المرشح والباحث عن عمل',
                'title_en'   => 'Candidate Obligations & Profile Integrity',
                'desc_ar'    => 'يتعهد الباحث عن العمل بالحفاظ على تحديث ملفه الشخصي، وإرفاق المستندات المطلوبة (جواز السفر، الشهادات)، ورفع فيديو تعريفي احترافي يعكس مهاراته، والتجاوب المهني مع طلبات التواصل الصادرة من أصحاب العمل.',
                'desc_en'    => 'Job seekers pledge to maintain updated profiles, attach valid required credentials (passports, certificates), upload professional video intros, and respond promptly to verified employer contact inquiries.',
                'is_active'  => true,
                'sort_order' => 4,
            ],
            [
                'slug'       => 'privacy-security',
                'title_ar'   => 'الخصوصية وحماية البيانات الشخصية',
                'title_en'   => 'Privacy & Data Protection',
                'desc_ar'    => 'تضمن منصة M-Kemet التشفير التام لبيانات الباحثين عن العمل والشركات. لا يتم إظهار بيانات التواصل المباشرة إلا للشركات التي تقدم طلب تواصل رسمي ومقبول وفق سياسة المنصة.',
                'desc_en'    => 'M-Kemet ensures full encryption and protection of user data. Direct contact details are restricted and shared only with verified employers who submit valid, authorized contact requests.',
                'is_active'  => true,
                'sort_order' => 5,
            ],
            [
                'slug'       => 'intellectual-property',
                'title_ar'   => 'الملكية الفكرية وحقوق الاستخدام',
                'title_en'   => 'Intellectual Property Rights',
                'desc_ar'    => 'جميع العلامات التجارية، والشعارات، والأنظمة البرمجية الخاصة بمنصة M-Kemet هي ملكية فكرية محفوطة. يُحظر كشط البيانات تلقائياً أو إعادة إغلاق واستخدام المحتوى دون إذن كتابي مسبق.',
                'desc_en'    => 'All brand trademarks, logos, system code, and database records of M-Kemet are proprietary assets. Automated scraping or unauthorized republication of system content is strictly prohibited.',
                'is_active'  => true,
                'sort_order' => 6,
            ],
            [
                'slug'       => 'modifications-and-updates',
                'title_ar'   => 'تحديث وتعديل الشروط والأحكام',
                'title_en'   => 'Modifications & Terms Updates',
                'desc_ar'    => 'تحتفظ إدارة منصة M-Kemet بحق تعديل أو تحديث بنود هذه الاتفاقية في أي وقت لملائمة قوانين العمل والتطويرات التقنية. يتم إخطار المستخدمين بأي تحديثات جوهرية عبر البريد الإلكتروني أو إشعارات المنصة.',
                'desc_en'    => 'M-Kemet management reserves the right to amend or update these terms at any time to align with recruitment legislation and technology updates. Users will be notified of material changes via email or platform notifications.',
                'is_active'  => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($terms as $termData) {
            Term::updateOrCreate(
                ['slug' => $termData['slug']],
                $termData
            );
        }
    }
}
