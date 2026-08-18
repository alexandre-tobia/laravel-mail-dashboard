<?php

namespace Copain\LaravelMailDashboard\Listeners;

use Copain\LaravelMailDashboard\Data\StoredEmail;
use Copain\LaravelMailDashboard\Storage\MailStore;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Email;
use Throwable;

class StoreOutgoingEmail
{
    public function handle(MessageSent $event): void
    {
        if (! config('mail-dashboard.enabled')) {
            return;
        }

        $message = $event->sent->getOriginalMessage();

        if (! $message instanceof Email) {
            return;
        }

        /** @var ?string $sourceClass */
        $sourceClass = $event->data['__laravel_notification']
            ?? $event->data['__laravel_mailable']
            ?? null;

        try {
            MailStore::make()->store(StoredEmail::fromSymfonyEmail(
                $message,
                $event->sent->getMessageId(),
                $sourceClass,
            ));
        } catch (Throwable $exception) {
            // Capturing must never break the application's mail flow.
            report($exception);
        }
    }
}
