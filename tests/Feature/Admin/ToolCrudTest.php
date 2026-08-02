<?php

use App\Models\Tool;

test('an admin can create a tool', function () {
    actingAsAdmin();

    $response = $this->post(route('admin.tools.store'), [
        'slug' => 'test-calculator',
        'title' => 'Test Calculator',
        'component' => 'TestCalculator',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.tools.index'));
    $this->assertDatabaseHas('tools', ['slug' => 'test-calculator', 'title' => 'Test Calculator']);
});

test('deleting a tool soft-deletes it and frees the slug for reuse', function () {
    actingAsAdmin();

    $tool = Tool::create([
        'slug' => 'reusable-slug', 'title' => 'Original', 'component' => 'X', 'status' => 'draft', 'keywords' => [],
    ]);

    $this->delete(route('admin.tools.destroy', $tool))->assertRedirect();

    expect(Tool::find($tool->id))->toBeNull()
        ->and(Tool::onlyTrashed()->find($tool->id))->not->toBeNull();

    // The original slug should be immediately reusable by a new tool.
    $response = $this->post(route('admin.tools.store'), [
        'slug' => 'reusable-slug', 'title' => 'New Tool', 'component' => 'Y', 'status' => 'draft',
    ]);
    $response->assertRedirect(route('admin.tools.index'));
    $this->assertDatabaseHas('tools', ['slug' => 'reusable-slug', 'title' => 'New Tool']);
});

test('a trashed tool can be restored and reappears in the index', function () {
    actingAsAdmin();

    $tool = Tool::create([
        'slug' => 'restore-me', 'title' => 'Restore Me', 'component' => 'X', 'status' => 'draft', 'keywords' => [],
    ]);
    $this->delete(route('admin.tools.destroy', $tool));

    $trashedId = Tool::onlyTrashed()->where('slug', 'like', 'restore-me%')->first()->id;

    $this->post(route('admin.tools.restore', $trashedId))->assertRedirect();

    $restored = Tool::find($trashedId);
    expect($restored)->not->toBeNull()
        ->and($restored->slug)->toBe('restore-me');
});

test('force-deleting a trashed tool removes it permanently', function () {
    actingAsAdmin();

    $tool = Tool::create([
        'slug' => 'gone-forever', 'title' => 'Gone Forever', 'component' => 'X', 'status' => 'draft', 'keywords' => [],
    ]);
    $this->delete(route('admin.tools.destroy', $tool));
    $trashedId = Tool::onlyTrashed()->where('slug', 'like', 'gone-forever%')->first()->id;

    $this->delete(route('admin.tools.force-delete', $trashedId))->assertRedirect();

    expect(Tool::withTrashed()->find($trashedId))->toBeNull();
});

test('bulk-publishing tools updates all selected rows', function () {
    actingAsAdmin();

    $a = Tool::create(['slug' => 'bulk-a', 'title' => 'A', 'component' => 'X', 'status' => 'draft', 'keywords' => []]);
    $b = Tool::create(['slug' => 'bulk-b', 'title' => 'B', 'component' => 'X', 'status' => 'draft', 'keywords' => []]);

    $this->post(route('admin.tools.bulk'), ['ids' => [$a->id, $b->id], 'action' => 'publish'])->assertRedirect();

    expect($a->fresh()->status)->toBe('published')
        ->and($b->fresh()->status)->toBe('published');
});

test('bulk-deleting tools soft-deletes all selected rows', function () {
    actingAsAdmin();

    $a = Tool::create(['slug' => 'bulk-del-a', 'title' => 'A', 'component' => 'X', 'status' => 'draft', 'keywords' => []]);
    $b = Tool::create(['slug' => 'bulk-del-b', 'title' => 'B', 'component' => 'X', 'status' => 'draft', 'keywords' => []]);

    $this->post(route('admin.tools.bulk'), ['ids' => [$a->id, $b->id], 'action' => 'delete'])->assertRedirect();

    expect(Tool::find($a->id))->toBeNull()
        ->and(Tool::onlyTrashed()->find($a->id))->not->toBeNull()
        ->and(Tool::find($b->id))->toBeNull();
});

test('a non-admin editor cannot reach the tools admin routes without auth', function () {
    $response = $this->get(route('admin.tools.index'));

    $response->assertRedirect(route('login'));
});
