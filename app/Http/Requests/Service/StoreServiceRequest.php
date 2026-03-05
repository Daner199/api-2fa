<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'foto_persona' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $this->validateBase64Image($value, $fail);
                },
            ],
        ];
    }

    // Función reutilizable para validar Base64
    private function validateBase64Image($value, $fail): void
    {
        // Acepta con o sin prefijo "data:image/png;base64,"
        if (preg_match('/^data:image\/(\w+);base64,/', $value)) {
            $imageData = substr($value, strpos($value, ',') + 1);
        } else {
            $imageData = $value;
        }

        // Intentar decodificar
        $decoded = base64_decode($imageData, true);
        if ($decoded === false) {
            $fail('El campo foto_persona no es un Base64 válido.');
            return;
        }

        // Verificar que los bytes correspondan a una imagen real
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($decoded);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            $fail('El Base64 no corresponde a una imagen válida (jpeg, png, gif, webp).');
        }
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'El nombre del servicio es obligatorio.',
            'foto_persona.required' => 'La foto en Base64 es obligatoria.',
        ];
    }
}