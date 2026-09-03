<?php

declare(strict_types=1);

namespace App\DTOs\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\AdminCreateUserRequest;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role,
        public ?int $organizationId = null,
        public ?string $newOrganizationName = null,
    ) {}

    public static function fromRequest(AdminCreateUserRequest $request): self
    {
        $roleValue = (string) $request->validated('role', UserRole::User->value);
        $role = UserRole::tryFrom($roleValue) ?? UserRole::User;

        return new self(
            name: trim((string) $request->validated('name')),
            email: strtolower(trim((string) $request->validated('email'))),
            password: (string) $request->validated('password'),
            role: $role,
            organizationId: $request->filled('organization_id')
                ? (int) $request->validated('organization_id')
                : null,
            newOrganizationName: $request->filled('new_organization_name')
                ? trim((string) $request->validated('new_organization_name'))
                : null,
        );
    }
}
