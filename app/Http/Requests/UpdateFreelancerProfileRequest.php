<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFreelancerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'hourly_rate' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'availability' => ['required', 'string', 'in:available,busy,offline'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'المسمى الوظيفي مطلوب.',
            'hourly_rate.required' => 'سعر الساعة مطلوب.',
            'hourly_rate.min' => 'سعر الساعة يجب أن لا يقل عن 0.',
            'availability.in' => 'حالة التوفر غير صالحة.',
        ];
    }
}
