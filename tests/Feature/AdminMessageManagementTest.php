<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminMessageManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_contact_submission_appears_in_admin_and_unsafe_content_is_escaped(): void
    {
        $dangerousName = '<script>alert("name")</script>';
        $dangerousMessage = '<img src=x onerror=alert("message")> Please share the collection details.';

        $this->post(route('contacts.store'), [
            'name' => $dangerousName,
            'email' => 'contact@example.test',
            'message' => $dangerousMessage,
        ])->assertRedirect()->assertSessionHas('status', 'Message sent successfully.');

        $message = ContactMessage::query()->firstOrFail();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee($dangerousName)
            ->assertDontSee($dangerousName, false)
            ->assertDontSee($dangerousMessage, false);
        $this->actingAs($admin)->get(route('admin.messages.show', $message))
            ->assertOk()
            ->assertSee($dangerousMessage)
            ->assertDontSee($dangerousMessage, false);
    }

    public function test_admin_can_mark_message_read_and_replied_with_allow_listed_statuses(): void
    {
        $admin = User::factory()->admin()->create();
        $message = ContactMessage::factory()->create(['status' => 'unread']);

        $this->actingAs($admin)->patchJson(route('admin.messages.update', $message), ['status' => 'read'])
            ->assertOk()
            ->assertJsonPath('message', 'Message marked as read.');
        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'read']);

        $this->actingAs($admin)->patchJson(route('admin.messages.update', $message), ['status' => 'replied'])
            ->assertOk()
            ->assertJsonPath('message', 'Message marked as replied.');
        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'replied']);

        $this->actingAs($admin)->patchJson(route('admin.messages.update', $message), ['status' => 'deleted'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'replied']);
    }
}
