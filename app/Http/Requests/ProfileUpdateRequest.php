<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'username' => [
                'nullable',
                'string',
                'alpha_dash',
                'max:50',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', function ($attribute, $value, $fail) {
                if (is_file($value) || $value instanceof \Illuminate\Http\UploadedFile) {
                    $validator = \Illuminate\Support\Facades\Validator::make(
                        [$attribute => $value],
                        [$attribute => 'image|mimes:jpg,jpeg,png,webp|max:2048']
                    );
                    if ($validator->fails()) {
                        $fail($validator->errors()->first($attribute));
                    }
                } else {
                    if (!is_string($value) || (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://'))) {
                        $fail('Avatar harus berupa gambar yang valid atau pilihan preset.');
                    }
                }
            }],
        ];
    }
}
