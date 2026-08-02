<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CollaborationEngineTest extends TestCase
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

    public function test_user_can_create_comment_on_project(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('projects.comments.store', $project), [
            'body' => 'This is a test discussion comment.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'body' => 'This is a test discussion comment.',
        ]);
    }

    public function test_user_can_reply_to_comment(): void
    {
        $user1 = $this->createOnboardedUser();
        $user2 = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user1->id]);

        $comment = Comment::factory()->create([
            'user_id' => $user1->id,
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
        ]);

        $response = $this->actingAs($user2)->post(route('comments.replies.store', $comment), [
            'body' => 'This is a threaded reply.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'user_id' => $user2->id,
            'parent_id' => $comment->id,
            'body' => 'This is a threaded reply.',
        ]);
    }

    public function test_comment_author_can_edit_comment(): void
    {
        $user = $this->createOnboardedUser();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patch(route('comments.update', $comment), [
            'body' => 'Updated comment body text.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Updated comment body text.', $comment->fresh()->body);
        $this->assertTrue($comment->fresh()->isEdited());
    }

    public function test_comment_author_or_project_owner_can_delete_comment(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $member = $this->createOnboardedUser();

        $comment = Comment::factory()->create([
            'user_id' => $member->id,
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
        ]);

        // Project owner moderates and deletes comment
        $response = $this->actingAs($owner)->delete(route('comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_unauthorized_user_cannot_delete_other_user_comment(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $commentAuthor = $this->createOnboardedUser();

        $comment = Comment::factory()->create([
            'user_id' => $commentAuthor->id,
        ]);

        $response = $this->actingAs($stranger)->delete(route('comments.destroy', $comment));

        $response->assertStatus(403);
    }

    public function test_comment_actions_log_activity(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $this->actingAs($user)->post(route('projects.comments.store', $project), [
            'body' => 'Activity logging comment test',
        ]);

        $this->assertDatabaseHas('activities', [
            'event' => 'comment_created',
            'user_id' => $user->id,
            'subject_id' => $project->id,
        ]);
    }

    public function test_mentions_are_parsed_correctly(): void
    {
        $comment = new Comment([
            'body' => 'Hey @john_doe and @jane_smith check this out!',
        ]);

        $mentions = $comment->parseMentions();

        $this->assertEquals(['john_doe', 'jane_smith'], $mentions);
    }
}
