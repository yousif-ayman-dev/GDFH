<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:5', 'max:100000'],
            'delivery_days' => ['required', 'integer', 'min:1', 'max:365'],
            'category' => ['required', 'string', 'max:100'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الخدمة مطلوب.',
            'description.required' => 'وصف الخدمة مطلوب.',
            'description.min' => 'وصف الخدمة يجب أن لا يقل عن 20 حرفاً.',
            'price.required' => 'سعر الخدمة مطلوب.',
            'price.min' => 'سعر الخدمة يجب أن يكون 5 دولارات على الأقل.',
            'delivery_days.required' => 'مدة التسليم بالربط الأيامي مطلوبة.',
            'category.required' => 'تصنيف الخدمة مطلوب.',
            'cover_image.image' => 'صورة الغلاف يجب أن تكون ملف صورة صالح.',
            'cover_image.max' => 'حجم صورة الغلاف لا يجب أن يتجاوز 2 ميجابايت.',
        ];
    }
}
