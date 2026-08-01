<?php

namespace App\Http\Requests;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('username') && filled($this->username)) {
            $rawUsername = (string) $this->username;
            $normalizedUsername = Str::lower(ltrim(trim($rawUsername), '@'));

            $user = User::query()
                ->whereRaw('LOWER(username) = ?', [$normalizedUsername])
                ->first();

            if ($user) {
                $this->merge([
                    'invitee_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string'],
            'invitee_id' => ['required_without:username', 'nullable', 'integer', 'exists:users,id'],
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
            $hasUsernameInput = $this->has('username') && filled($this->username);
            $inviteeId = (int) $this->input('invitee_id');
            $team = $this->route('team');

            if ($hasUsernameInput && ! $inviteeId) {
                $validator->errors()->add('username', 'اسم المستخدم هذا غير موجود في المنظومة.');
                return;
            }

            if (! $inviteeId || ! ($team instanceof Team)) {
                return;
            }

            $targetField = $hasUsernameInput ? 'username' : 'invitee_id';

            // 1. Inviter cannot invite himself
            if ($inviteeId === (int) $this->user()?->id) {
                $validator->errors()->add($targetField, 'لا يمكنك دعوة نفسك لفرقة العمل.');
            }

            // 2. Invitee cannot already belong to the team
            if ($team->memberships()->where('user_id', $inviteeId)->where('status', 'active')->exists()) {
                $validator->errors()->add($targetField, 'هذا المستخدم عضو بالفعل في فرقة العمل.');
            }

            // 3. No duplicate pending invitation
            if (TeamInvitation::hasPendingInvitation($team->id, $inviteeId)) {
                $validator->errors()->add($targetField, 'توجد دعوة معلقة بالفعل لهذا المستخدم.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required_without' => 'يرجى إدخال اسم المستخدم.',
            'invitee_id.required_without' => 'يرجى اختيار المستخدم المراد دعوته.',
            'invitee_id.exists' => 'المستخدم المحدد غير موجود.',
            'role.in' => 'الدور المحدد غير صالح.',
        ];
    }
}
