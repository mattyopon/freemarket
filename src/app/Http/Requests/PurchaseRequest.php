<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'payment_method' => ['required', 'string', 'in:コンビニ払い,カード支払い'],
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
            'payment_method.required' => '支払い方法を選択してください',
            'payment_method.in' => '選択された支払い方法が無効です',
        ];
    }

    /**
     * バリデーション後の処理
     * 配送先（プロフィール情報の住所）が設定されているか確認
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if ($user && (!$user->postal_code || !$user->address)) {
                $validator->errors()->add('shipping_address', '配送先住所を設定してください。プロフィール編集画面から設定できます。');
            }
        });
    }
}

