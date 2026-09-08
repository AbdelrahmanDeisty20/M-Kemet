<?php

namespace Tests\Feature\API;

use App\Models\Country;
use App\Models\Document;
use App\Models\Gender;
use App\Models\Profession;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobSeekerSearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_keyword_parameter()
    {
        // Calling search without keyword parameter must fail validation (422)
        $response = $this->getJson('/api/job-seekers/search');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['keyword']);
    }

    public function test_filter_requires_at_least_one_filter_option()
    {
        // Calling filter without any filter options must fail validation (422)
        $response = $this->getJson('/api/job-seekers/filter');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['filter']);
    }

    public function test_can_search_job_seekers_by_keyword()
    {
        $country = Country::create(['name_ar' => 'مصر', 'name_en' => 'Egypt', 'code' => 'EG']);
        $profession1 = Profession::create(['title_ar' => 'مهندس برمجيات', 'title_en' => 'Software Engineer']);
        $profession2 = Profession::create(['title_ar' => 'طبيب', 'title_en' => 'Doctor']);
        $gender = Gender::create(['name_ar' => 'ذكر', 'name_en' => 'Male', 'code' => 'male']);

        // Candidate 1: Ahmed (Software Engineer, skills: ["Laravel", "Vue"])
        $user1 = User::create([
            'name' => 'أحمد علي',
            'email' => 'ahmed@example.com',
            'user_type' => 'candidate',
            'password' => bcrypt('password'),
        ]);

        UserProfile::create([
            'user_id' => $user1->id,
            'gender_id' => $gender->id,
            'current_country_id' => $country->id,
            'profession_id' => $profession1->id,
            'skills' => ['Laravel', 'Vue'],
            'status' => 'approved',
        ]);

        // Candidate 2: Mohamed (Doctor, skills: ["Surgery"])
        $user2 = User::create([
            'name' => 'محمد محمود',
            'email' => 'mohamed@example.com',
            'user_type' => 'candidate',
            'password' => bcrypt('password'),
        ]);

        UserProfile::create([
            'user_id' => $user2->id,
            'gender_id' => $gender->id,
            'current_country_id' => $country->id,
            'profession_id' => $profession2->id,
            'skills' => ['Surgery'],
            'status' => 'approved',
        ]);

        // Search by keyword "أحمد" (Name)
        $responseName = $this->getJson('/api/job-seekers/search?keyword=أحمد');
        $responseName->assertStatus(200);
        $responseName->assertJsonFragment(['name' => 'أحمد علي']);
        $responseName->assertJsonMissing(['name' => 'محمد محمود']);

        // Search by keyword "مهندس" (Profession)
        $responseProf = $this->getJson('/api/job-seekers/search?keyword=مهندس');
        $responseProf->assertStatus(200);
        $responseProf->assertJsonFragment(['name' => 'أحمد علي']);
        $responseProf->assertJsonMissing(['name' => 'محمد محمود']);

        // Search by keyword "Laravel" (Skill)
        $responseSkill = $this->getJson('/api/job-seekers/search?keyword=Laravel');
        $responseSkill->assertStatus(200);
        $responseSkill->assertJsonFragment(['name' => 'أحمد علي']);
        $responseSkill->assertJsonMissing(['name' => 'محمد محمود']);
    }

    public function test_can_filter_job_seekers_by_country_profession_gender_and_passport_status()
    {
        $countryEgypt = Country::create(['name_ar' => 'مصر', 'name_en' => 'Egypt', 'code' => 'EG']);
        $countryKsa = Country::create(['name_ar' => 'السعودية', 'name_en' => 'Saudi Arabia', 'code' => 'SA']);

        $professionDev = Profession::create(['title_ar' => 'مطور', 'title_en' => 'Developer']);
        $professionDoctor = Profession::create(['title_ar' => 'طبيب', 'title_en' => 'Doctor']);

        $genderMale = Gender::create(['name_ar' => 'ذكر', 'name_en' => 'Male', 'code' => 'male']);
        $genderFemale = Gender::create(['name_ar' => 'أنثى', 'name_en' => 'Female', 'code' => 'female']);

        // Candidate 1: Male, Egypt, Dev, Approved Passport
        $user1 = User::create([
            'name' => 'كريم مصطفى',
            'email' => 'kareem@example.com',
            'user_type' => 'candidate',
            'password' => bcrypt('password'),
        ]);

        UserProfile::create([
            'user_id' => $user1->id,
            'gender_id' => $genderMale->id,
            'current_country_id' => $countryEgypt->id,
            'profession_id' => $professionDev->id,
            'status' => 'approved',
        ]);

        Document::create([
            'user_id' => $user1->id,
            'document_type' => 'passport',
            'file_path' => 'passports/kareem.pdf',
            'is_approved' => true,
        ]);

        // Candidate 2: Female, KSA, Doctor, No Passport
        $user2 = User::create([
            'name' => 'سارة أحمد',
            'email' => 'sara@example.com',
            'user_type' => 'candidate',
            'password' => bcrypt('password'),
        ]);

        UserProfile::create([
            'user_id' => $user2->id,
            'gender_id' => $genderFemale->id,
            'current_country_id' => $countryKsa->id,
            'profession_id' => $professionDoctor->id,
            'status' => 'approved',
        ]);

        // Filter 1: country_id
        $resCountry = $this->getJson('/api/job-seekers/filter?country_id=' . $countryEgypt->id);
        $resCountry->assertStatus(200);
        $resCountry->assertJsonFragment(['name' => 'كريم مصطفى']);
        $resCountry->assertJsonMissing(['name' => 'سارة أحمد']);

        // Filter 2: profession_id
        $resProf = $this->getJson('/api/job-seekers/filter?profession_id=' . $professionDoctor->id);
        $resProf->assertStatus(200);
        $resProf->assertJsonFragment(['name' => 'سارة أحمد']);
        $resProf->assertJsonMissing(['name' => 'كريم مصطفى']);

        // Filter 3: gender_id
        $resGender = $this->getJson('/api/job-seekers/filter?gender_id=' . $genderFemale->id);
        $resGender->assertStatus(200);
        $resGender->assertJsonFragment(['name' => 'سارة أحمد']);
        $resGender->assertJsonMissing(['name' => 'كريم مصطفى']);

        // Filter 4: passport_status (approved passport)
        $resPassApproved = $this->getJson('/api/job-seekers/filter?passport_status=approved');
        $resPassApproved->assertStatus(200);
        $resPassApproved->assertJsonFragment(['name' => 'كريم مصطفى']);
        $resPassApproved->assertJsonMissing(['name' => 'سارة أحمد']);
    }

    public function test_can_fetch_top_6_professions_and_countries_and_filter_options()
    {
        for ($i = 1; $i <= 10; $i++) {
            Profession::create([
                'title_ar' => "مهنة {$i}",
                'title_en' => "Profession {$i}",
                'is_active' => true,
            ]);
        }

        for ($i = 1; $i <= 8; $i++) {
            Country::create([
                'name_ar' => "دولة {$i}",
                'name_en' => "Country {$i}",
                'code' => "C{$i}",
                'is_active' => true,
            ]);
        }

        $resProf = $this->getJson('/api/professions/top-6');
        $resProf->assertStatus(200);
        $resProf->assertJsonCount(6, 'data');

        $resCountry = $this->getJson('/api/countries/top-6');
        $resCountry->assertStatus(200);
        $resCountry->assertJsonCount(6, 'data');

        $resFilterOptions = $this->getJson('/api/job-seekers/filter-options');
        $resFilterOptions->assertStatus(200);
        $resFilterOptions->assertJsonCount(6, 'data.professions');
        $resFilterOptions->assertJsonCount(6, 'data.countries');
    }
}
