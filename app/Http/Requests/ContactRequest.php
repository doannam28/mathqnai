<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'content' => 'required|string',
            'title' => 'required|string',
        ];
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return [
            'email.required' => 'Tên không được để trống',
            'phone.required' => 'Số điện thoại không được để trống',
            'title.required' => 'Tiêu đề không được để trống',
            'content.required' => 'Tin nhắn không được để trống',
        ];
    }
}
