<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class BranchNameRegex implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = $this->data['subdomain_pattern'];

        if (!empty($pattern) && !preg_match($pattern, $value)) {
            $fail('The subdomain regex pattern must be match with the branch name.');
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
