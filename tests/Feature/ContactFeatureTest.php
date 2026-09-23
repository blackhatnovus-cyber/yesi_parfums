<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ContactFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_message_is_saved_and_exact_success_message_is_shown(): void
    {
        $payload = [
            'name' => 'Issey Cabangon',
            'email' => 'issey@example.com',
            'message' => 'I would like to learn more about the fragrance collection.',
        ];

        $this->post(route('contacts.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('status', 'Message sent successfully.');

        $this->assertDatabaseHas('contact_messages', $payload);
    }

    public function test_invalid_contact_payload_is_rejected_without_database_write(): void
    {
        $this->post(route('contacts.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'message' => 'short',
        ])->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_page_escapes_repopulated_user_input(): void
    {
        $dangerousName = '<script>alert("xss")</script>';

        $this->from(route('contacts'))->post(route('contacts.store'), [
            'name' => $dangerousName,
            'email' => 'invalid',
            'message' => 'A sufficiently long message for validation.',
        ]);

        $this->get(route('contacts'))
            ->assertOk()
            ->assertDontSee($dangerousName, false);
    }
}
