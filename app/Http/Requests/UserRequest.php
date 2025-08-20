<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [
            'name' => $userId ? 'sometimes|string|max:40' : 'required|string|max:40',
            'email' => $userId
                ? 'sometimes|string|email|max:255|unique:users,email,' . $userId
                : 'required|string|email|max:255|unique:users,email,' . $userId,
            'password' => $userId ? 'sometimes|string|min:8' : 'required|string|min:8',
            'roles' => 'sometimes|array',
            'roles.*' => 'integer|exists:roles,id'
        ];
    }
}
