<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripePaymentService
{
    /**
     * 配送先住所サービス
     *
     * @var \App\Services\ShippingAddressService
     */
    private $shippingAddressService;

    /**
     * コンストラクタ
     *
     * @param  \App\Services\ShippingAddressService  $shippingAddressService
     * @return void
     */
    public function __construct(ShippingAddressService $shippingAddressService)
    {
        $this->shippingAddressService = $shippingAddressService;
    }

    /**
     * Stripeの決済画面にリダイレクトするURLを取得
     *
     * @param  \App\Models\Item  $item
     * @param  \App\Models\User  $user
     * @param  string  $paymentMethod
     * @return array ['url' => string, 'session_id' => string]
     * @throws \Exception
     */
    public function getCheckoutUrl(Item $item, User $user, string $paymentMethod)
    {
        $stripeSecretKey = env('STRIPE_SECRET_KEY');
        
        if (!$stripeSecretKey || $stripeSecretKey === 'sk_test_placeholder') {
            throw new \Exception('Stripe APIキーが設定されていません。.envファイルにSTRIPE_SECRET_KEYを設定してください。');
        }

        Stripe::setApiKey($stripeSecretKey);

        // このアイテム用の配送先住所をセッションから取得（なければユーザーの住所を使用）
        $shippingAddress = session('shipping_address_' . $item->id);
        if (!$shippingAddress) {
            $shippingAddress = $this->shippingAddressService->build($user);
        }

        // 支払い方法に応じてpayment_method_typesを設定
        $paymentMethodTypes = [];
        if ($paymentMethod === 'コンビニ払い') {
            $paymentMethodTypes = ['konbini'];
        } elseif ($paymentMethod === 'カード支払い') {
            $paymentMethodTypes = ['card'];
        } else {
            // デフォルトでは両方をサポート
            $paymentMethodTypes = ['card', 'konbini'];
        }

        try {
            $session = Session::create([
                'payment_method_types' => $paymentMethodTypes,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => ['name' => $item->name],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.stripe.success', ['item' => $item->id]) . '?session_id={CHECKOUT_SESSION_ID}&popup=true',
                'cancel_url' => route('purchase.create', ['item' => $item->id]) . '?canceled=true',
                'metadata' => [
                    'item_id' => $item->id,
                    'user_id' => $user->id,
                    'shipping_address' => $shippingAddress,
                    'selected_payment_method' => $paymentMethod,
                ],
            ]);

            return [
                'url' => $session->url,
                'session_id' => $session->id,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe checkout session creation error: ' . $e->getMessage());
            throw new \Exception('Stripe決済セッションの作成に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * Stripe決済処理を実行
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item  $item
     * @param  \App\Models\User  $user
     * @return void
     */
    public function processPayment(Request $request, Item $item, User $user)
    {
        $stripeSecretKey = env('STRIPE_SECRET_KEY');
        
        if (!$stripeSecretKey || $stripeSecretKey === 'sk_test_placeholder') {
            \Log::error('Stripe APIキーが設定されていません');
            return;
        }

        Stripe::setApiKey($stripeSecretKey);

        $sessionId = $request->get('session_id');
        if (!$sessionId) {
            return;
        }

        try {
            $session = Session::retrieve($sessionId);
            $metadata = $session->metadata;
            $itemId = $metadata->item_id ?? $item->id;
            $userId = $metadata->user_id ?? $user->id;
            $shippingAddress = $metadata->shipping_address ?? '';
            
            // 実際に使用された支払い方法を取得
            $paymentMethod = 'カード支払い'; // デフォルト
            if (isset($metadata->selected_payment_method)) {
                $paymentMethod = $metadata->selected_payment_method;
            } elseif (isset($session->payment_method_types) && in_array('konbini', $session->payment_method_types)) {
                // セッションから支払い方法を判定
                if ($session->payment_method_types[0] === 'konbini') {
                    $paymentMethod = 'コンビニ払い';
                }
            }

            $existingPurchase = Purchase::where('item_id', $itemId)->first();
            if (!$existingPurchase) {
                $item->update(['sold_at' => now()]);

                Purchase::create([
                    'user_id' => $userId,
                    'item_id' => $itemId,
                    'payment_method' => $paymentMethod,
                    'shipping_address' => $shippingAddress,
                    'purchased_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Stripe session retrieval error: ' . $e->getMessage());
        }
    }
}

