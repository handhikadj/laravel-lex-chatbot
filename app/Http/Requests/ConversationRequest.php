<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class ConversationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $this->extendValidation();

        return [
            'message' => [
                'contains_url',
                'contains_html',
            ],
        ];
    }

    public function extendValidation()
    {
        $message = 'Message contains characters or words that are not allowed';

        Validator::extend(
            'contains_url',
            fn ($attr, $value) => !preg_match('/\b(https?|ftp|file):\/\/[-A-Za-z0-9+&@#\\/%?=~_|!:,.;]*[-A-Za-z0-9+&@#\\/%=~_|]/i', $value),
            $message
        );

        Validator::extend(
            'contains_html',
            fn ($attr, $value) => $value == strip_tags($value),
            $message
        );
    }
}
