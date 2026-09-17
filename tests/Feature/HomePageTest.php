<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('links to the filament login page without public registration', function () {
    // Public panel registration is disabled by design — accounts are
    // created by مدير النظام. Guests see login only.
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee(route('filament.admin.auth.login'), false);
    expect(Route::has('filament.admin.auth.register'))->toBeFalse();
});

test('shows the dashboard link to authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee(route('filament.admin.pages.dashboard'), false);
});

test('loads its assets without external network dependencies', function () {
    $html = $this->get(route('home'))->getContent();

    // Compiled CSS comes from the local build, not a dev server or CDN.
    expect($html)->toContain('/build/assets/');

    expect($html)
        ->not->toContain('googleapis')
        ->not->toContain('gstatic')
        ->not->toContain('unpkg')
        ->not->toContain('jsdelivr')
        ->not->toContain('cdn.jsdelivr')
        ->not->toContain('5173');
});
