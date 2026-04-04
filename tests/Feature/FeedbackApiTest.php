<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Feedback;

class FeedbackApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the user can submit a public feedback without name and email.
     */
    public function test_can_submit_feedback(): void
    {
        $payload = [
            'rating' => 5,
            'message' => 'Your system is highly robust and user friendly.',
        ];

        $response = $this->postJson('/api/feedback', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'rating',
                         'message',
                         'user_id',
                         'id',
                         'updated_at',
                         'created_at',
                     ],
                 ]);

        // Check if data actually exists in database
        $this->assertDatabaseHas('feedback', [
            'rating' => 5,
            'message' => 'Your system is highly robust and user friendly.',
        ]);
    }

    /**
     * Test mapping of invalid ratings in feedback submission.
     */
    public function test_fails_feedback_validation_on_invalid_ratings(): void
    {
        $payload = [
            'rating' => 6, // Exceeds max 5
            'message' => 'Testing validation constraints.',
        ];

        $response = $this->postJson('/api/feedback', $payload);

        // Expect standard validation error (422)
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['rating']);
    }

    /**
     * Test retrieving feedback list endpoints.
     */
    public function test_can_retrieve_feedback_list(): void
    {
        // Seed database with fake data first since RefreshDatabase clears it
        Feedback::create([
            'rating' => 4,
            'message' => 'Great stuff.',
        ]);

        Feedback::create([
            'rating' => null, // Rating is nullable
            'message' => 'Also pretty good!',
        ]);

        $response = $this->getJson('/api/feedback');

        $response->assertStatus(200);

        // Basic check if our newly seeded inputs are inside the collection
        $data = $response->json('data.data'); 
        
        $this->assertGreaterThan(0, count($data));
        
        // Ensure structure matches pagination spec
        $response->assertJsonStructure([
            'message',
            'data' => [
                'current_page',
                'data' => [
                    '*' => ['id', 'user_id', 'rating', 'message', 'created_at', 'updated_at']
                ],
                'first_page_url',
                'last_page',
                'total'
            ]
        ]);
    }
}
