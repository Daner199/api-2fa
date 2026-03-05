<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Mail\OtpMail;
use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * PASO 1 del flujo: recibe email y password
     * Si el dispositivo es de confianza → da token directo
     * Si no → genera y envía OTP por correo
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Buscar usuario por email
        $user = User::where('email', $request->email)->first();

        // Verificar que exista y que la contraseña sea correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        // Verificar si viene el header de dispositivo de confianza
        $deviceToken = $request->header('X-Device-Token');

        if ($deviceToken) {
            $trusted = TrustedDevice::where('user_id', $user->id)
                ->where('device_token', $deviceToken)
                ->first();

            if ($trusted) {
               
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'success'      => true,
                    'message'      => 'Login exitoso (dispositivo de confianza).',
                    'requires_otp' => false,
                    'token'        => $token,
                    'user'         => $user,
                ]);
            }
        }

       
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar OTP hasheado y tiempo de expiración (10 minutos)
        $user->update([
            'otp_code'       => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Enviar por correo
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'success'      => true,
            'message'      => 'Se envió un código OTP a tu correo. Válido por 10 minutos.',
            'requires_otp' => true,
        ]);
    }

    /**
     * PASO 2 del flujo: valida el OTP recibido por correo
     * Si remember_device = true → guarda dispositivo como de confianza
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Verificar si el OTP ya expiró
        if (!$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'El código OTP ha expirado. Solicita uno nuevo.',
            ], 422);
        }

        // Verificar que el código sea correcto
        if (!Hash::check($request->otp, $user->otp_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Código OTP incorrecto.',
            ], 422);
        }

        // Limpiar el OTP para que no pueda usarse de nuevo
        $user->update([
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        // Crear token de acceso con Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = [
            'success' => true,
            'message' => 'Verificación exitosa.',
            'token'   => $token,
            'user'    => $user,
        ];

        // Si el usuario quiere recordar este dispositivo
        if ($request->boolean('remember_device')) {
            $deviceToken = Str::uuid()->toString();

            TrustedDevice::create([
                'user_id'      => $user->id,
                'device_token' => $deviceToken,
                'device_name'  => $request->device_name ?? 'Dispositivo desconocido',
            ]);

            // Devolver el device_token para que el cliente lo guarde
            $response['device_token'] = $deviceToken;
            $response['message']      = 'Verificación exitosa. Dispositivo guardado como de confianza.';
        }

        return response()->json($response);
    }

    /**
     * Cerrar sesión: invalida el token actual
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /**
     * Retorna los datos del usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user(),
        ]);
    }
}