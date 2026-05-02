<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('kode_kantor') && $this->input('kode_kantor') === '') {
            $this->merge(['kode_kantor' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'kode_kantor' => ['nullable', 'string', 'size:3', Rule::exists('kode_kantor', 'kode')],
        ];
    }
}
