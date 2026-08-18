<?php

use Copain\LaravelMailDashboard\Storage\MailStore;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class TestWelcomeMailable extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome aboard');
    }

    public function content(): Content
    {
        return new Content(htmlString: '<h1>Welcome!</h1>');
    }
}

it('captures a plain text email regardless of the mail driver', function () {
    Mail::raw('Hello from the array driver.', function (Message $message) {
        $message->from('sender@example.com', 'The Sender')
            ->to('recipient@example.com')
            ->subject('Plain text email');
    });

    $emails = MailStore::make()->all();

    expect($emails)->toHaveCount(1)
        ->and($emails[0]->subject)->toBe('Plain text email')
        ->and($emails[0]->from)->toBe('The Sender <sender@example.com>')
        ->and($emails[0]->to)->toBe(['recipient@example.com'])
        ->and($emails[0]->textBody)->toBe('Hello from the array driver.')
        ->and($emails[0]->htmlBody)->toBeNull()
        ->and($emails[0]->messageId)->not->toBeNull();
});

it('captures html emails with utf-8 subject, cc, bcc and reply-to', function () {
    Mail::html('<h1>Bonjour</h1><p>Un e-mail en <strong>français</strong> 🇫🇷 avec width=device-width</p>', function (Message $message) {
        $message->from('sender@example.com')
            ->to(['alice@example.com', 'bob@example.com'])
            ->cc('carol@example.com')
            ->bcc('dave@example.com')
            ->replyTo('reply@example.com')
            ->subject('Été indien — réservation confirmée');
    });

    $email = MailStore::make()->all()[0];

    expect($email->subject)->toBe('Été indien — réservation confirmée')
        ->and($email->to)->toBe(['alice@example.com', 'bob@example.com'])
        ->and($email->cc)->toBe(['carol@example.com'])
        ->and($email->bcc)->toBe(['dave@example.com'])
        ->and($email->replyTo)->toBe(['reply@example.com'])
        ->and($email->htmlBody)->toContain('width=device-width')
        ->and(json_encode($email->toDetail()))->not->toBeFalse();
});

it('captures attachments', function () {
    Mail::send([], [], function (Message $message) {
        $message->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('With attachment')
            ->html('<p>See attached.</p>')
            ->attachData('%PDF-1.4 fake pdf content', 'invoice.pdf', ['mime' => 'application/pdf']);
    });

    $email = MailStore::make()->all()[0];

    expect($email->attachments)->toHaveCount(1)
        ->and($email->attachments[0]->filename)->toBe('invoice.pdf')
        ->and($email->attachments[0]->contentType)->toBe('application/pdf')
        ->and($email->attachments[0]->content)->toBe('%PDF-1.4 fake pdf content')
        ->and($email->htmlBody)->toContain('See attached.');
});

it('records the mailable class that produced the email', function () {
    Mail::to('recipient@example.com')->send(new TestWelcomeMailable);

    $email = MailStore::make()->all()[0];

    expect($email->sourceClass)->toBe(TestWelcomeMailable::class)
        ->and($email->subject)->toBe('Welcome aboard')
        ->and($email->htmlBody)->toContain('Welcome!');
});

it('does not capture emails when the dashboard is disabled', function () {
    config()->set('mail-dashboard.enabled', false);

    Mail::raw('Should not be stored', function (Message $message) {
        $message->from('sender@example.com')->to('recipient@example.com')->subject('Hidden');
    });

    expect(MailStore::make()->all())->toBe([]);
});

it('lists emails newest first', function () {
    Mail::raw('First', fn (Message $m) => $m->from('a@example.com')->to('b@example.com')->subject('First'));
    Mail::raw('Second', fn (Message $m) => $m->from('a@example.com')->to('b@example.com')->subject('Second'));

    $subjects = array_map(fn ($email) => $email->subject, MailStore::make()->all());

    expect($subjects)->toBe(['Second', 'First']);
});

it('forgets a single email and clears all of them', function () {
    Mail::raw('First', fn (Message $m) => $m->from('a@example.com')->to('b@example.com')->subject('First'));
    Mail::raw('Second', fn (Message $m) => $m->from('a@example.com')->to('b@example.com')->subject('Second'));
    Mail::raw('Third', fn (Message $m) => $m->from('a@example.com')->to('b@example.com')->subject('Third'));

    $store = MailStore::make();
    $second = $store->all()[1];

    expect($store->forget($second->id))->toBeTrue()
        ->and($store->forget($second->id))->toBeFalse()
        ->and($store->find($second->id))->toBeNull();

    $remaining = array_map(fn ($email) => $email->subject, $store->all());

    expect($remaining)->toBe(['Third', 'First'])
        ->and($store->clear())->toBe(2)
        ->and($store->all())->toBe([]);
});

it('round-trips an email through storage without losing data', function () {
    Mail::send([], [], function (Message $message) {
        $message->from('sender@example.com', 'Sender')
            ->to('recipient@example.com')
            ->subject('Round trip')
            ->html('<p>Body</p>')
            ->attachData('binary content', 'file.bin', ['mime' => 'application/octet-stream']);
    });

    $store = MailStore::make();
    $stored = $store->all()[0];
    $found = $store->find($stored->id);

    expect($found?->toDetail())->toBe($stored->toDetail())
        ->and($found?->attachments[0]->content)->toBe('binary content')
        ->and($found?->raw)->toContain('Subject: Round trip');
});
