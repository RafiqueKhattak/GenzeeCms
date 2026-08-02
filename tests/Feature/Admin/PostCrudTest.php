<?php

use App\Models\Post;

test('an admin can create a post', function () {
    actingAsAdmin();

    $response = $this->post(route('admin.posts.store'), [
        'type' => 'blog',
        'slug' => 'test-post',
        'title' => 'Test Post',
        'body' => '<p>Hello world</p>',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.posts.index'));
    $this->assertDatabaseHas('posts', ['slug' => 'test-post', 'title' => 'Test Post']);
});

test('a script tag in the post body is stripped on save', function () {
    actingAsAdmin();

    $this->post(route('admin.posts.store'), [
        'type' => 'blog',
        'slug' => 'xss-test',
        'title' => 'XSS Test',
        'body' => '<p>Safe text</p><script>alert(1)</script>',
        'status' => 'draft',
    ]);

    $post = Post::where('slug', 'xss-test')->firstOrFail();
    expect($post->body)->not->toContain('<script>')
        ->and($post->body)->toContain('Safe text');
});

test('deleting a post soft-deletes it and frees the (type, slug) pair for reuse', function () {
    actingAsAdmin();

    $post = Post::create([
        'type' => 'blog', 'slug' => 'reusable-post-slug', 'title' => 'Original', 'body' => '<p>x</p>', 'status' => 'draft',
    ]);

    $this->delete(route('admin.posts.destroy', $post))->assertRedirect();

    expect(Post::find($post->id))->toBeNull()
        ->and(Post::onlyTrashed()->find($post->id))->not->toBeNull();

    $response = $this->post(route('admin.posts.store'), [
        'type' => 'blog', 'slug' => 'reusable-post-slug', 'title' => 'New Post', 'body' => '<p>y</p>', 'status' => 'draft',
    ]);
    $response->assertRedirect(route('admin.posts.index'));
    $this->assertDatabaseHas('posts', ['slug' => 'reusable-post-slug', 'title' => 'New Post']);
});

test('a restored post keeps its author and category associations', function () {
    actingAsAdmin();

    $post = Post::create([
        'type' => 'news', 'slug' => 'restore-post', 'title' => 'Restore Post', 'body' => '<p>x</p>', 'status' => 'draft',
    ]);
    $this->delete(route('admin.posts.destroy', $post));
    $trashedId = Post::onlyTrashed()->where('slug', 'like', 'restore-post%')->first()->id;

    $this->post(route('admin.posts.restore', $trashedId))->assertRedirect();

    $restored = Post::find($trashedId);
    expect($restored)->not->toBeNull()
        ->and($restored->slug)->toBe('restore-post')
        ->and($restored->type)->toBe('news');
});
