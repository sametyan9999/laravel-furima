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
    ```env
    cp .env.example .env
    ```
> ※ 本リポジトリは `src/` 配下が Laravel のルートです。`.env` は `src/.env` に作成してください。

4. .env のDB設定を修正（Docker用に修正）
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

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

---

## テスト実行方法
コンテナ内で以下を実行してテストを実施します。

```bash
docker compose exec php bash
php artisan test
```

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

---

#### ④ MailHog / Stripe 注意書き

```md
> 💡 **補足**
> - メールは MailHog に送信されます（開発用URL: [http://localhost:8025](http://localhost:8025)）。
> - Stripe は **テストキー** で動作します。提出・運用時は本番キーに差し替えてください。
```
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