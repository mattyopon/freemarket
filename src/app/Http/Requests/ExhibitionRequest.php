<?php

namespace App\Http\Requests;

use App\Constants\ValidationConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExhibitionRequest extends FormRequest
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
            'item_image' => ['required', 'array', 'min:1'],
            'item_image.*' => ['image', 'mimes:jpeg,png', 'max:' . ValidationConstants::MAX_IMAGE_SIZE_KB],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['exists:categories,id'],
            'condition' => ['required', 'string', 'in:新品,未使用に近い,目立った傷や汚れなし,やや傷や汚れあり,傷や汚れあり,全体的に状態が悪い'],
            'name' => ['required', 'string', 'max:' . ValidationConstants::MAX_NAME_LENGTH],
            'brand_name' => ['nullable', 'string', 'max:' . ValidationConstants::MAX_NAME_LENGTH],
            'description' => ['required', 'string', 'max:' . ValidationConstants::MAX_DESCRIPTION_LENGTH],
            'price' => ['required', 'integer', 'min:0'],
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
            'item_image.required' => '商品画像を選択してください',
            'item_image.*.image' => '商品画像は画像ファイルを選択してください',
            'item_image.*.mimes' => '商品画像はjpeg、png形式のみ対応しています',
            'item_image.*.max' => '商品画像は' . (ValidationConstants::MAX_IMAGE_SIZE_KB / 1024) . 'MB以下にしてください',
            'categories.required' => 'カテゴリーを選択してください',
            'categories.*.exists' => '選択されたカテゴリーが無効です',
            'condition.required' => '商品の状態を選択してください',
            'condition.in' => '商品の状態が無効です',
            'name.required' => '商品名を入力してください',
            'name.max' => '商品名は' . ValidationConstants::MAX_NAME_LENGTH . '文字以内で入力してください',
            'brand_name.max' => 'ブランド名は' . ValidationConstants::MAX_NAME_LENGTH . '文字以内で入力してください',
            'description.required' => '商品の説明を入力してください',
            'description.max' => '商品の説明は' . ValidationConstants::MAX_DESCRIPTION_LENGTH . '文字以内で入力してください',
            'price.required' => '販売価格を入力してください',
            'price.integer' => '販売価格は数値で入力してください',
            'price.min' => '販売価格は0円以上で入力してください',
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

