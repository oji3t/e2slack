## 概要

例外発生時にSlackに通知するライブラリです．多くのphp開発において利用することが可能です．
最低限の拡張のみを許容し，インストールと幾つかの設定のみですぐに利用できます．機能の追加要望やプルリクエスト，バグ報告は随時受け付けています．
例外発生時に即座に通知を受信できることを是として開発しているため，完全なログは別途記録を行ってください．

## 利用開始

### Slackの設定

Slackのwebhookを利用しているため，そのための設定が必要です．これはこのライブラリのインストールよりも簡単です．
権限のあるアカウントでログインした状態で[ここ](https://my.slack.com/services/new/incoming-webhook/)にアクセスするとincoming-webhookのインテグレーションを作成でき，遷移先のページにてendpointが確認できるのでそれを控えておいてください．
全てが上手くいっていれば，`https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXX`のようなURLになっているはずです．
権限のないアカウントの場合は権限がありませんというようなエラーメッセージが英語で出てくるので権限のある人に相談してください．

### ライブラリのインストール

composerによるインストールのみをサポートしています．以下のコマンドでインストールしてください．

```shell
$ composer require oji3t/e2slack
```

以上で準備は完了です．

## 利用方法

### 基本的な利用

phpの例外インスタンスと，slackの設定を引数に渡してください．

```php
use ExceptionToSlack\Notification;
use FooException;

try {
    throw new FooException;
} catch (FooException $e) {
    $config = ['endpoint' => YOUR_WEBHOOK_ENDPOINT];
    $notification = new Notification($e, $config);
    $notification->send();
}
```

autoloadが有効であれば以下の様にも書くことができます．これは，上記のコードと全く同じ動作をします．
もちろん，e2slack関数を別途定義していた場合はそちらが優先されるため，あなたのすでに作成されたアプリケーションを破壊することはありません．
ただし，その状態でこのパッケージの提供するe2slack関数を利用するとPHPのエラーを引き起こす原因となるので注意してください．

```php
use FooException;

try {
    throw new FooException;
} catch (FooException $e) {
    $config = ['endpoint' => YOUR_WEBHOOK_ENDPOINT];
    e2slack($e, $config);
}
```

送信する例外の種類によって送信するチャンネルを分けることができます．

### 設定

インストールして即利用可能であることを是としているため，あまり設定できることは多くありませんが，設定により以下の内容を変更できます．
**endpointなどは他人に知られるべき情報ではないので，[Dotenv](https://github.com/vlucas/phpdotenv)などの利用を推奨します．**

1. endpoint…設定したエンドポイントです．デフォルトは`null`です．
2. channel…メッセージを送信するSlackのチャンネルです．デフォルトは`'#general'`です．`'@user.name'`の様に指定することもできます．
3. username…メッセージを投稿するbotの名前です．デフォルトは`'Notification'`です．エラーの種類によって変えると便利かもしれません．
4. icon…メッセージを投稿するbotのアイコンです．画像ファイルのURLまたは[絵文字](http://www.webpagefx.com/tools/emoji-cheat-sheet/)を指定します．デフォルトは可愛らしい絵文字が指定されています．

上記の項目をキーとして第二引数に連想配列で指定してください．
また，インスタンスを作成した後で設定を上書きすることも可能です．

```php
use ExceptionToSlack\Notification;
use FooException;

try {
    throw new FooException;
} catch (FooException $e) {
    $config = ['endpoint' => YOUR_WEBHOOK_ENDPOINT];
    $notification = new Notification($e, $config);
    $notification->setUsername('Debug Bot');
    $notification->setChannel('@user.name');
    $notification->setIcon(':love_letter:');
    $notification->send();
}
```

また，多くのPHPフレームワークではコンフィグレーション機能やエラーハンドリング機能が付属しているため，それぞれにあわせてうまく利用してください．例えばLaravel5では以下の様にできます．

```ini
// .env
SLACK_ENDPOINT=https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXX
SLACK_CHANNEL=#a_project_channel
SLACK_USERNAME=Notification
SLACK_ICON=:love_letter:
```

```php
// config/services.php
return [
    // ~~~

    'e2slack' => [
        'endpoint' => env('SLACK_ENDPOINT'),
        'channel' => env('SLACK_CHANNEL'),
        'username' => env('SLACK_USERNAME'),
        'icon' => env('SLACK_ICON'),
    ],
];
```

```php
// app/Exceptions/Handler.php
namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use App\Exceptions\FooException;
use ExceptionToSlack\Notification;

class Handler extends ExceptionHandler
{
    // ~~~

    public function render($request, Exception $e)
    {
        if ($e instanceof FooException) {
            $notification = new Notification($e, config('services.e2slack'));
            $notification->send();
        }

        return parent::render($request, $e);
    }
}
```

### メッセージ送信エラーのハンドリング

メッセージが送信された場合，sendメソッド並びにe2slack関数は現在のインスタンスを返却します．これは，メソッドチェーンでメッセージを別チャンネルに再送信したい場合などを想定した実装です．
送信になんらかの原因で失敗した場合は，上記のいずれもfalseを返します．送信の失敗を取得する場合は`$notification===false`などとしてください．

## 注意点

endpointなどの情報は流出しないように丁重に管理してください．
パッケージの処理時に内部でエラーが発生した場合に無限ループを発生させる危険性があるため，PHPのException基底クラスにハンドリングしてメッセージを送信するというような実装は行ってはいけません．
上記を含め，当パッケージの利用により発生したいかなる損害も開発者であるTakara Ojiは負いかねますのでご了承ください．
