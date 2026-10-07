<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function makeAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function makeParent(): User
{
    return User::factory()->create(['role' => 'orang_tua']);
}