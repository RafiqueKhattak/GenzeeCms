<?php

namespace App\Services\PolicyChecker;

/**
 * Contract for anything that can judge a piece of content against Google
 * AdSense / Publisher policies. RuleBasedPolicyChecker is the only
 * implementation today; a future LlmPolicyChecker (calling out to a
 * language model for nuanced, free-text review) can implement this same
 * interface and be swapped in via the 'policy-checker.driver' config value
 * without touching any controller or Vue code.
 */
interface ContentPolicyCheckerInterface
{
    public function check(PolicyCheckRequest $request): PolicyCheckResult;
}
