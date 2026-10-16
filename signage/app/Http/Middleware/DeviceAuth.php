<?php

namespace App\Http\Middleware;

use App\Models\DigitalSignage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Device-Token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Device token tidak ditemukan. Sertakan header X-Device-Token.',
            ], 401);
        }

        $device = DigitalSignage::where('device_token', $token)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device token tidak valid.',
                'error_code'  => 'TOKEN_INVALID',
                're_register' => true,   // aplikasi TV sebaiknya memanggil POST /api/device/register lagi
            ], 401);
        }

        if ($device->status === 'Nonaktif') {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat dinonaktifkan oleh admin.',
            ], 403);
        }

        $request->merge(['_device' => $device]);

        return $next($request);
    }
}