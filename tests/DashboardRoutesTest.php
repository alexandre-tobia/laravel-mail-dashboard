<?php

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

function sendTestEmail(string $subject = 'Hello'): void
{
    Mail::send([], [], function (Message $message) use ($subject) {
        $message->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject($subject)
            ->html('<p>Hello world</p>')
            ->attachData('file content', 'notes.txt', ['mime' => 'text/plain']);
    });
}

it('serves the dashboard view', function () {
    $this->get('/mail-dashboard')
        ->assertOk()
        ->assertSee('mail-dashboard', escape: false)
        ->assertSee('window.__MAIL_DASHBOARD__', escape: false);
});

it('serves the built assets', function () {
    $this->get('/mail-dashboard/assets/app.js')->assertOk()->assertHeader('Content-Type', 'text/javascript; charset=utf-8');
    $this->get('/mail-dashboard/assets/app.css')->assertOk()->assertHeader('Content-Type', 'text/css; charset=utf-8');
    $this->get('/mail-dashboard/assets/other.js')->assertNotFound();
});

it('lists emails as json', function () {
    sendTestEmail('Listed email');

    $this->getJson('/mail-dashboard/api/emails')
        ->assertOk()
        ->assertJsonCount(1, 'emails')
        ->assertJsonPath('emails.0.subject', 'Listed email')
        ->assertJsonPath('emails.0.attachments_count', 1)
        ->assertJsonPath('count', 1);
});

it('shows a single email with bodies and headers', function () {
    sendTestEmail('Detailed email');

    $id = $this->getJson('/mail-dashboard/api/emails')->json('emails.0.id');

    $this->getJson("/mail-dashboard/api/emails/{$id}")
        ->assertOk()
        ->assertJsonPath('email.subject', 'Detailed email')
        ->assertJsonPath('email.attachments.0.filename', 'notes.txt');

    $this->getJson('/mail-dashboard/api/emails/00000000000000000000_0000000000000000')->assertNotFound();
});

it('downloads an attachment', function () {
    sendTestEmail();

    $id = $this->getJson('/mail-dashboard/api/emails')->json('emails.0.id');

    $response = $this->get("/mail-dashboard/api/emails/{$id}/attachments/0");

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename="notes.txt"');

    expect($response->getContent())->toBe('file content');

    $this->get("/mail-dashboard/api/emails/{$id}/attachments/99")->assertNotFound();
});

it('deletes a single email', function () {
    sendTestEmail('To delete');
    sendTestEmail('To keep');

    $emails = $this->getJson('/mail-dashboard/api/emails')->json('emails');
    $target = collect($emails)->firstWhere('subject', 'To delete');

    $this->withSession(['_token' => 'test-token'])
        ->deleteJson("/mail-dashboard/api/emails/{$target['id']}", [], ['X-CSRF-TOKEN' => 'test-token'])
        ->assertNoContent();

    $this->getJson('/mail-dashboard/api/emails')
        ->assertJsonCount(1, 'emails')
        ->assertJsonPath('emails.0.subject', 'To keep');
});

it('deletes all emails', function () {
    sendTestEmail('One');
    sendTestEmail('Two');

    $this->withSession(['_token' => 'test-token'])
        ->deleteJson('/mail-dashboard/api/emails', [], ['X-CSRF-TOKEN' => 'test-token'])
        ->assertNoContent();

    $this->getJson('/mail-dashboard/api/emails')->assertJsonCount(0, 'emails');
});

it('returns 404 on every route when disabled', function () {
    config()->set('mail-dashboard.enabled', false);

    $this->get('/mail-dashboard')->assertNotFound();
    $this->getJson('/mail-dashboard/api/emails')->assertNotFound();
});
