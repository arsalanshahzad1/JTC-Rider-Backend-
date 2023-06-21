<?php

namespace App\Http\Requests\AddonItems;

use App\Models\AddonItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddonItemRequest extends FormRequest
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
        return AddonItem::$rules;

    }
}
