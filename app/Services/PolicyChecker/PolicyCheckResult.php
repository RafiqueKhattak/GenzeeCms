<?php

namespace App\Services\PolicyChecker;

class PolicyCheckResult
{
    /** @param PolicyFinding[] $findings */
    public function __construct(
        public readonly int $score,
        /** @var 'approvable'|'needs_work'|'not_approvable' */
        public readonly string $status,
        public readonly array $findings,
        public readonly string $checkedBy = 'rule-based',
    ) {
    }

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'status' => $this->status,
            'checked_by' => $this->checkedBy,
            'findings' => array_map(fn (PolicyFinding $f) => $f->toArray(), $this->findings),
        ];
    }
}
