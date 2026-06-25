<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\DismissTutorialAction;
use App\Http\Requests\DismissTutorialRequest;
use Illuminate\Http\JsonResponse;

final class UserController extends Controller
{
    public function __construct(
        private readonly DismissTutorialAction $dismissAction
    ) {}

    /**
     * Dismiss the onboarding tutorial for the authenticated user.
     */
    public function dismissTutorial(DismissTutorialRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $this->dismissAction->execute($user);

        return response()->json([
            'success' => true,
            'message' => 'Tutorial descartado correctamente.',
        ]);
    }
}
