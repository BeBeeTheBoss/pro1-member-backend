<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class ServerTimeTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_returns_the_current_server_time_and_timestamp(): void
    {
        config(['app.timezone' => 'Asia/Yangon']);
        Carbon::setTestNow(Carbon::parse('2026-10-31 15:30:45', 'Asia/Yangon'));

        $response = $this->getJson('/api/server-time');

        $response
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'current_time' => '2026-10-31T15:30:45+06:30',
                    'timestamp' => 1793437245,
                    'timezone' => 'Asia/Yangon',
                ],
                'status' => 200,
                'message' => 'Server time retrieved successfully',
            ]);
    }
}
