<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>決済完了</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .message {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message h1 {
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .message p {
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="message">
        <h1>決済が完了しました</h1>
        <p>このウィンドウは自動的に閉じられます...</p>
    </div>
    <script>
        // 親ウィンドウにメッセージを送信
        if (window.opener) {
            window.opener.postMessage({
                type: 'stripe-checkout-complete',
                redirectUrl: '{{ $redirectUrl }}'
            }, '*');
            
            // 少し待ってからウィンドウを閉じる
            setTimeout(function() {
                window.close();
            }, 2000);
        } else {
            // ポップアップでない場合は通常のリダイレクト
            window.location.href = '{{ $redirectUrl }}';
        }
    </script>
</body>
</html>

