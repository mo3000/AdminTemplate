<?php

namespace App\Http\Requests\admin;

use App\Admin;
use Illuminate\Foundation\Http\FormRequest;

class ModifyPassword extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|string|min:6|max:16',
            'newPassword' => 'required|string|min:6|max:16'
        ];
    }
}
