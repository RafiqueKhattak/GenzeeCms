<?php

namespace App\Services\PolicyChecker;

class PolicyFinding
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        /** @var 'pass'|'warn'|'fail' */
        public readonly string $severity,
        public readonly string $message,
        public readonly ?string $suggestion = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'severity' => $this->severity,
            'message' => $this->message,
            'suggestion' => $this->suggestion,
        ];
    }
}
