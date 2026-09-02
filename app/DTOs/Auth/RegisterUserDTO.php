<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\RegisterRequest;

/**
 * Data Transfer Object for user registration.
 *
 * Encapsulates all validated registration data,
 * decoupling HTTP input from the domain action layer.
 */
final readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $organizationName = null,
    ) {}

    /**
     * Create a DTO instance from a validated RegisterRequest.
     */
    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            email: strtolower(trim((string) $request->validated('email'))),
            password: (string) $request->validated('password'),
            organizationName: $request->filled('organization_name')
                ? trim((string) $request->validated('organization_name'))
                : null,
        );
    }
}
