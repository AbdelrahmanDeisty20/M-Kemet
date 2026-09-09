<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Document;
use App\Models\Gender;
use App\Models\Profession;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $countries   = Country::all()->keyBy('code');
        $professions = Profession::all()->keyBy('id');
        $male        = Gender::where('code', 'male')->first();
        $female      = Gender::where('code', 'female')->first();

        $egypt = $countries->get('EG');
        $saudi = $countries->get('SA');
        $uae   = $countries->get('AE');

        // -------------------------------------------------------------
        // Candidate 1: Fixed Test Candidate (Ready for tests & tokens)
        // -------------------------------------------------------------
        $user1 = User::updateOrCreate(
            ['email' => 'candidate@example.com'],
            [
                'name'       => 'أحمد محمود العبد',
                'phone'      => '+201000000001',
                'country_id' => $egypt?->id,
                'user_type'  => 'candidate',
                'status'     => 'active',
                'password'   => Hash::make('password'),
            ]
        );

        $user1->tokens()->delete();
        $tokenResult1 = $user1->createToken('CandidateTestToken');

        $profile1 = UserProfile::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'birth_date'         => '1995-06-15',
                'gender_id'          => $male?->id,
                'current_country_id' => $egypt?->id,
                'qualification_id'   => 3,
                'qualification'      => 'بكالوريوس هندسة الحاسبات والمعلومات',
                'sub_specialization' => 'تطوير البرمجيات وتطبيقات الويب',
                'profession_id'      => 1,
                'experience_years'   => 5,
                'experience_level_id'=> 2,
                'expected_salary'    => 3500.00,
                'willing_to_travel'  => true,
                'languages'          => ['العربية', 'الإنجليزية'],
                'skills'             => ['PHP', 'Laravel', 'REST API', 'MySQL', 'Vue.js', 'Git'],
                'summary'            => 'مهندس برمجيات خبرة 5 سنوات في بناء وتطوير الأنظمة السحابية والشبكات، أبحث عن فرصة عمل ممتازة.',
                'status'             => 'approved',
            ]
        );

        if ($profile1) {
            $profile1->professions()->sync([1, 5]);
            $profile1->targetCountries()->sync(array_filter([$egypt?->id, $saudi?->id, $uae?->id]));
        }

        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'personal_photo'],
            ['file_path' => 'documents/personal_photos/candidate_1.jpg', 'disk' => 'public', 'is_approved' => true]
        );
        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'cv'],
            ['file_path' => 'documents/cvs/candidate_1_cv.pdf', 'disk' => 'private', 'is_approved' => true]
        );
        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'national_id'],
            ['file_path' => 'documents/national_ids/candidate_1_id.jpg', 'disk' => 'private', 'is_approved' => true]
        );
        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'passport'],
            ['file_path' => 'documents/passports/candidate_1_passport.jpg', 'disk' => 'private', 'is_approved' => true]
        );

        Video::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'video_path'       => 'videos/candidate_1_intro.mp4',
                'thumbnail_path'   => 'videos/thumbnails/candidate_1_thumb.jpg',
                'duration_seconds' => 60,
                'file_size_mb'     => 15.50,
                'status'           => 'approved',
            ]
        );

        // -------------------------------------------------------------
        // Seed 50 Realistic & Diverse Job Seekers
        // -------------------------------------------------------------
        $candidatesData = [
            // Male Candidates
            ['name' => 'محمد أحمد علي', 'gender_id' => $male?->id, 'country_code' => 'EG', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس حاسبات والمعلومات', 'sub_spec' => 'تطوير Back-end', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 3000, 'passport' => 'approved'],
            ['name' => 'محمود حسن سعيد', 'gender_id' => $male?->id, 'country_code' => 'SA', 'prof_id' => 5, 'qual_id' => 3, 'qual' => 'بكالوريوس هندسة مدنية', 'sub_spec' => 'إدارة مشاريع البناء', 'exp' => 7, 'exp_lvl' => 3, 'salary' => 6000, 'passport' => 'approved'],
            ['name' => 'إبراهيم خليل عمر', 'gender_id' => $male?->id, 'country_code' => 'JO', 'prof_id' => 2, 'qual_id' => 4, 'qual' => 'ماجستير الطب والجراحة العامة', 'sub_spec' => 'طب عام وأسرة', 'exp' => 9, 'exp_lvl' => 3, 'salary' => 7500, 'passport' => 'approved'],
            ['name' => 'طارق عبد الله الشريف', 'gender_id' => $male?->id, 'country_code' => 'KW', 'prof_id' => 3, 'qual_id' => 3, 'qual' => 'بكالوريوس تجارة ومحاسبة', 'sub_spec' => 'مراجعة وتدقيق مالي', 'exp' => 6, 'exp_lvl' => 2, 'salary' => 4500, 'passport' => 'pending'],
            ['name' => 'يوسف مصطفى الهاشمي', 'gender_id' => $male?->id, 'country_code' => 'AE', 'prof_id' => 4, 'qual_id' => 3, 'qual' => 'بكالوريوس تسويق وإعلام', 'sub_spec' => 'إعلانات وحملات رقمية', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 3500, 'passport' => 'none'],
            ['name' => 'عمر خالد الفارس', 'gender_id' => $male?->id, 'country_code' => 'QA', 'prof_id' => 7, 'qual_id' => 3, 'qual' => 'بكالوريوس إدارة أعمال', 'sub_spec' => 'مبيعات الشركات B2B', 'exp' => 8, 'exp_lvl' => 4, 'salary' => 7000, 'passport' => 'approved'],
            ['name' => 'علي الكيلاني', 'gender_id' => $male?->id, 'country_code' => 'SY', 'prof_id' => 8, 'qual_id' => 3, 'qual' => 'بكالوريوس الفنون الجميلة والتصميم', 'sub_spec' => 'تصميم واجهات المستخدم UI/UX', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 2800, 'passport' => 'pending'],
            ['name' => 'كريم عادل النجار', 'gender_id' => $male?->id, 'country_code' => 'EG', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس علوم الحاسب', 'sub_spec' => 'تطوير تطبيقات الموبايل Flutter', 'exp' => 2, 'exp_lvl' => 1, 'salary' => 2000, 'passport' => 'approved'],
            ['name' => 'عبد الرحمن زكي', 'gender_id' => $male?->id, 'country_code' => 'SD', 'prof_id' => 6, 'qual_id' => 3, 'qual' => 'بكالوريوس تمريض شرف', 'sub_spec' => 'رعاية مركزة وطوارئ', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 2500, 'passport' => 'none'],
            ['name' => 'حمزة سامي المطيري', 'gender_id' => $male?->id, 'country_code' => 'SA', 'prof_id' => 3, 'qual_id' => 3, 'qual' => 'بكالوريوس محاسبة وتمويل', 'sub_spec' => 'محاسبة تكاليف', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 4000, 'passport' => 'approved'],
            ['name' => 'زياد حسام القحطاني', 'gender_id' => $male?->id, 'country_code' => 'SA', 'prof_id' => 5, 'qual_id' => 3, 'qual' => 'بكالوريوس هندسة تشييد', 'sub_spec' => 'حساب كميات ومواصفات', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 5500, 'passport' => 'pending'],
            ['name' => 'خالد السعيد', 'gender_id' => $male?->id, 'country_code' => 'MA', 'prof_id' => 1, 'qual_id' => 4, 'qual' => 'ماجستير علوم البيانات والذكاء الاصطناعي', 'sub_spec' => 'تعلم الآلة Data Science', 'exp' => 6, 'exp_lvl' => 3, 'salary' => 5000, 'passport' => 'approved'],
            ['name' => 'مصطفى كمال الدين', 'gender_id' => $male?->id, 'country_code' => 'IQ', 'prof_id' => 2, 'qual_id' => 3, 'qual' => 'بكالوريوس طب وجراحة', 'sub_spec' => 'ممارس عام', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 4200, 'passport' => 'none'],
            ['name' => 'حسين فهد الشمري', 'gender_id' => $male?->id, 'country_code' => 'OM', 'prof_id' => 7, 'qual_id' => 2, 'qual' => 'دبلوم عالي إدارة وتجارة', 'sub_spec' => 'إدارة المبيعات والتوزيع', 'exp' => 10, 'exp_lvl' => 4, 'salary' => 6500, 'passport' => 'approved'],
            ['name' => 'حسام الدين السيد', 'gender_id' => $male?->id, 'country_code' => 'EG', 'prof_id' => 5, 'qual_id' => 3, 'qual' => 'بكالوريوس هندسة معمارية', 'sub_spec' => 'تصميم معماري وتخطيط', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 2700, 'passport' => 'pending'],
            ['name' => 'هاني رفيق الزهراني', 'gender_id' => $male?->id, 'country_code' => 'SA', 'prof_id' => 4, 'qual_id' => 3, 'qual' => 'بكالوريوس إعلام وتسويق رقمي', 'sub_spec' => 'صناعة محتوى وإدارة منصات', 'exp' => 2, 'exp_lvl' => 1, 'salary' => 3000, 'passport' => 'approved'],
            ['name' => 'رمزي توفيق الخولي', 'gender_id' => $male?->id, 'country_code' => 'LB', 'prof_id' => 8, 'qual_id' => 3, 'qual' => 'بكالوريوس تصميم جرافيك', 'sub_spec' => 'الهوية البصرية والهوية التجارية', 'exp' => 7, 'exp_lvl' => 3, 'salary' => 3800, 'passport' => 'none'],
            ['name' => 'فادي ممدوح بركات', 'gender_id' => $male?->id, 'country_code' => 'JO', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس هندسة البرمجيات', 'sub_spec' => 'Full-Stack Developer', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 4000, 'passport' => 'approved'],
            ['name' => 'ماجد سليمان الحبيشي', 'gender_id' => $male?->id, 'country_code' => 'YE', 'prof_id' => 3, 'qual_id' => 3, 'qual' => 'بكالوريوس علوم مالية ومصرفية', 'sub_spec' => 'تحليل مالي وميزانيات', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 2600, 'passport' => 'pending'],
            ['name' => 'وائل عبد الفتاح', 'gender_id' => $male?->id, 'country_code' => 'DZ', 'prof_id' => 5, 'qual_id' => 4, 'qual' => 'ماجستير الهندسة الإنشائية', 'sub_spec' => 'تصميم المنشآت الخرسانية', 'exp' => 11, 'exp_lvl' => 4, 'salary' => 6200, 'passport' => 'approved'],
            ['name' => 'باسل عصام غانم', 'gender_id' => $male?->id, 'country_code' => 'TN', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس تكنولوجيا المعلومات', 'sub_spec' => 'أمن المعلومات والشبكات Cybersecurity', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 3200, 'passport' => 'approved'],
            ['name' => 'زياد منصور الشامي', 'gender_id' => $male?->id, 'country_code' => 'SY', 'prof_id' => 6, 'qual_id' => 2, 'qual' => 'دبلوم تمريض عالي', 'sub_spec' => 'تمريض جراحي', 'exp' => 6, 'exp_lvl' => 2, 'salary' => 2200, 'passport' => 'none'],
            ['name' => 'نادر فاروق الشيخ', 'gender_id' => $male?->id, 'country_code' => 'EG', 'prof_id' => 7, 'qual_id' => 3, 'qual' => 'بكالوريوس تجارة خارجية', 'sub_spec' => 'تطوير أعمال ومبيعات ميدانية', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 2900, 'passport' => 'approved'],
            ['name' => 'عصام فتحي البدر', 'gender_id' => $male?->id, 'country_code' => 'KW', 'prof_id' => 2, 'qual_id' => 4, 'qual' => 'دكتوراه في طب الباطنة العامة', 'sub_spec' => 'استشاري باطنة', 'exp' => 14, 'exp_lvl' => 4, 'salary' => 9500, 'passport' => 'approved'],
            ['name' => 'شريف أنور رضوان', 'gender_id' => $male?->id, 'country_code' => 'EG', 'prof_id' => 4, 'qual_id' => 3, 'qual' => 'بكالوريوس تسويق إلكتروني', 'sub_spec' => 'تحسين محركات البحث SEO', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 2400, 'passport' => 'pending'],

            // Female Candidates
            ['name' => 'سارة علي حسن', 'gender_id' => $female?->id, 'country_code' => 'EG', 'prof_id' => 3, 'qual_id' => 3, 'qual' => 'بكالوريوس تجارة وإدارة أعمال', 'sub_spec' => 'محاسبة مالية', 'exp' => 2, 'exp_lvl' => 2, 'salary' => 2000, 'passport' => 'pending'],
            ['name' => 'مريم إبراهيم السيد', 'gender_id' => $female?->id, 'country_code' => 'SA', 'prof_id' => 2, 'qual_id' => 3, 'qual' => 'بكالوريوس طب وطب أسنان', 'sub_spec' => 'طبيبة أسنان عامة', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 6000, 'passport' => 'approved'],
            ['name' => 'نورهان محمد الشربيني', 'gender_id' => $female?->id, 'country_code' => 'EG', 'prof_id' => 8, 'qual_id' => 3, 'qual' => 'بكالوريوس فنيات وتصميم', 'sub_spec' => 'Graphic & Motion Graphics', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 2500, 'passport' => 'approved'],
            ['name' => 'فاطمة الزهراء العتيبي', 'gender_id' => $female?->id, 'country_code' => 'SA', 'prof_id' => 4, 'qual_id' => 3, 'qual' => 'بكالوريوس إعلام وتواصل جماهيري', 'sub_spec' => 'إدارة شبكات التواصل الاجتماعي', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 4500, 'passport' => 'approved'],
            ['name' => 'ياسمين عبد الرحمن', 'gender_id' => $female?->id, 'country_code' => 'JO', 'prof_id' => 6, 'qual_id' => 3, 'qual' => 'بكالوريوس تمريض', 'sub_spec' => 'تمريض أطفال ورعاية حديثي الولادة', 'exp' => 7, 'exp_lvl' => 3, 'salary' => 3200, 'passport' => 'none'],
            ['name' => 'هبة الله محمود', 'gender_id' => $female?->id, 'country_code' => 'EG', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس هندسة الحاسبات', 'sub_spec' => 'تطوير تطبيقات iOS Swift', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 3400, 'passport' => 'approved'],
            ['name' => 'رانيا عادل الفايز', 'gender_id' => $female?->id, 'country_code' => 'AE', 'prof_id' => 7, 'qual_id' => 3, 'qual' => 'بكالوريوس تسويق دولي', 'sub_spec' => 'إدارة حسابات كبار العملاء Key Accounts', 'exp' => 6, 'exp_lvl' => 3, 'salary' => 6500, 'passport' => 'approved'],
            ['name' => 'آية مصطفى عثمان', 'gender_id' => $female?->id, 'country_code' => 'SD', 'prof_id' => 2, 'qual_id' => 3, 'qual' => 'بكالوريوس العلوم الصيدلانية', 'sub_spec' => 'صيدلية سريرية ومستشفيات', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 2800, 'passport' => 'pending'],
            ['name' => 'شروق حاتم التميمي', 'gender_id' => $female?->id, 'country_code' => 'IQ', 'prof_id' => 5, 'qual_id' => 3, 'qual' => 'بكالوريوس هندسة ميكانيكية', 'sub_spec' => 'أنظمة التكييف والتبريد HVAC', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 3800, 'passport' => 'none'],
            ['name' => 'ريم طارق البلوشي', 'gender_id' => $female?->id, 'country_code' => 'OM', 'prof_id' => 3, 'qual_id' => 3, 'qual' => 'بكالوريوس مالية ومحاسبة', 'sub_spec' => 'تخطيط مالي وموازنات', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 3500, 'passport' => 'approved'],
            ['name' => 'داليا فاروق الحسيني', 'gender_id' => $female?->id, 'country_code' => 'EG', 'prof_id' => 4, 'qual_id' => 4, 'qual' => 'ماجستير التسويق والتجارة الإلكترونية', 'sub_spec' => 'تخطيط استراتيجي للتسويق', 'exp' => 8, 'exp_lvl' => 3, 'salary' => 4200, 'passport' => 'approved'],
            ['name' => 'أميرة مجدي الشمري', 'gender_id' => $female?->id, 'country_code' => 'KW', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس علوم الحاسب', 'sub_spec' => 'اختبار برمجيات SQA & Testing', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 3600, 'passport' => 'pending'],
            ['name' => 'لمياء زكريا سالم', 'gender_id' => $female?->id, 'country_code' => 'MA', 'prof_id' => 8, 'qual_id' => 3, 'qual' => 'بكالوريوس الفنون الرقمية', 'sub_spec' => 'تصميم الواجهات UI/UX والتفاعل', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 3100, 'passport' => 'approved'],
            ['name' => 'دينا الشربيني', 'gender_id' => $female?->id, 'country_code' => 'EG', 'prof_id' => 6, 'qual_id' => 3, 'qual' => 'بكالوريوس تمريض عام', 'sub_spec' => 'تمريض عمليات وجراحة', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 2300, 'passport' => 'approved'],
            ['name' => 'ندى أحمد الزهراني', 'gender_id' => $female?->id, 'country_code' => 'SA', 'prof_id' => 3, 'qual_id' => 3, 'qual' => 'بكالوريوس إدارة مالية', 'sub_spec' => 'إدارة الرواتب والضرائب', 'exp' => 2, 'exp_lvl' => 1, 'salary' => 3800, 'passport' => 'none'],
            ['name' => 'هناء توفيق مراد', 'gender_id' => $female?->id, 'country_code' => 'SY', 'prof_id' => 2, 'qual_id' => 4, 'qual' => 'ماجستير في طب الأطفال', 'sub_spec' => 'أخصائية أطفال', 'exp' => 10, 'exp_lvl' => 4, 'salary' => 7000, 'passport' => 'approved'],
            ['name' => 'إيمان خالد النمر', 'gender_id' => $female?->id, 'country_code' => 'JO', 'prof_id' => 7, 'qual_id' => 3, 'qual' => 'بكالوريوس إدارة أعمال', 'sub_spec' => 'خدمة عملاء ومبيعات داخلية', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 2700, 'passport' => 'pending'],
            ['name' => 'خديجة عبد الله الماجد', 'gender_id' => $female?->id, 'country_code' => 'QA', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس هندسة الحاسوب', 'sub_spec' => 'Cloud Engineer & DevOps', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 5800, 'passport' => 'approved'],
            ['name' => 'نهى عصام العبد الله', 'gender_id' => $female?->id, 'country_code' => 'LB', 'prof_id' => 4, 'qual_id' => 3, 'qual' => 'بكالوريوس العلاقات العامة', 'sub_spec' => 'إدارة الأزمات والتواصل المؤسسي', 'exp' => 6, 'exp_lvl' => 3, 'salary' => 4000, 'passport' => 'approved'],
            ['name' => 'سلمى رامي العلي', 'gender_id' => $female?->id, 'country_code' => 'TN', 'prof_id' => 8, 'qual_id' => 3, 'qual' => 'بكالوريوس تصميم الوسائط المتعددة', 'sub_spec' => 'Motion Graphics & 3D', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 2900, 'passport' => 'none'],
            ['name' => 'منة الله حسام', 'gender_id' => $female?->id, 'country_code' => 'EG', 'prof_id' => 5, 'qual_id' => 3, 'qual' => 'بكالوريوس الهندسة المعمارية', 'sub_spec' => 'تصميم داخلي وديكور', 'exp' => 2, 'exp_lvl' => 1, 'salary' => 2200, 'passport' => 'pending'],
            ['name' => 'أسماء عبد العزيز', 'gender_id' => $female?->id, 'country_code' => 'DZ', 'prof_id' => 3, 'qual_id' => 3, 'qual' => 'بكالوريوس المحاسبة والجباية', 'sub_spec' => 'محاسبة عامة', 'exp' => 5, 'exp_lvl' => 2, 'salary' => 2600, 'passport' => 'approved'],
            ['name' => 'رزان يوسف الغامدي', 'gender_id' => $female?->id, 'country_code' => 'SA', 'prof_id' => 1, 'qual_id' => 3, 'qual' => 'بكالوريوس علوم الحاسبات', 'sub_spec' => 'Frontend Developer React', 'exp' => 3, 'exp_lvl' => 2, 'salary' => 4200, 'passport' => 'approved'],
            ['name' => 'لجين إياد منصور', 'gender_id' => $female?->id, 'country_code' => 'JO', 'prof_id' => 2, 'qual_id' => 3, 'qual' => 'بكالوريوس طب وجراحة عامة', 'sub_spec' => 'طبيبة مقيمة', 'exp' => 2, 'exp_lvl' => 1, 'salary' => 3000, 'passport' => 'pending'],
            ['name' => 'شهد بدر السبيعي', 'gender_id' => $female?->id, 'country_code' => 'SA', 'prof_id' => 7, 'qual_id' => 3, 'qual' => 'بكالوريوس التسويق والمبيعات', 'sub_spec' => 'مشرفة مبيعات منطقة', 'exp' => 4, 'exp_lvl' => 2, 'salary' => 4800, 'passport' => 'approved'],
        ];

        foreach ($candidatesData as $index => $c) {
            $candidateNum = $index + 2;
            $email = "candidate{$candidateNum}@example.com";
            $country = $countries->get($c['country_code']) ?? $egypt;

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'       => $c['name'],
                    'phone'      => '+201' . str_pad($candidateNum, 9, '0', STR_PAD_LEFT),
                    'country_id' => $country?->id,
                    'user_type'  => 'candidate',
                    'status'     => 'active',
                    'password'   => Hash::make('password'),
                ]
            );

            // Generate birth date based on experience
            $birthYear = 2026 - (22 + $c['exp']);
            $birthDate = "{$birthYear}-05-15";

            $skillsList = match($c['prof_id']) {
                1 => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'REST API', 'Git'],
                2 => ['Diagnosis', 'Emergency Care', 'Patient Management', 'Internal Medicine'],
                3 => ['Accounting', 'Financial Analysis', 'Excel', 'Taxation', 'Auditing'],
                4 => ['SEO', 'Google Ads', 'Social Media', 'Content Creation', 'Analytics'],
                5 => ['AutoCAD', 'Structural Engineering', 'Project Management', 'Site Supervision'],
                6 => ['Patient Care', 'ICU', 'First Aid', 'Vital Signs Monitoring'],
                7 => ['B2B Sales', 'Negotiation', 'CRM', 'Lead Generation', 'Sales Strategy'],
                8 => ['Figma', 'Adobe Photoshop', 'UI/UX', 'Illustrator', 'Prototyping'],
                default => ['Communication', 'Teamwork', 'Problem Solving'],
            };

            $profile = UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'birth_date'         => $birthDate,
                    'gender_id'          => $c['gender_id'],
                    'current_country_id' => $country?->id,
                    'qualification_id'   => $c['qual_id'],
                    'qualification'      => $c['qual'],
                    'sub_specialization' => $c['sub_spec'],
                    'profession_id'      => $c['prof_id'],
                    'experience_years'   => $c['exp'],
                    'experience_level_id'=> $c['exp_lvl'],
                    'expected_salary'    => $c['salary'],
                    'willing_to_travel'  => ($index % 2 === 0),
                    'languages'          => ($index % 3 === 0) ? ['العربية', 'الإنجليزية', 'الفرنسية'] : ['العربية', 'الإنجليزية'],
                    'skills'             => $skillsList,
                    'summary'            => "متخصص في {$c['sub_spec']} مع خبرة تزيد عن {$c['exp']} سنوات في سوق العمل العربي. أمتلك مهارات عالية وأسعى للانضمام لفريق عمل مميز.",
                    'status'             => 'approved',
                ]
            );

            if ($profile) {
                // Attach profession and secondary profession
                $secProfId = ($c['prof_id'] % 8) + 1;
                $profile->professions()->sync(array_unique([$c['prof_id'], $secProfId]));

                // Attach target countries (2-3 target countries)
                $targetCodes = ['SA', 'AE', 'KW', 'QA'];
                $targetIds = [];
                foreach ($targetCodes as $tCode) {
                    if ($tCountry = $countries->get($tCode)) {
                        $targetIds[] = $tCountry->id;
                    }
                }
                $profile->targetCountries()->sync(array_slice($targetIds, 0, ($index % 3) + 1));
            }

            // Documents
            Document::updateOrCreate(
                ['user_id' => $user->id, 'document_type' => 'personal_photo'],
                ['file_path' => "documents/personal_photos/candidate_{$candidateNum}.jpg", 'disk' => 'public', 'is_approved' => true]
            );

            Document::updateOrCreate(
                ['user_id' => $user->id, 'document_type' => 'cv'],
                ['file_path' => "documents/cvs/candidate_{$candidateNum}_cv.pdf", 'disk' => 'private', 'is_approved' => true]
            );

            Document::updateOrCreate(
                ['user_id' => $user->id, 'document_type' => 'national_id'],
                ['file_path' => "documents/national_ids/candidate_{$candidateNum}_id.jpg", 'disk' => 'private', 'is_approved' => true]
            );

            // Passport Document based on $c['passport']
            if ($c['passport'] === 'approved') {
                Document::updateOrCreate(
                    ['user_id' => $user->id, 'document_type' => 'passport'],
                    ['file_path' => "documents/passports/candidate_{$candidateNum}_passport.jpg", 'disk' => 'private', 'is_approved' => true]
                );
            } elseif ($c['passport'] === 'pending') {
                Document::updateOrCreate(
                    ['user_id' => $user->id, 'document_type' => 'passport'],
                    ['file_path' => "documents/passports/candidate_{$candidateNum}_passport.jpg", 'disk' => 'private', 'is_approved' => false]
                );
            }
            // If 'none', no passport document is created!

            // Video for some candidates (every 3rd candidate)
            if ($index % 3 === 0) {
                Video::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'video_path'       => "videos/candidate_{$candidateNum}_intro.mp4",
                        'thumbnail_path'   => "videos/thumbnails/candidate_{$candidateNum}_thumb.jpg",
                        'duration_seconds' => rand(45, 120),
                        'file_size_mb'     => rand(10, 25) + 0.50,
                        'status'           => 'approved',
                    ]
                );
            }
        }

        if (isset($this->command)) {
            $this->command->info("Candidate Seeded Successfully! Total candidate accounts created: " . (count($candidatesData) + 1));
            $this->command->info("Candidate 1 Token: " . $tokenResult1->plainTextToken);
        }
    }
}
