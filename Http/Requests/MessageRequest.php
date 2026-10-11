<?php

namespace Amplify\System\Message\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public const ATTACHMENT_MIMES = 'jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,rtf,csv,xls,xlsx,ppt,pptx';

    public static function acceptAttribute(): string
    {
        return '.'.str_replace(',', ',.', self::ATTACHMENT_MIMES);
    }

    /**
     * @return array<int, string>
     */
    public static function attachmentRules(): array
    {
        return ['required_without:msg', 'file', 'mimes:'.self::ATTACHMENT_MIMES, 'max:1000'];
    }

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
            'attachment' => self::attachmentRules(),
        ];

        if ($this->method() == 'POST') {
            $rules['msg_to'] = 'required|integer';
            $rules['user_type'] = 'nullable|in:user,contact';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'attachment.mimes' => 'Attach an image, PDF, Word, Excel, PowerPoint, CSV, or text file.',
        ];
    }
}
