<?php

namespace Copain\LaravelMailDashboard\Http\Controllers;

use Copain\LaravelMailDashboard\Data\StoredEmail;
use Copain\LaravelMailDashboard\Storage\MailStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EmailsController
{
    public function index(): JsonResponse
    {
        $store = MailStore::make();
        $emails = $store->all();

        return response()->json([
            'emails' => array_map(fn (StoredEmail $email) => $email->toSummary(), $emails),
            'count' => count($emails),
            'storage' => $store->description(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $email = MailStore::make()->find($id);

        abort_if($email === null, 404);

        return response()->json(['email' => $email->toDetail()]);
    }

    public function attachment(string $id, int $index): SymfonyResponse
    {
        $email = MailStore::make()->find($id);

        abort_if($email === null || ! isset($email->attachments[$index]), 404);

        $attachment = $email->attachments[$index];

        return response($attachment->content, 200, [
            'Content-Type' => $attachment->contentType,
            'Content-Disposition' => 'attachment; filename="'.addcslashes($attachment->filename, '"\\').'"',
            'Content-Length' => (string) $attachment->size,
        ]);
    }

    public function destroy(string $id): Response
    {
        abort_unless(MailStore::make()->forget($id), 404);

        return response()->noContent();
    }

    public function destroyAll(): Response
    {
        MailStore::make()->clear();

        return response()->noContent();
    }
}
