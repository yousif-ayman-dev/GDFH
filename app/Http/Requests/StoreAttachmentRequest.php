<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:2048'],
            'visibility' => ['sometimes', 'required', 'string', Rule::in(['private', 'project', 'public'])],
        ];
    }
}
