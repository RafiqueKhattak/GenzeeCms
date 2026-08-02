<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\PolicyChecker\ContentPolicyCheckerInterface;
use App\Services\PolicyChecker\DuplicateContentChecker;
use App\Services\PolicyChecker\PolicyCheckRequest;
use App\Services\PolicyChecker\PolicyCheckResult;
use App\Services\PolicyChecker\PolicyFinding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyCheckController extends Controller
{
    public function check(
        Request $request,
        ContentPolicyCheckerInterface $checker,
        DuplicateContentChecker $duplicateChecker
    ): JsonResponse {
        $data = $request->validate([
            'type' => ['required', 'in:blog,news,tool'],
            'title' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer'],
            'post_id' => ['nullable', 'integer'],
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

        // The duplicate check needs database access, so it lives outside the
        // ContentPolicyCheckerInterface (which stays Eloquent-free) and is
        // merged into the result here.
        $duplicate = $duplicateChecker->check(
            $data['title'] ?? '',
            $data['body'] ?? '',
            $data['post_id'] ?? null
        );

        return response()->json($this->merge($result, $duplicate)->toArray());
    }

    protected function merge(PolicyCheckResult $result, PolicyFinding $extra): PolicyCheckResult
    {
        $findings = [...$result->findings, $extra];

        $score = 100;
        foreach ($findings as $finding) {
            $score -= match ($finding->severity) {
                'fail' => 30,
                'warn' => 8,
                default => 0,
            };
        }
        $score = max(0, min(100, $score));

        $hasFail = collect($findings)->contains(fn (PolicyFinding $f) => $f->severity === 'fail');
        $status = $hasFail ? 'not_approvable' : ($score >= 85 ? 'approvable' : 'needs_work');

        return new PolicyCheckResult($score, $status, $findings, $result->checkedBy);
    }
}
