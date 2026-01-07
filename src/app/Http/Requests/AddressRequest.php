<?php

namespace App\Http\Requests;

use App\Constants\ValidationConstants;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
     * バリデーションルール
     *
     * @return array
     */
    public function rules()
    {
        return [
            'postal_code' => ['required', 'string', 'regex:/^\d{3}-\d{4}$/', 'max:' . ValidationConstants::MAX_POSTAL_CODE_LENGTH],
            'address' => ['required', 'string', 'max:' . ValidationConstants::MAX_ADDRESS_LENGTH],
            'building_name' => ['nullable', 'string', 'max:' . ValidationConstants::MAX_BUILDING_NAME_LENGTH],
        ];
    }

    /**
     * カスタムエラーメッセージ
     *
     * @return array
     */
    public function messages()
    {
        return [
            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.regex' => '郵便番号はハイフンありの8文字（例：123-4567）で入力してください',
            'postal_code.max' => '郵便番号は' . ValidationConstants::MAX_POSTAL_CODE_LENGTH . '文字以内で入力してください',
            'address.required' => '住所を入力してください',
            'address.max' => '住所は' . ValidationConstants::MAX_ADDRESS_LENGTH . '文字以内で入力してください',
            'building_name.max' => '建物名は' . ValidationConstants::MAX_BUILDING_NAME_LENGTH . '文字以内で入力してください',
        ];
    }
}

