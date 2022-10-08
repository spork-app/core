<?php

namespace Spork\Core\Tests\Unit;

use Spork\Core\Contracts\ActionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class FakeAction implements ActionInterface
{
    public function name(): string
    {
        return 'Fake Action';
    }

    public function route(): string
    {
        return '/api/route-app';
    }

    public function validation(array $rules): void
    {
        if (empty($rules)) {
            return;
        }

        request()->validate($rules);
    }

    public function tags(): array
    {
        return [];
    }

    // This will be how we execute this action.
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'Hello',
        ]);
    }
}
