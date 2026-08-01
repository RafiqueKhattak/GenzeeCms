<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Policy Checker Driver
    |--------------------------------------------------------------------------
    |
    | Which App\Services\PolicyChecker\ContentPolicyCheckerInterface
    | implementation to bind. 'rule-based' is free and instant. A future
    | 'llm' driver (calling out to an AI model for nuanced, free-text
    | review) can be added and switched to here without any controller or
    | frontend changes.
    |
    */
    'driver' => env('POLICY_CHECKER_DRIVER', 'rule-based'),
];
