<?php

namespace App\Http\Requests\AddonsAddonItems;

use App\Models\AddonsAndAddonItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddonsAddonItemRequest extends FormRequest
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
        return AddonsAndAddonItem::$rules;

    }
}
