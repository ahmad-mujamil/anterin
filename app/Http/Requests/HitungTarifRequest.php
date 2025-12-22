<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HitungTarifRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'pickup_lat'   => ['required','numeric:-90,90'],
            'pickup_lng'  => ['required','numeric:-180,180'],
            'destination_lat' => ['required','numeric:-90,90'],
            "destination_lng" => ['required', 'numeric:-180,180'],
        ];
    }
}
