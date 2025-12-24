<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role==='user';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'no_order'   => ['required','numeric','unique:order,no_order'],
            'pickup_address'  => ['nullable','string'],
            'pickup_lat' => ['required','numeric:-90,90'],
            "pickup_lng" => ['required', 'numeric:-180,180'],
            'destination_address'   => ['nullable','string'],
            "destination_lat" => ['required', 'numeric:-90,90'],
            "destination_lng" => ['required', 'numeric:-180,180'],
            "status" => ['required', Rule::in(['pending', 'accepted', 'on_delivery', 'done', 'cancel'])],
            "user_id" => ['required','exists:users,id'],
        ];
    }

    public function prepareForValidation() : self
    {
        return $this->merge([
            "user_id" => auth()->user()->id,
            "status" => "pending",
            "no_order" => time(),
        ]);
    }
}
