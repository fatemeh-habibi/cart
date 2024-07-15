<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * @var mixed|string[]
     */
    private $rules;

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
     * @return array
     */
    public function rules(): array
    {
        $route_name = $this->route()->getName();
        $title= explode('_', $route_name,2);
        $function = trim($title[1]);

        $this->offsetUnset('_method');
        switch ($function) {
            case 'permission_store':
                    $this->rules = [
                        'user_id' => 'required|integer|exists:permissions,id',
                        'role_id' => 'required_without:permissions|integer|exists:permissions,id',
                        'permissions' =>  'required_without:role_id|array',
                        'permissions.*' => 'exists:permissions,id'
                    ];
                break;
            case 'store':
                   $this->rules = [
                    'mobile' => 'required|regex:/[0]{1}[0-9]{10}/|unique:users',
                    'email' => 'nullable|sometimes|email|unique:users',
                    'password_confirmation' => 'required|string|min:6',
                    'password' => 'required|string|confirmed|min:6',
                    'username' => 'required|unique:users|string|max:255',
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'image' => 'nullable',
                    'activated' => 'boolean'
                   ];
                break;
            case 'update':
                $this->rules = [
                    'change_password' => 'boolean',
                    'password_confirmation' => 'exclude_if:change_password,false|required|string|min:6',
                    'password' => 'exclude_if:change_password,false|required|string|confirmed|min:6',
                    'username' => ['nullable','sometimes','string','max:255', Rule::unique('users')->ignore($this->segment(5))],
                    'mobile' => ['required','regex:/[0]{1}[0-9]{10}/', Rule::unique('users')->ignore($this->segment(5))],
                    'email' => ['nullable','sometimes','email', Rule::unique('users')->ignore($this->segment(5))],
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'image' => 'nullable',
                    'activated' => 'boolean',
                    ];
                break;
            default:
                break;
        }

        return $this->rules;
    }
}
