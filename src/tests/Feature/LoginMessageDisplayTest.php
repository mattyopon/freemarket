<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginMessageDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、正しいメッセージが表示されることを確認
     *
     * @return void
     */
    public function test_email_required_shows_correct_message()
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        
        // セッションのエラーメッセージを取得
        $errors = session('errors');
        $emailErrors = $errors->get('email');
        
        // カスタムメッセージが表示されていることを確認
        $this->assertContains('メールアドレスを入力してください', $emailErrors);
        // デフォルトメッセージが表示されていないことを確認
        $this->assertNotContains('このフィールドを入力してください', $emailErrors);
    }

    /**
     * パスワードが未入力の場合、正しいメッセージが表示されることを確認
     *
     * @return void
     */
    public function test_password_required_shows_correct_message()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
        
        // セッションのエラーメッセージを取得
        $errors = session('errors');
        $passwordErrors = $errors->get('password');
        
        // カスタムメッセージが表示されていることを確認
        $this->assertContains('パスワードを入力してください', $passwordErrors);
        // デフォルトメッセージが表示されていないことを確認
        $this->assertNotContains('このフィールドを入力してください', $passwordErrors);
    }
}

