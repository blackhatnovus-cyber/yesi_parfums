<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['name', 'username', 'email'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $normalized[$field] = $field === 'name'
                    ? trim($value)
                    : Str::lower(trim($value));
            }
        }

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ];
    }
}
