<?php

test('public registration is disabled for the admin-only system', function () {
    $this->get('/register')->assertNotFound();
});
