<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginValidationMessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、「メールアドレスを入力してください」が表示される
     * （メール形式のバリデーションでは表示されないことを確認）
     *
     * @return void
     */
    public function test_email_required_shows_correct_message()
    {
        $response = $this->post('/login', [
            'email' => '', // 空文字
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        // requiredルールのメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
        // emailルールのメッセージは表示されない
        $this->assertNotContains('メールアドレスはメール形式で入力してください', session('errors')->get('email'));
    }

    /**
     * メールアドレスが入力されているが形式が正しくない場合、
     * 「メールアドレスはメール形式で入力してください」が表示される
     * （requiredのメッセージは表示されないことを確認）
     *
     * @return void
     */
    public function test_email_format_shows_correct_message()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email', // 形式が正しくないが、入力されている
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        // emailルールのメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスはメール形式で入力してください']);
        // requiredルールのメッセージは表示されない
        $this->assertNotContains('メールアドレスを入力してください', session('errors')->get('email'));
    }

    /**
     * メールアドレスがnullの場合、「メールアドレスを入力してください」が表示される
     *
     * @return void
     */
    public function test_email_null_shows_required_message()
    {
        $response = $this->post('/login', [
            // emailフィールドを送信しない
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }
}

