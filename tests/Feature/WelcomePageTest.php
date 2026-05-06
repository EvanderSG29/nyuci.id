<?php

test('welcome page renders theme toggle and themed public image assets', function () {
    $this
        ->get(route('home'))
        ->assertOk()
        ->assertSee('welcomePage(', false)
        ->assertSee('role="switch"', false)
        ->assertSee('toggleSimpleTheme()', false)
        ->assertSee('dashboard-light.png', false)
        ->assertSee('dashboard-dark.png', false)
        ->assertSee('edit-light.png', false)
        ->assertSee('edit-dark.png', false)
        ->assertSee('payment-light.png', false)
        ->assertSee('payment-dark.png', false);
});
