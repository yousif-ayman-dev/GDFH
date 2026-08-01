<?php

namespace App\Http\Requests;

use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTeamInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $team = $this->route('team');
        if (! ($team instanceof Team)) {
            return false;
        }

        return $this->user()?->can('create', [TeamInvitation::class, $team]) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invitee_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'in:owner,admin,member,viewer'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $inviteeId = (int) $this->input('invitee_id');
            $team = $this->route('team');

            if (! $inviteeId || ! ($team instanceof Team)) {
                return;
            }

            // 1. Inviter cannot invite himself
            if ($inviteeId === (int) $this->user()?->id) {
                $validator->errors()->add('invitee_id', 'لا يمكنك دعوة نفسك لفرقة العمل.');
            }

            // 2. Invitee cannot already belong to the team
            if ($team->memberships()->where('user_id', $inviteeId)->where('status', 'active')->exists()) {
                $validator->errors()->add('invitee_id', 'هذا المستخدم عضو بالفعل في فرقة العمل.');
            }

            // 3. No duplicate pending invitation
            if (TeamInvitation::hasPendingInvitation($team->id, $inviteeId)) {
                $validator->errors()->add('invitee_id', 'توجد دعوة معلقة بالفعل لهذا المستخدم.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invitee_id.required' => 'يرجى اختيار المستخدم المراد دعوته.',
            'invitee_id.exists' => 'المستخدم المحدد غير موجود.',
            'role.in' => 'الدور المحدد غير صالح.',
        ];
    }
}
