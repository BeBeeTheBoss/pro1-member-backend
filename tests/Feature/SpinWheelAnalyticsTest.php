<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\SpinRecord;
use App\Models\SpinWheelChanceDaily;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SpinWheelAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_defaults_to_today_and_returns_spin_analytics(): void
    {
        Carbon::setTestNow('2026-10-19 12:00:00');

        $admin = Admin::create([
            'emp_code' => 'ADM-001',
            'name' => 'Analytics Admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department' => 'digital',
        ]);

        $firstUser = $this->createUser('M-001', '09111111111');
        $secondUser = $this->createUser('M-002', '09222222222');

        $superPrize = SpinWheelChanceDaily::create([
            'date' => '2026-10-19',
            'points' => 500,
            'max_times' => 3,
            'type' => 'super_prize',
        ]);
        $megaPrize = SpinWheelChanceDaily::create([
            'date' => '2026-10-19',
            'points' => 5000,
            'max_times' => 1,
            'type' => 'mega_prize',
        ]);

        Setting::create(['attribute' => 'super_prize_target', 'value' => '2']);
        Setting::create(['attribute' => 'mega_prize_target', 'value' => '3']);

        $this->createSpin($firstUser, $superPrize, 500, '2026-10-19 10:10:00');
        $this->createSpin($firstUser, $superPrize, 500, '2026-10-19 10:30:00');
        $this->createSpin($secondUser, $megaPrize, 5000, '2026-10-19 11:00:00');

        $response = $this->actingAs($admin, 'admin')->get(route('spin-wheel-analytics'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SpinWheelAnalytics/Index')
                ->where('analytics.filters.start_date', '2026-10-19')
                ->where('analytics.filters.end_date', '2026-10-19')
                ->where('analytics.trend_granularity', 'hourly')
                ->where('analytics.summary.total_spins', 3)
                ->where('analytics.summary.unique_spinners', 2)
                ->where('analytics.summary.reward_points', 6000)
                ->where('analytics.summary.super_prize_count', 2)
                ->where('analytics.summary.mega_prize_count', 1)
                ->has('analytics.trend', 24)
                ->where('analytics.trend.10.spin_count', 2)
                ->has('analytics.targets', 2)
                ->where('analytics.targets.0.target', 2)
                ->where('analytics.targets.0.threshold_crossings', 1)
                ->where('analytics.targets.1.target', 3)
                ->where('analytics.targets.1.threshold_crossings', 1)
                ->has('analytics.prize_distribution', 2)
                ->has('analytics.inventory', 2)
                ->has('analytics.recent_records', 3));

        $rangeResponse = $this->actingAs($admin, 'admin')->get(route('spin-wheel-analytics', [
            'start_date' => '2026-10-18',
            'end_date' => '2026-10-19',
        ]));

        $rangeResponse->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('analytics.trend_granularity', 'daily')
                ->has('analytics.trend', 2)
                ->where('analytics.trend.0.spin_count', 0)
                ->where('analytics.trend.1.spin_count', 3));
    }

    private function createUser(string $idCard, string $phone): User
    {
        return User::create([
            'name' => "Member {$idCard}",
            'idcard' => $idCard,
            'phone' => $phone,
            'password' => bcrypt('password'),
            'birth_date' => '1990-01-01',
        ]);
    }

    private function createSpin(
        User $user,
        SpinWheelChanceDaily $chance,
        int $rewardPoints,
        string $spunAt
    ): SpinRecord {
        return SpinRecord::create([
            'user_id' => $user->id,
            'spin_wheel_chance_daily_id' => $chance->id,
            'at_max_times' => $chance->max_times,
            'reward_points' => $rewardPoints,
            'spun_at' => $spunAt,
            'date' => Carbon::parse($spunAt)->toDateString(),
        ]);
    }
}
