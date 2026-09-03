<?php

declare(strict_types=1);

namespace App\DTOs\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\AdminUpdateUserRequest;

final readonly class UpdateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public UserRole $role,
        public ?int $organizationId = null,
        public ?string $password = null,
    ) {}

    public static function fromRequest(AdminUpdateUserRequest $request): self
    {
        $roleValue = (string) $request->validated('role', UserRole::User->value);
        $role = UserRole::tryFrom($roleValue) ?? UserRole::User;

        return new self(
            name: trim((string) $request->validated('name')),
            email: strtolower(trim((string) $request->validated('email'))),
            role: $role,
            organizationId: $request->filled('organization_id')
                ? (int) $request->validated('organization_id')
                : null,
            password: $request->filled('password')
                ? (string) $request->validated('password')
                : null,
        );
    }
}
