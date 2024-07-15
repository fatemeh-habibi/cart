<?php

namespace App\Http\Requests;

use App\Rules\ReCaptcha;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'username' => 'required|string|max:255',
            'password' => 'required|min:6',
            // 'g_recaptcha_response' => ['required', new ReCaptcha],  //when OTP login => it will be coment
            // 'code' => 'required|string' //when OTP login
        ];
    }
}
