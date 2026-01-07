<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemSellController;
use App\Http\Controllers\ItemManageController;
use App\Http\Controllers\ItemCommentController;
use App\Http\Controllers\ItemLikeController;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ログイン関連のルート
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// 会員登録関連のルート
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// メール認証関連のルート
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.resend');
});

// メール認証リンク（認証不要でアクセス可能）
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');

// プロフィール設定関連のルート（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

// 商品一覧関連のルート
Route::get('/', [ItemController::class, 'index'])->name('items.index');
// マイページ（デフォルトでおすすめタブ）
Route::get('/mypage', [ItemController::class, 'index'])->name('mypage');

// いいね関連のルート（認証不要）
Route::post('/items/{item}/like', [ItemLikeController::class, 'store'])->name('items.like');
Route::delete('/items/{item}/like', [ItemLikeController::class, 'destroy'])->name('items.unlike');

// 商品出品関連のルート（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/sell', [ItemSellController::class, 'create'])->name('items.sell');
    Route::post('/sell', [ItemSellController::class, 'store'])->name('items.sell.store');
    Route::get('/items/{item}/sell/complete', [ItemSellController::class, 'complete'])->name('items.sell.complete');
    
    // 商品管理関連のルート
    Route::get('/items/{item}/manage', [ItemManageController::class, 'edit'])->name('items.manage');
    Route::post('/items/{item}/manage', [ItemManageController::class, 'update'])->name('items.manage.update');
    
    // コメント関連のルート
    Route::post('/items/{item}/comments', [ItemCommentController::class, 'store'])->name('items.comments.store');
    
    // 購入関連のルート
            Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
            Route::post('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
            Route::get('/purchase/{item}', [PurchaseController::class, 'create'])->name('purchase.create');
            Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->name('purchase.store');
            Route::get('/purchase/{item}/stripe/success', [PurchaseController::class, 'stripeSuccess'])->name('purchase.stripe.success');
            Route::get('/purchase/{item}/complete', [PurchaseController::class, 'complete'])->name('purchase.complete');
});

// 商品詳細関連のルート（/items/sellより後に定義）
Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
