<?php

use App\Models\User;

class DashboardTest extends TestCase
{
    /** @test */
    public function view_json_dashboard()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', '/api/dashboard')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    'best_selling' => [],
                    'low_quantity' => []
                ],
                'error'
            ]);
    }
}
