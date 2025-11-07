<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | このファイルでは、Stripe・MailHog などの外部サービスとの連携設定を管理します。
    | すべての値は .env から読み込まれるため、認証情報は安全に保たれます。
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe設定
    |--------------------------------------------------------------------------
    | テスト／本番環境を問わず Stripe のキーを .env から読み込みます。
    | Webhook（コンビニ決済通知など）も設定可能です。
    */

    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'), // ← コンビニ決済対応で使用
    ],

    /*
    |--------------------------------------------------------------------------
    | MailHog設定（開発環境用）
    |--------------------------------------------------------------------------
    | ローカル環境で送信されるメールをブラウザ上で確認できます。
    | URL: http://localhost:8025
    */

    'mailhog' => [
        'url' => env('MAILHOG_URL', 'http://localhost:8025'),
    ],

];