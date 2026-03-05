<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'foto_persona' => [
                'sometimes', // solo valida si viene en el request
                'string',
                function ($attribute, $value, $fail) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $value)) {
                        $imageData = substr($value, strpos($value, ',') + 1);
                    } else {
                        $imageData = $value;
                    }
                    $decoded = base64_decode($imageData, true);
                    if ($decoded === false) {
                        $fail('El campo foto_persona no es un Base64 válido.');
                        return;
                    }
                    $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($decoded);
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail('El Base64 no corresponde a una imagen válida.');
                    }
                },
            ],
        ];
    }
}