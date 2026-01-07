<?php

namespace App\Http\Requests;

use App\Constants\ValidationConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png', 'max:' . ValidationConstants::MAX_IMAGE_SIZE_KB],
            'postal_code' => ['required', 'string', 'regex:/^\d{3}-\d{4}$/', 'max:' . ValidationConstants::MAX_POSTAL_CODE_LENGTH],
            'address' => ['required', 'string', 'max:' . ValidationConstants::MAX_ADDRESS_LENGTH],
            'building_name' => ['nullable', 'string', 'max:' . ValidationConstants::MAX_BUILDING_NAME_LENGTH],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'ユーザー名を入力してください',
            'name.max' => 'ユーザー名は20文字以内で入力してください',
            'profile_image.image' => 'プロフィール画像は画像ファイルを選択してください',
            'profile_image.mimes' => 'プロフィール画像はjpeg、png形式のみ対応しています',
            'profile_image.max' => 'プロフィール画像は' . (ValidationConstants::MAX_IMAGE_SIZE_KB / 1024) . 'MB以下にしてください',
            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.regex' => '郵便番号はハイフンありの8文字（例：123-4567）で入力してください',
            'postal_code.max' => '郵便番号は' . ValidationConstants::MAX_POSTAL_CODE_LENGTH . '文字以内で入力してください',
            'address.required' => '住所を入力してください',
            'address.max' => '住所は' . ValidationConstants::MAX_ADDRESS_LENGTH . '文字以内で入力してください',
            'building_name.max' => '建物名は' . ValidationConstants::MAX_BUILDING_NAME_LENGTH . '文字以内で入力してください',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}

