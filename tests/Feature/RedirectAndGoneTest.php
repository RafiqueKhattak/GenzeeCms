<?php

use App\Models\Redirect;

test('a 301 redirect entry sends visitors to the new path', function () {
    Redirect::create(['from_path' => '/old-page/', 'to_path' => '/new-page/', 'status_code' => 301]);

    $response = getRaw('/old-page/');

    $response->assertStatus(301)->assertRedirect('/new-page/');
});

test('a 410 redirect entry renders the Gone page with a 410 status', function () {
    Redirect::create(['from_path' => '/retired-tool/', 'to_path' => null, 'status_code' => 410]);

    $response = getRaw('/retired-tool/');

    $response->assertStatus(410);
});

test('a 404 with no matching redirect entry stays a plain 404', function () {
    $response = getRaw('/this-path-was-never-a-thing/');

    $response->assertStatus(404);
});

test('a redirect entry also catches a route that matched but whose record is gone', function () {
    // /tools/{slug}/ matches the route, but no Tool with this slug exists —
    // this is the "route matched, record not found" 404 path, which the
    // same NotFoundHttpException handler in bootstrap/app.php must also catch.
    Redirect::create(['from_path' => '/tools/retired-calculator/', 'to_path' => '/tools/', 'status_code' => 302]);

    $response = getRaw('/tools/retired-calculator/');

    $response->assertStatus(302)->assertRedirect('/tools/');
});
