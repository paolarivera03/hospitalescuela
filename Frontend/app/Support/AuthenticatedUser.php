<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class AuthenticatedUser
{
    public static function fromToken(?string $token): array
    {
        $fallback = [
            'id' => null,
            'usuario' => 'USUARIO',
            'correo' => null,
            'nombre' => null,
            'apellido' => null,
            'telefono' => null,
        ];

        if (! $token) {
            return $fallback;
        }

        $tokenData = self::decodeToken($token);
        $userId = $tokenData['id'] ?? null;

        if (! $userId) {
            return array_merge($fallback, $tokenData);
        }

        try {
            $response = Http::withToken($token)->get("http://localhost:3000/api/usuarios/{$userId}");

            if ($response->successful()) {
                return array_merge($fallback, $tokenData, $response->json());
            }
        } catch (\Throwable $e) {
        }

        return array_merge($fallback, $tokenData);
    }

    private static function decodeToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return [];
        }

        $payloadBase64 = strtr($parts[1], '-_', '+/');
        $padding = 4 - (strlen($payloadBase64) % 4);
        if ($padding < 4) {
            $payloadBase64 .= str_repeat('=', $padding);
        }

        $payloadJson = base64_decode($payloadBase64);
        if (! $payloadJson) {
            return [];
        }

        $payload = json_decode($payloadJson, true);
        return is_array($payload) ? $payload : [];
    }
}