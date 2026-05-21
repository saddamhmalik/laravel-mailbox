<?php

declare(strict_types=1);

it('serves package css assets', function (): void {
    $response = $this->get('/mailbox/assets/mailbox.css');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/css');
});

it('serves package js assets', function (): void {
    $response = $this->get('/mailbox/assets/mailbox.js');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('javascript');
});

it('renders inbox with bundled assets', function (): void {
    $this->get('/mailbox')
        ->assertOk()
        ->assertSee('mailbox.css', false);
});
