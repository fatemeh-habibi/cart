<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            case 'store':
                   $this->rules = [
                    'name' => 'required|unique:roles|string|max:255',
                    'name_fa' => 'required|unique:roles|string|max:255',
                    'slug' => 'required|unique:roles|string|max:255'
                   ];
                break;
            case 'update':
                $this->rules = [
                    'name_fa' => ['required','string','max:255', Rule::unique('roles')->ignore($this->segment(5))],
                    'name' => ['nullable','sometimes','string','max:255', Rule::unique('roles')->ignore($this->segment(5))],
                    'slug' => ['nullable','sometimes','string','max:255', Rule::unique('roles')->ignore($this->segment(5))]
                    ];
                break;
            default:
                break;
        }

        return $this->rules;
    }
}
