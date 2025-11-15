<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /** @var array<string, string> */
    private array $errors;

    /**
     * @param array<string, string> $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct('The given data was invalid.');
        $this->errors = $errors;
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
