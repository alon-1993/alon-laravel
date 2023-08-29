<?php

namespace App\Http\Requests;

use App\Enums\Admin\AdminStatus;
use App\Enums\Admin\TypeEnum;
use App\Rules\NumericLength;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('admin')?->getKey();
        return [
            'name' => [
                'required',
                Rule::unique('admins')->whereNull('deleted_at')->ignore($id),
            ],
            'email' => [
                'nullable',
                'email'
            ],
            'phone' => [
                'required',
                Rule::unique('admins')->whereNull('deleted_at')->ignore($id),
                'numeric',
                new NumericLength(11, '手机号')
            ],
            'role_ids' => 'required|array',
            'status' => [
                'required',
                Rule::in(AdminStatus::toArray())
            ],
        ];
    }

}
