<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama'   => ['required','string','max:100'],
            'email'  => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'jenis_kendaraan'   => ['required', 'string', Rule::in(['motor','mobil'])],
            'no_polisi' => ['required','string','max:30'],
            'merek_kendaraan'  => ['nullable','string','max:50'],
        ];
    }
}
