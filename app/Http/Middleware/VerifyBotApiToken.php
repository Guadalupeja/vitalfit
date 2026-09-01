<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBotApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.vitalfit_bot.token');
        $expectedSecret = config('services.vitalfit_bot.header_secret');

        $bearerToken = $request->bearerToken();

        if (!$expectedToken || !$bearerToken || !hash_equals($expectedToken, $bearerToken)) {
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Token inválido o ausente.',
                ],
            ], 401);
        }

        if ($expectedSecret) {
            $receivedSecret = (string) $request->header('X-VitalFit-Bot-Secret', '');

            if (!hash_equals($expectedSecret, $receivedSecret)) {
                return response()->json([
                    'error' => [
                        'code' => 'forbidden',
                        'message' => 'Header secreto inválido o ausente.',
                    ],
                ], 403);
            }
        }

        return $next($request);
    }
}