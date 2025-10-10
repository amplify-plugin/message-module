<?php

namespace Amplify\System\Message\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'as_customer' => 'required|boolean',
            'msg' => 'required_without:attachment|nullable|min:1',
            'attachment' => 'required_without:msg|max:1000',
        ];

        if ($this->method() == 'POST') {
            $rules['msg_to'] = 'required|integer';
            $rules['user_type'] = 'nullable|in:user,contact';
        }

        return $rules;
    }
}
