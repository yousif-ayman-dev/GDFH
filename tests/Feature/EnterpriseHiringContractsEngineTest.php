<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ProposalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseHiringContractsEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function createOnboardedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(Str::random(8)),
            'account_type' => 'client',
        ], $attributes));
    }

    public function test_freelancer_can_submit_proposal_for_marketplace_project(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create([
            'owner_id' => $client->id,
            'visibility' => 'marketplace',
        ]);

        $response = $this->actingAs($freelancer)->post(route('projects.proposals.store', $project), [
            'bid_amount' => 1200.00,
            'delivery_days' => 10,
            'cover_letter' => 'I have 5 years experience in Laravel and Vue.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('proposals', [
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'bid_amount' => 1200.00,
            'status' => 'pending',
        ]);
    }

    public function test_project_owner_cannot_submit_proposal_on_own_project(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $project = Project::factory()->create(['owner_id' => $client->id]);

        $response = $this->actingAs($client)->post(route('projects.proposals.store', $project), [
            'bid_amount' => 500.00,
            'delivery_days' => 5,
            'cover_letter' => 'Testing self proposal rejection.',
        ]);

        $response->assertSessionHasErrors('proposal');
    }

    public function test_duplicate_proposal_submission_is_prevented(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id]);

        Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
        ]);

        $response = $this->actingAs($freelancer)->post(route('projects.proposals.store', $project), [
            'bid_amount' => 800.00,
            'delivery_days' => 7,
            'cover_letter' => 'Duplicate proposal test.',
        ]);

        $response->assertSessionHasErrors('proposal');
    }

    public function test_client_can_accept_proposal_and_issue_contract(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancerA = $this->createOnboardedUser(['account_type' => 'freelancer']);
        $freelancerB = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id, 'status' => 'open']);

        $proposalA = Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancerA->id,
            'status' => 'pending',
        ]);

        $proposalB = Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancerB->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($client)->post(route('proposals.accept', $proposalA));

        $response->assertRedirect();
        $this->assertEquals('accepted', $proposalA->fresh()->status);
        $this->assertEquals('rejected', $proposalB->fresh()->status);
        $this->assertEquals('in_progress', $project->fresh()->status);

        $this->assertDatabaseHas('contracts', [
            'project_id' => $project->id,
            'proposal_id' => $proposalA->id,
            'client_id' => $client->id,
            'freelancer_id' => $freelancerA->id,
            'status' => 'active',
        ]);
    }

    public function test_client_can_reject_proposal(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id]);
        $proposal = Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($client)->post(route('proposals.reject', $proposal));

        $response->assertRedirect();
        $this->assertEquals('rejected', $proposal->fresh()->status);
    }

    public function test_freelancer_can_withdraw_proposal(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id]);
        $proposal = Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($freelancer)->post(route('proposals.withdraw', $proposal));

        $response->assertRedirect();
        $this->assertEquals('withdrawn', $proposal->fresh()->status);
    }

    public function test_contract_completion_workflow(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id, 'status' => 'in_progress']);

        $contract = Contract::factory()->create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($client)->post(route('contracts.complete', $contract));

        $response->assertRedirect();
        $this->assertEquals('completed', $contract->fresh()->status);
        $this->assertEquals('completed', $project->fresh()->status);
    }

    public function test_unauthorized_user_cannot_access_or_manage_other_user_contracts(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);
        $stranger = $this->createOnboardedUser();

        $contract = Contract::factory()->create([
            'client_id' => $client->id,
            'freelancer_id' => $freelancer->id,
        ]);

        $response = $this->actingAs($stranger)->get(route('contracts.show', $contract));

        $response->assertStatus(403);
    }
}
