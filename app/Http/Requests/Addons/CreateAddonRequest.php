<?php

namespace App\Http\Requests\Addons;

use App\Models\Addons;
use Illuminate\Foundation\Http\FormRequest;

class CreateAddonRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return Addons::$rules;
//        return [
//        'name' => 'required',
//        'slug' => 'required',
//        ];
    }
}
