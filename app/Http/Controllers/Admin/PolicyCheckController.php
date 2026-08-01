<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\PolicyChecker\ContentPolicyCheckerInterface;
use App\Services\PolicyChecker\PolicyCheckRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyCheckController extends Controller
{
    public function check(Request $request, ContentPolicyCheckerInterface $checker): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:blog,news,tool'],
            'title' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ]);

        $category = ! empty($data['category_id'])
            ? Category::find($data['category_id'])?->name
            : null;

        $result = $checker->check(new PolicyCheckRequest(
            type: $data['type'],
            title: $data['title'] ?? '',
            bodyHtml: $data['body'] ?? '',
            excerpt: $data['excerpt'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
            featuredImage: $data['featured_image'] ?? null,
            category: $category,
            tags: $data['tags'] ?? [],
        ));

        return response()->json($result->toArray());
    }
}
