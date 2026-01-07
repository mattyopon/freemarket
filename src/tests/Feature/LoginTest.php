<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     *
     * @return void
     */
    public function test_login_validation_email_required()
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
        $response->assertRedirect();
    }

    /**
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     *
     * @return void
     */
    public function test_login_validation_password_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
        $response->assertRedirect();
    }

    /**
     * 入力情報が間違っている場合、バリデーションメッセージが表示される
     *
     * @return void
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['login']);
        $response->assertSessionHasErrors(['login' => 'ログイン情報が登録されていません']);
        $response->assertRedirect();
    }

    /**
     * 正しい情報が入力された場合、ログイン処理が実行される
     *
     * @return void
     */
    public function test_login_succeeds_with_valid_credentials()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * ログインページが表示される
     *
     * @return void
     */
    public function test_login_page_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * メールアドレスの形式が正しくない場合、バリデーションメッセージが表示される
     *
     * @return void
     */
    public function test_login_validation_email_format()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'メールアドレスはメール形式で入力してください']);
        $response->assertRedirect();
    }
}

