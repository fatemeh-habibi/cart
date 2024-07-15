<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        $function=trim($title[1]);

        $this->offsetUnset('_method');
        switch ($function) {
            case 'grid':
                    $this->rules = [];
                break;
            case 'store':
                   $this->rules = [
                        'name' => 'required',
                        'quantity' => 'required|numeric|gt:0',
                        'cost' => 'required|numeric|gt:0',
                        // 'url' => 'required|string|max:128|unique:products_lang',
                        // 'activated' => 'required',
                        // 'sku' => 'nullable|unique:products',
                        // 'description' => 'required',
                   ];
                break;
            case 'update':
                    $this->rules = [
                        'name' => 'required',
                        'quantity' => 'required|numeric|gt:0',
                        'cost' => 'required|numeric|gt:0',
                        // 'url' => ['required','string','max:128', Rule::unique('products_lang')->ignore($this->segment(5))],
                        // 'sku' => ['nullable','string','max:128', Rule::unique('products')->ignore($this->segment(5))],
                        // 'activated' => 'required',
                        // 'description' => 'required',
                    ];
                break;
            default:
                break;
        }

        return $this->rules;
    }
}
