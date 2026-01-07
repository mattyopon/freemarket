<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * プロフィール設定画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * プロフィール情報を更新
     *
     * @param  \App\Http\Requests\ProfileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->only(['name', 'postal_code', 'address', 'building_name']);

        // プロフィール画像のアップロード処理
        if ($request->hasFile('profile_image')) {
            try {
                // 既存の画像を削除
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    @unlink(Storage::disk('public')->path($user->profile_image));
                }

                // 新しい画像を保存（直接ファイル操作で権限エラーを回避）
                $file = $request->file('profile_image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $directory = storage_path('app/public/profile_images');
                
                // ディレクトリが存在しない場合は作成
                if (!is_dir($directory)) {
                    @mkdir($directory, 0755, true);
                }
                
                // ファイルを直接保存（権限エラーを回避）
                $filePath = $directory . '/' . $filename;
                if (@move_uploaded_file($file->getPathname(), $filePath)) {
                    $data['profile_image'] = 'profile_images/' . $filename;
                } else {
                    throw new \Exception('ファイルの保存に失敗しました');
                }
            } catch (\Exception $e) {
                // エラーが発生した場合は画像なしで続行
                \Log::error('Profile image upload error: ' . $e->getMessage());
                // エラーメッセージを返す
                return back()->withErrors(['profile_image' => '画像のアップロードに失敗しました: ' . $e->getMessage()]);
            }
        }

        $user->update($data);

        return redirect()->route('profile.edit')->with('status', '更新しました');
    }
}
