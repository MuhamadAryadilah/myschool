<?php

it('menampilkan ikon di setiap menu bar atas admin', function () {
    $html = $this->actingAs(makeAdmin())->get(route('admin.dashboard'))->assertOk()->getContent();

    expect($html)->not->toContain('hidden h-4 w-4 2xl:block');
    expect(substr_count($html, 'stroke-linejoin="round" d="M'))->toBeGreaterThanOrEqual(8);
});