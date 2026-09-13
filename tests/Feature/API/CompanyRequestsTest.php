<?php

namespace Tests\Feature\API;

use App\Models\Application;
use App\Models\Company;
use App\Models\Country;
use App\Models\Gender;
use App\Models\Profession;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanyRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected User $companyUser;
    protected Company $company;
    protected User $candidateUser;
    protected UserProfile $candidateProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::create([
            'name_ar' => 'مصر',
            'name_en' => 'Egypt',
            'code'    => 'EG',
        ]);

        $profession = Profession::create([
            'title_ar' => 'مهندس برمجيات',
            'title_en' => 'Software Engineer',
        ]);

        $gender = Gender::create([
            'name_ar' => 'ذكر',
            'name_en' => 'Male',
            'code'    => 'male',
        ]);

        // Create Company User & Company
        $this->companyUser = User::create([
            'name'       => 'شركة التقنية المتقدمة',
            'email'      => 'company@tech.com',
            'phone'      => '+201000000099',
            'country_id' => $country->id,
            'user_type'  => 'company',
            'status'     => 'active',
            'password'   => Hash::make('password'),
        ]);

        $this->company = Company::create([
            'user_id'      => $this->companyUser->id,
            'company_name' => 'شركة التقنية المتقدمة',
            'status'       => 'approved',
        ]);

        // Create Candidate User & Profile
        $this->candidateUser = User::create([
            'name'       => 'أحمد محمود العبد',
            'email'      => 'candidate@test.com',
            'phone'      => '+201000000098',
            'country_id' => $country->id,
            'user_type'  => 'candidate',
            'status'     => 'active',
            'password'   => Hash::make('password'),
        ]);

        $this->candidateProfile = UserProfile::create([
            'user_id'            => $this->candidateUser->id,
            'gender_id'          => $gender->id,
            'current_country_id' => $country->id,
            'profession_id'      => $profession->id,
            'sub_specialization' => 'تطوير البرمجيات',
            'status'             => 'approved',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_my_requests(): void
    {
        $response = $this->getJson('/api/my-requests');

        $response->assertStatus(401);
    }

    public function test_candidate_user_is_forbidden_from_company_my_requests(): void
    {
        $response = $this->actingAs($this->candidateUser, 'sanctum')
                         ->getJson('/api/my-requests');

        $response->assertStatus(403)
                 ->assertJson([
                     'status'  => false,
                     'message' => __('messages.companyOnly'),
                 ]);
    }

    public function test_company_user_can_get_my_requests_with_custom_resource(): void
    {
        // Create an application
        $application = Application::create([
            'company_id'           => $this->company->id,
            'candidate_profile_id' => $this->candidateProfile->id,
            'status'               => 'pending',
            'notes'                => 'طلب تواصل مبدئي',
        ]);

        $response = $this->actingAs($this->companyUser, 'sanctum')
                         ->getJson('/api/my-requests');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data'   => [
                         [
                             'id'           => $application->id,
                             'name'         => 'أحمد محمود العبد',
                             'status'       => 'pending',
                             'status_label' => 'طلب تواصل قيد الانتظار',
                         ]
                     ]
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'profession',
                             'request_date',
                             'created_at',
                             'status',
                             'status_label',
                             'notes',
                             'candidate',
                         ]
                     ],
                     'pagination' => [
                         'current_page',
                         'per_page',
                         'total',
                     ]
                 ]);
    }

    public function test_company_cannot_send_contact_request_to_pending_or_rejected_candidate(): void
    {
        $this->candidateProfile->update(['status' => 'pending']);

        $response = $this->actingAs($this->companyUser, 'sanctum')
                         ->postJson("/api/job-seekers/{$this->candidateProfile->id}/contact-request");

        $response->assertStatus(400)
                 ->assertJson([
                     'status'  => false,
                     'message' => __('messages.candidateNotApproved'),
                 ]);

        $this->candidateProfile->update(['status' => 'rejected']);

        $response = $this->actingAs($this->companyUser, 'sanctum')
                         ->postJson("/api/job-seekers/{$this->candidateProfile->id}/contact-request");

        $response->assertStatus(400)
                 ->assertJson([
                     'status'  => false,
                     'message' => __('messages.candidateNotApproved'),
                 ]);
    }
}
