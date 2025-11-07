# coachtechフリマ

アイテムの出品・購入を行うためのWebアプリケーションです。
ユーザーはログイン後に商品を出品したり、他ユーザーの商品を購入・いいね・コメントすることができます。

---

## アプリケーション概要
- サービス名：**coachtechフリマ**
- サービス概要：出品・購入・いいね機能を備えたフリマアプリ
- 制作目的：LaravelおよびDocker環境でのWebアプリ開発実践
- 開発目標：初年度のユーザー数1,000人達成を想定
- 対象ユーザー：10〜30代の社会人
- 使用環境：PC（Chrome / Firefox / Safari 最新版対応）

---

## 主な機能
- ユーザー登録 / ログイン / ログアウト（Laravel Fortify使用）
- 商品一覧表示（おすすめ / マイリスト切替）
- 商品詳細表示・コメント投稿
- 商品出品（画像アップロード対応）
- いいね登録・解除機能
- 購入機能（住所変更・支払い方法選択）
- マイページ（購入履歴 / 出品履歴 / プロフィール編集）

---

## 環境構築

### Dockerビルド
1. リポジトリのクローン
    ```bash
    git clone https://github.com/sametyan9999/laravel-furima.git
    cd laravel-furima
    ```

2. コンテナをビルド・起動
    ```bash
    docker compose up -d --build
    ```
※ MySQL が OS によって起動しない場合があるので、それぞれのPCに合わせて docker-compose.yml を編集してください。

### Laravel環境構築
1. PHPコンテナに入る
    ```bash
    docker compose exec php bash
    ```

2. 依存関係をインストール
    ```bash
    composer install
    ```

3. .env ファイルを作成
    ```bash
    cp .env.example .env
    ```
> ※ `.env` ファイルはプロジェクト直下（`laravel-furima/.env`）に作成してください。

4. `.env` の設定を修正（Docker用設定＋Stripe・MailHog設定）
```
# ======================
# 基本設定
# ======================
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# ======================
# Database（Docker用）
# ======================
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

# ======================
# ファイルアップロード設定
# ======================
FILESYSTEM_DRIVER=public

# ======================
# メール設定（MailHog）
# ======================
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="COACHTECH FLEA"

# ======================
# その他
# ======================
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ======================
# Stripe設定（各自のキーを設定してください）
# ======================
STRIPE_KEY=pk_test_******************************
STRIPE_SECRET=sk_test_******************************
```
---

**補足**
> - `.env` は開発者ごとに設定が異なります。クローン後は上記のように各自で設定してください。
> - `.env` は `.gitignore` により GitHub へアップロードされません。
> - Stripeキーは、Stripeダッシュボード → 開発者 → APIキー → テストモード から取得可能です。
> - MailHog は開発用のメールキャッチャーです。送信結果は [http://localhost:8025](http://localhost:8025) で確認できます。

---

**テストカード情報**
> - カード番号：`4000003920000003`
> - 有効期限：任意の未来日（例：12/30）
> - CVC：任意の3桁（例：123）
> - この番号を使うと実際に課金は発生せず、テスト決済が行えます。
> - 詳細は [Stripe公式ドキュメント](https://stripe.com/docs/testing#international-cards) を参照してください。

---

5. アプリケーションキーを生成
    ```bash
    php artisan key:generate
    ```

6. マイグレーションを実行
    ```bash
    php artisan migrate
    ```

7. シーディングを実行
    ```bash
    php artisan db:seed
    ```
### Seeder内容
初期データには以下が含まれます：
- CategoriesSeeder：カテゴリ14件
- ItemsSeeder：デフォルト商品10件
- ItemsCategorySeeder：商品とカテゴリの紐付け
---

8. ストレージリンク作成（画像アップロード対応）
```
php artisan storage:link
```
storage/app/public に保存された商品・プロフィール画像を
http://localhost/storage/... で閲覧できるようにします。

---

## テスト実行方法
コンテナ内で以下を実行してテストを実施します。

```bash
docker compose exec php bash
php artisan test
```

---

## 決済処理フロー（Stripe）
本アプリでは、Stripeを利用した カード支払い と コンビニ支払い の2種類に対応しています。

 ### カード支払い
 - 支払い完了後、即座に「購入完了」画面へ遷移します。
 - 商品は即時に `sold` 状態へ更新され、一覧でも「Sold」バッジが表示されます。

 ### コンビニ支払い
 - Stripeが発行する支払い番号を使って、店頭（ローソン・ファミリーマートなど）で支払いを行います。
 - 入金完了後、Stripeの Webhook 通知によってシステムが商品を `sold` 状態へ更新します。
 - そのため、支払い直後はステータスがすぐには反映されません（非同期処理）。

---

## Webhook設定（開発環境）

開発環境では、Stripe CLIを使ってWebhookをローカルに転送します。
```bash
stripe listen --forward-to http://localhost/stripe/webhook
```
実行後に表示されるメッセージ：
```
Ready! Your webhook signing secret is whsec_xxxxxxxxxxxxx
```
この値を `.env` に設定します：
```
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```
設定後はキャッシュをクリア：
```bash
php artisan config:clear
```

---

## Webhookテスト（コンビニ入金の再現）
別ターミナルで以下を実行し、入金完了イベントを再現します
```bash
stripe trigger payment_intent.succeeded \
  --override payment_intent:metadata.item_id=1 \
  --override payment_intent:metadata.user_id=5
```
- `item_id`：販売中商品のID
- `user_id`：購入者ユーザーのID
※実際のDB値に置き換えてください。

実行後、DBまたは一覧画面で `sold` に更新されていることを確認できます。

💡 **補足**
- Stripe CLIを終了すると Webhook の受信も停止します。再開時は新しい Secret を `.env` に再設定してください。
- 本番環境では Stripe ダッシュボード上で正式な Webhook URL を登録します。
- Webhook リスニング中は Ctrl + C で停止可能です。


---

## 環境情報（Docker構成）
| サービス | バージョン / イメージ | 備考 |
|-----------|----------------------|------|
| nginx | nginx:1.27-alpine | ARM対応 |
| php | php:8.1-fpm | Composer導入済 |
| mysql | mysql:8.4 | Docker永続化設定済 |
| phpMyAdmin | phpmyadmin/phpmyadmin:latest | 管理用GUI |
| mailhog | mailhog/mailhog:v1.0.1 | 開発用メール確認ツール（http://localhost:8025） |

---

## 使用技術(実行環境)

| 分類 | 技術・ライブラリ |
|------|----------------|
| 言語 | PHP 8.1.x |
| フレームワーク | Laravel 8.75+ |
| 認証 | Laravel Fortify / Laravel Sanctum |
| データベース | MySQL 8.4 |
| 決済 | Stripe PHP 18.x |
| フロントエンド | Blade / jQuery 3.7.1 |
| インフラ | Docker / Docker Compose |
| テスト | PHPUnit 9.5 |
| 管理ツール | phpMyAdmin / MailHog |
| バージョン管理 | Git / GitHub |

> **補足**
> - メールは MailHog に送信されます（開発用URL: [http://localhost:8025](http://localhost:8025)）。
> - Stripe は **テストキー** で動作します。

---

## ER図
![alt text](ER.png)
---

## アプリケーションURL
| 環境 | URL |
|------|------|
| 開発環境 | [http://localhost](http://localhost) |
| phpMyAdmin | [http://localhost:8080](http://localhost:8080) |
| MailHog | [http://localhost:8025](http://localhost:8025) |
| ユーザー登録 | [http://localhost/register](http://localhost/register) |
| ログイン | [http://localhost/login](http://localhost/login) |

© 2025 coachtechフリマ


修正聞いたかな