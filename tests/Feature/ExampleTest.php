<?php

test('the application sends guests to the login page', function () {
    $this->get('/')
        ->assertRedirect(route('login'));
});
