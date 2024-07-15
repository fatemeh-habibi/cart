<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
        $route_name = $this->route()->getName();
        $title      = explode('_', $route_name, 2);
        $function   = trim($title[1]);

        switch ($function) {
            case 'grid':
                $this->rules = [];
                break;
            case 'store':
                $this->rules = [
                    'first_name'     => 'required|string|max:128',
                    'last_name'      => 'required|string|max:128',
                    'mobile'      => 'required|unique:customers',
                    'image' => 'nullable',
                    'email' => 'nullable|unique:customers',
                    'activated' => 'required'
                ];
                break;
            case 'update':
                $this->rules = [
                    'email' => 'nullable|:roles',
                    'first_name'     => 'required|string|max:128',
                    'last_name'      => 'required|string|max:128',
                    'image' => 'nullable',
                    'mobile' => ['required','string', Rule::unique('customers')->ignore($this->segment(5))],
                    'activated' => 'required'
                ];
                break;
            case 'approve':
                $this->rules = [
                ];
                break;
            default:
                break;
        }

        return $this->rules;
    }
}
