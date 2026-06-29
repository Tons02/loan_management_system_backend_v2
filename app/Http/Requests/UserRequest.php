<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [

            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',

            'first_name' => 'required|string|max:100',

            'middle_name' => 'nullable|string|max:100',

            'last_name' => 'required|string|max:100',

            'suffix' => 'nullable|string|max:20',

            'birthday' => 'required|date',

            'mobile_number' => 'nullable|string|max:20',

            'gender' => 'required|in:Male,Female',

            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users')->ignore($user),
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user),
            ],

            'role' => 'required|string|max:50',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'profile_picture.image' => 'The profile picture must be an image.',
            'profile_picture.mimes' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.',
            'profile_picture.max' => 'The profile picture must not exceed 10MB.',

            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email is already registered.',

            'gender.in' => 'Gender must be either Male or Female.',
        ];
    }
}
