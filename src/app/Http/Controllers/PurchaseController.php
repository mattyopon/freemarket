<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\Purchase;
use App\Services\ShippingAddressService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    private $shippingAddressService;
    private $stripePaymentService;

    public function __construct(ShippingAddressService $shippingAddressService, StripePaymentService $stripePaymentService)
    {
        $this->shippingAddressService = $shippingAddressService;
        $this->stripePaymentService = $stripePaymentService;
    }

    /**
     * 購入可能かどうかを検証する
     *
     * @param  \App\Models\Item  $item
     * @param  \App\Models\User  $user
     * @return void
     */
    private function validatePurchaseAvailability(Item $item, $user)
    {
        if (!$user) {
            abort(403, 'ログインが必要です');
        }

        if ($item->user_id === $user->id) {
            abort(403, '自分の商品は購入できません');
        }

        if (!$item->is_published) {
            abort(404);
        }

        if ($item->isSold()) {
            abort(403, 'この商品は既に売れています');
        }
    }

    /**
     * 購入手続き画面を表示
     *
     * @param  int  $item
     * @return \Illuminate\View\View
     */
    public function create($item)
    {
        $item = Item::with(['user', 'categories'])->findOrFail($item);
        $user = Auth::user();

        $this->validatePurchaseAvailability($item, $user);

        // 保存されたセッションIDを取得
        $sessionId = session('stripe_checkout_session_id_' . $item->id);
        
        // セッションIDがある場合、決済状態を確認
        $paymentStatus = null;
        if ($sessionId) {
            try {
                $stripeSecretKey = env('STRIPE_SECRET_KEY');
                if ($stripeSecretKey && $stripeSecretKey !== 'sk_test_placeholder') {
                    \Stripe\Stripe::setApiKey($stripeSecretKey);
                    $session = \Stripe\Checkout\Session::retrieve($sessionId);
                    $paymentStatus = $session->payment_status; // 'paid', 'unpaid', 'no_payment_required'
                }
            } catch (\Exception $e) {
                \Log::error('Stripe session check error: ' . $e->getMessage());
            }
        }

        // このアイテム用の配送先住所をセッションから取得（なければユーザーの住所を使用）
        $shippingAddress = session('shipping_address_' . $item->id);
        if (!$shippingAddress) {
            // 初回アクセス時は、ユーザーの住所をセッションに保存
            $shippingAddress = $this->shippingAddressService->build($user);
            if ($shippingAddress) {
                session(['shipping_address_' . $item->id => $shippingAddress]);
            }
        }

        return view('purchase.create', compact('item', 'user', 'sessionId', 'paymentStatus', 'shippingAddress'));
    }

    /**
     * 購入を確定
     *
     * @param  \App\Http\Requests\PurchaseRequest  $request
     * @param  int  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PurchaseRequest $request, $item)
    {
        $item = Item::findOrFail($item);
        $user = Auth::user();

        $this->validatePurchaseAvailability($item, $user);

        // コンビニ支払いとカード支払いの両方でStripe決済画面に接続
        if (in_array($request->payment_method, ['コンビニ払い', 'カード支払い'])) {
            try {
                $checkoutData = $this->stripePaymentService->getCheckoutUrl($item, $user, $request->payment_method);
                $checkoutUrl = $checkoutData['url'];
                $sessionId = $checkoutData['session_id'];
                
                // セッションIDをセッションに保存（後で確認できるように）
                if ($sessionId) {
                    session(['stripe_checkout_session_id_' . $item->id => $sessionId]);
                }
                
                // AJAXリクエストの場合はJSONでURLを返す
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['checkout_url' => $checkoutUrl, 'session_id' => $sessionId]);
                }
                
                return redirect($checkoutUrl);
            } catch (\Exception $e) {
                // AJAXリクエストの場合はJSONでエラーを返す
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => $e->getMessage()], 400);
                }
                
                return redirect()->back()
                    ->withErrors(['payment_method' => $e->getMessage()])
                    ->withInput();
            }
        }

        return $this->processPurchase($item, $user, $request->payment_method);
    }

    /**
     * 購入完了画面を表示
     *
     * @param  int  $item
     * @return \Illuminate\View\View
     */
    public function complete($item)
    {
        $item = Item::findOrFail($item);
        $user = Auth::user();
        return view('purchase.complete', compact('item', 'user'));
    }

    /**
     * 購入手続き中の住所変更画面を表示
     *
     * @param  int  $item
     * @return \Illuminate\View\View
     */
    public function editAddress($item)
    {
        $item = Item::findOrFail($item);
        $user = Auth::user();

        $this->validatePurchaseAvailability($item, $user);

        return view('purchase.edit-address', compact('item', 'user'));
    }

    /**
     * 購入手続き中の住所を更新
     *
     * @param  \App\Http\Requests\AddressRequest  $request
     * @param  int  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAddress(AddressRequest $request, $item)
    {
        $item = Item::findOrFail($item);
        $user = Auth::user();

        $this->validatePurchaseAvailability($item, $user);

        // ユーザーの住所も更新（プロフィール情報として）
        $user->update([
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building_name' => $request->building_name,
        ]);

        // このアイテム用の配送先住所をセッションに保存（各アイテムに紐づける）
        $shippingAddress = $this->shippingAddressService->build($user);
        session(['shipping_address_' . $item->id => $shippingAddress]);

        return redirect()->route('purchase.create', ['item' => $item->id])
            ->with('status', '変更しました');
    }

    /**
     * 購入処理を実行
     *
     * @param  \App\Models\Item  $item
     * @param  \App\Models\User  $user
     * @param  string  $paymentMethod
     * @return \Illuminate\Http\RedirectResponse
     */
    private function processPurchase($item, $user, $paymentMethod)
    {
        $item->update(['sold_at' => now()]);

        // このアイテム用の配送先住所をセッションから取得（なければユーザーの住所を使用）
        $shippingAddress = session('shipping_address_' . $item->id);
        if (!$shippingAddress) {
            $shippingAddress = $this->shippingAddressService->build($user);
        }

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => $paymentMethod,
            'shipping_address' => $shippingAddress,
            'purchased_at' => now(),
        ]);

        // 購入完了後、セッションから配送先住所を削除
        session()->forget('shipping_address_' . $item->id);

        return redirect()->route('purchase.complete', ['item' => $item->id]);
    }

    /**
     * Stripe決済成功時のコールバック
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $item
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function stripeSuccess(Request $request, $item)
    {
        $item = Item::findOrFail($item);
        $user = Auth::user();
        $this->stripePaymentService->processPayment($request, $item, $user);
        
        // ポップアップウィンドウから開かれた場合は親ウィンドウを更新
        if ($request->get('popup')) {
            return view('purchase.stripe-success-popup', [
                'redirectUrl' => route('purchase.complete', ['item' => $item->id])
            ]);
        }
        
        return redirect()->route('purchase.complete', ['item' => $item->id]);
    }
}

