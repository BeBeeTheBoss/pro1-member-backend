<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use App\Models\EventPlatform;
use App\Models\EventPlatformLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventWithoutPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_event_can_be_created_without_platforms(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->createAdmin(), 'admin')->post(route('events.store'), [
            'name' => 'Platform-free event',
            'description' => 'An event without platform links.',
            'start_date' => '2026-10-20',
            'start_time' => '10:00',
            'end_date' => '2026-10-20',
            'end_time' => '12:00',
            'image' => UploadedFile::fake()->image('event.jpg'),
        ]);

        $response->assertRedirect(route('events'))
            ->assertSessionHasNoErrors();

        $event = Event::where('name', 'Platform-free event')->firstOrFail();

        $this->assertDatabaseCount('event_platform_links', 0);
        $this->assertCount(0, $event->platformLinks);
    }

    public function test_an_event_can_be_updated_to_have_no_platforms(): void
    {
        $event = Event::create([
            'name' => 'Existing event',
            'description' => 'Existing description.',
            'start_date' => '2026-10-20',
            'start_time' => '10:00',
            'end_date' => '2026-10-20',
            'end_time' => '12:00',
        ]);
        $platform = EventPlatform::create(['name' => 'Website']);
        EventPlatformLink::create([
            'event_id' => $event->id,
            'event_platform_id' => $platform->id,
            'link' => 'https://example.com',
        ]);

        $response = $this->actingAs($this->createAdmin(), 'admin')->post(route('events.update'), [
            'id' => $event->id,
            'name' => 'Updated event',
            'description' => 'Updated without platform links.',
            'start_date' => '2026-10-21',
            'start_time' => '10:00',
            'end_date' => '2026-10-21',
            'end_time' => '12:00',
        ]);

        $response->assertRedirect(route('events'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated event',
        ]);
        $this->assertDatabaseMissing('event_platform_links', [
            'event_id' => $event->id,
        ]);
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'emp_code' => 'ADM-EVENT',
            'name' => 'Event Admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department' => 'digital',
        ]);
    }
}
