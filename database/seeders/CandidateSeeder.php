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
        $egypt = Country::where('code', 'EG')->first();
        $saudi = Country::where('code', 'SA')->first();
        $uae   = Country::where('code', 'AE')->first();
        $male  = Gender::where('code', 'male')->first();
        $female = Gender::where('code', 'female')->first();

        // -------------------------------------------------------------
        // Candidate 1: Full Profile (Ready for GET /candidate/profile)
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

        // Clear existing tokens and create a stable test token
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

        // Attach professions and target countries
        $softwareEngineer = Profession::find(1);
        $civilEngineer    = Profession::find(5);

        if ($profile1) {
            $professionIds = array_filter([$softwareEngineer?->id, $civilEngineer?->id]);
            $profile1->professions()->sync($professionIds);

            $countryIds = array_filter([$egypt?->id, $saudi?->id, $uae?->id]);
            $profile1->targetCountries()->sync($countryIds);
        }

        // Add Documents
        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'personal_photo'],
            [
                'file_path'     => 'documents/personal_photos/candidate_1.jpg',
                'disk'          => 'public',
                'is_approved'   => true,
            ]
        );

        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'cv'],
            [
                'file_path'     => 'documents/cvs/candidate_1_cv.pdf',
                'disk'          => 'private',
                'is_approved'   => true,
            ]
        );

        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'national_id'],
            [
                'file_path'     => 'documents/national_ids/candidate_1_id.jpg',
                'disk'          => 'private',
                'is_approved'   => true,
            ]
        );

        Document::updateOrCreate(
            ['user_id' => $user1->id, 'document_type' => 'passport'],
            [
                'file_path'     => 'documents/passports/candidate_1_passport.jpg',
                'disk'          => 'private',
                'is_approved'   => true,
            ]
        );

        // Add Video
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
        // Candidate 2: Fresh Profile (Ready for testing PUT /upload)
        // -------------------------------------------------------------
        $user2 = User::updateOrCreate(
            ['email' => 'candidate2@example.com'],
            [
                'name'       => 'سارة علي حسن',
                'phone'      => '+201000000002',
                'country_id' => $egypt?->id,
                'user_type'  => 'candidate',
                'status'     => 'active',
                'password'   => Hash::make('password'),
            ]
        );

        $user2->tokens()->delete();
        $user2->createToken('Candidate2TestToken');

        UserProfile::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'birth_date'         => '1998-03-20',
                'gender_id'          => $female?->id,
                'current_country_id' => $egypt?->id,
                'qualification_id'   => 3,
                'qualification'      => 'بكالوريوس تجارة وإدارة أعمال',
                'sub_specialization' => 'محاسبة مالية',
                'profession_id'      => 3,
                'experience_years'   => 2,
                'experience_level_id'=> 2,
                'expected_salary'    => 2000.00,
                'willing_to_travel'  => false,
                'languages'          => ['العربية'],
                'skills'             => ['Accounting', 'Excel', 'Financial Analysis'],
                'summary'            => 'محاسبة مالية طموحة تسعى لتطوير مهاراتها في شركة دولية.',
                'status'             => 'pending',
            ]
        );

        $this->command->info("Candidate Seeded Successfully!");
        $this->command->info("Candidate 1 Token: " . $tokenResult1->plainTextToken);
    }
}
