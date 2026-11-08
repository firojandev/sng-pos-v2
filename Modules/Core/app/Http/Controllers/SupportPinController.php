<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportPinController extends Controller
{
    /**
     * Verify the logged-in user's own support PIN. Used to temporarily
     * reveal masked salary figures in the UI (employees list, salary info,
     * and the employee's own "My Salary" page).
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $pin = $request->string('pin')->toString();

        $valid = $user && ! empty($user->support_pin) && hash_equals((string) $user->support_pin, $pin);

        if (! $valid) {
            return response()->json(['valid' => false], 422);
        }

        return response()->json(['valid' => true]);
    }
}
