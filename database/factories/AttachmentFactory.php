<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'uploaded_by' => $user,
            'attachable_type' => Project::class,
            'attachable_id' => Project::factory(),
            'original_name' => 'document.pdf',
            'stored_name' => 'attachments/' . fake()->uuid() . '.pdf',
            'disk' => 'local',
            'path' => 'attachments/' . fake()->uuid() . '.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 102400,
            'checksum' => md5('dummy_content'),
            'visibility' => 'private',
            'metadata' => [
                'ip' => '127.0.0.1',
            ],
        ];
    }
}
