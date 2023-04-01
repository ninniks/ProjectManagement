<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AcceptOnlyJsonRequestTest extends TestCase
{
    public function test_accept_only_json_requests(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/api/projects')
            ->assertStatus(415)
            ->assertExactJson(["error" => "Unsupported Content-Type"]);
    }
}
