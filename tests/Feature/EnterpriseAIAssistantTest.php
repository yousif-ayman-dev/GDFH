<?php

namespace Tests\Feature;

use App\Models\AIConversation;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\AIAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseAIAssistantTest extends TestCase
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

    public function test_ai_dashboard_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('ai.index'));

        $response->assertStatus(200);
        $response->assertSee('مساعد الذكاء الاصطناعي للمؤسسة');
        $response->assertSee('مؤشر صحة الأداء');
    }

    public function test_unauthenticated_user_cannot_access_ai_dashboard(): void
    {
        $response = $this->get(route('ai.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_conversation_creation(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->post(route('ai.conversations.store'), [
            'title' => 'محادثة تحليل الأداء',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ai_conversations', [
            'user_id' => $user->id,
            'title' => 'محادثة تحليل الأداء',
        ]);
    }

    public function test_message_storage_and_rule_based_response_generation(): void
    {
        $user = $this->createOnboardedUser();
        $conversation = AIConversation::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('ai.conversations.messages.store', $conversation), [
            'message' => 'ما هي حالة المهام والمشاريع الحالية؟',
        ]);

        $response->assertRedirect();

        // Should store user message and assistant reply
        $this->assertDatabaseHas('ai_messages', [
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'ما هي حالة المهام والمشاريع الحالية؟',
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
        ]);
    }

    public function test_workspace_analysis_generation(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Task::factory()->create([
            'project_id' => $project->id,
            'due_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
            'status' => 'todo',
        ]);

        $aiService = app(AIAssistantService::class);
        $analysis = $aiService->getWorkspaceAnalysis($user);

        $this->assertArrayHasKey('health_score', $analysis);
        $this->assertArrayHasKey('strengths', $analysis);
        $this->assertArrayHasKey('weaknesses', $analysis);
        $this->assertArrayHasKey('recommendations', $analysis);
        $this->assertArrayHasKey('warnings', $analysis);

        $this->assertGreaterThan(0, count($analysis['warnings']));
    }

    public function test_conversation_deletion(): void
    {
        $user = $this->createOnboardedUser();
        $conversation = AIConversation::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('ai.conversations.destroy', $conversation));

        $response->assertRedirect();
        $this->assertDatabaseMissing('ai_conversations', ['id' => $conversation->id]);
    }

    public function test_unauthorized_user_cannot_manage_other_user_conversation(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $conversation = AIConversation::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($stranger)->delete(route('ai.conversations.destroy', $conversation));

        $response->assertStatus(403);
    }

    public function test_gemini_ai_provider_with_faked_http_response(): void
    {
        $user = $this->createOnboardedUser();
        config(['services.gemini.api_key' => 'test-api-key-123']);

        \Illuminate\Support\Facades\Http::fake([
            'generativelanguage.googleapis.com/*' => \Illuminate\Support\Facades\Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'مرحباً، أنا نموذج جيميناي للذكاء الاصطناعي!']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $provider = app(\App\Services\AI\AIProviderInterface::class);
        $response = $provider->generateResponse($user, 'اختبار المساعد');

        $this->assertEquals('مرحباً، أنا نموذج جيميناي للذكاء الاصطناعي!', $response);
    }

    public function test_gemini_provider_falls_back_when_api_key_empty(): void
    {
        config(['services.gemini.api_key' => null]);

        $provider = app(\App\Services\AI\AIProviderInterface::class);
        $this->assertInstanceOf(\App\Services\AI\RuleBasedAIProvider::class, $provider);
    }
}
