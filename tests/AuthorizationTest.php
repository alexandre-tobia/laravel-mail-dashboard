<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;

function fakeUser(string $email): User
{
    $user = new User;
    $user->email = $email;

    return $user;
}

it('is open in the local environment when no gate is defined', function () {
    $this->get('/mail-dashboard')->assertOk();
});

it('is forbidden outside the local environment when no gate is defined', function () {
    $this->app['env'] = 'production';

    $this->get('/mail-dashboard')->assertForbidden();
    $this->getJson('/mail-dashboard/api/emails')->assertForbidden();
});

it('grants access through the viewMailDashboard gate', function () {
    $this->app['env'] = 'production';

    Gate::define('viewMailDashboard', function (?User $user) {
        return in_array($user?->email, ['atobia@neverhack.com'], true);
    });

    $this->actingAs(fakeUser('atobia@neverhack.com'))
        ->get('/mail-dashboard')
        ->assertOk();
});

it('denies access when the viewMailDashboard gate refuses', function () {
    $this->app['env'] = 'production';

    Gate::define('viewMailDashboard', function (?User $user) {
        return in_array($user?->email, ['atobia@neverhack.com'], true);
    });

    $this->actingAs(fakeUser('intruder@example.com'))
        ->get('/mail-dashboard')
        ->assertForbidden();

    // Guests are denied as well.
    $this->flushSession();
    auth()->logout();
    $this->get('/mail-dashboard')->assertForbidden();
});

it('lets the gate override the local-only default', function () {
    // Even in local, a defined gate has the final say.
    Gate::define('viewMailDashboard', fn (?User $user) => false);

    $this->get('/mail-dashboard')->assertForbidden();
});
