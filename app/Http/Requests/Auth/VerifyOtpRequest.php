<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'           => 'required|email',
            'otp'             => 'required|string|size:6',
            'remember_device' => 'boolean',
            'device_name'     => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio.',
            'otp.required'   => 'El código OTP es obligatorio.',
            'otp.size'       => 'El código OTP debe tener exactamente 6 dígitos.',
        ];
    }
}