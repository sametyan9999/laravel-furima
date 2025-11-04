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
    docker-compose up -d --build
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

## 環境情報（Docker構成）
| サービス | バージョン / イメージ | 備考 |
|-----------|----------------------|------|
| nginx | nginx:1.27-alpine | ARM対応 |
| php | php:8.1-fpm | Composer導入済 |
| mysql | mysql:8.4 | Docker永続化設定済 |
| phpMyAdmin | phpmyadmin/phpmyadmin:latest | 管理用GUI |

---

## 使用技術(実行環境)

| 分類 | 技術・ライブラリ |
|------|----------------|
| 言語 | PHP 8.1 |
| フレームワーク | Laravel 10.x |
| データベース | MySQL 8.4 |
| インフラ | Docker / Docker Compose |
| 認証 | Laravel Fortify |
| フロントエンド | Blade, jQuery 3.7.1 |
| 管理ツール | phpMyAdmin |
| バージョン管理 | Git / GitHub |

---

## ER図
```mermaid
erDiagram
  USERS {
    BIGINT PK id
    VARCHAR(255) name
    VARCHAR(255) email  "UNIQUE"
    TIMESTAMP email_verified_at
    VARCHAR(255) password
    VARCHAR(100) remember_token
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  PROFILES {
    BIGINT PK id
    BIGINT FK user_id  "UNIQUE -> users.id"
    VARCHAR(255) avatar_path
    VARCHAR(8) postal_code
    VARCHAR(255) address_line1
    VARCHAR(255) address_line2
    VARCHAR(20) phone
    VARCHAR(255) bio
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  CATEGORIES {
    BIGINT PK id
    VARCHAR(100) name  "UNIQUE"
    BIGINT FK parent_id "-> categories.id (NULLABLE)"
    INT UNSIGNED sort
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  CONDITIONS {
    TINYINT UNSIGNED PK id
    VARCHAR(50) name  "UNIQUE"
  }

  ITEMS {
    BIGINT PK id
    BIGINT FK user_id       "-> users.id"
    BIGINT FK category_id   "-> categories.id  (代表カテゴリ)"
    TINYINT UNSIGNED FK condition_id "-> conditions.id"
    VARCHAR(120) name
    TEXT description
    VARCHAR(80)  brand
    VARCHAR(255) image
    INT UNSIGNED price
    ENUM('on_sale','sold','draft') status
    INT UNSIGNED likes_count     "DEFAULT 0"
    INT UNSIGNED comments_count  "DEFAULT 0"
    DATETIME sold_at
    TIMESTAMP deleted_at
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  LIKES {
    BIGINT PK id
    BIGINT FK user_id "-> users.id"
    BIGINT FK item_id "-> items.id"
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  COMMENTS {
    BIGINT PK id
    BIGINT FK item_id "-> items.id"
    BIGINT FK user_id "-> users.id"
    VARCHAR(255) body
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  PURCHASES {
    CHAR(36) PK id
    BIGINT FK user_id "-> users.id"
    BIGINT FK item_id "-> items.id"
    INT UNSIGNED amount "NOT NULL DEFAULT 0"
    ENUM('card','convenience') payment_method
    ENUM('pending','paid','failed','canceled') payment_status "DEFAULT 'paid'"
    VARCHAR(100) stripe_payment_intent_id
    DATETIME purchased_at
    VARCHAR(100) shipping_name
    VARCHAR(8)   shipping_postal_code
    VARCHAR(255) shipping_address1
    VARCHAR(255) shipping_address2
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  CATEGORY_ITEM {
    BIGINT FK category_id "PK part -> categories.id"
    BIGINT FK item_id     "PK part -> items.id"
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  %% リレーション（カーディナリティ）
  USERS     ||--|| PROFILES   : "1 : 1"
  USERS     ||--o{ ITEMS      : "1 : N (sells)"
  USERS     ||--o{ COMMENTS   : "1 : N (writes)"
  USERS     ||--o{ LIKES      : "1 : N (likes)"
  USERS     ||--o{ PURCHASES  : "1 : N (buys)"

  ITEMS     }o--|| CATEGORIES : "N : 1 (primary_category)"
  ITEMS     }o--o{ CATEGORIES : "N : M via category_item"
  CATEGORIES ||--o{ CATEGORIES: "1 : N (parent→children)"

  CONDITIONS ||--o{ ITEMS     : "1 : N (condition)"
  ITEMS     ||--o{ COMMENTS   : "1 : N (has)"
  ITEMS     ||--o{ LIKES      : "1 : N (liked by)"
  ITEMS     ||--o{ PURCHASES  : "1 : N (app rule=1件想定)"  %% DB制約ではなく業務ルール
```
---

## アプリケーションURL
開発環境 : http://localhost

phpMyAdmin : http://localhost:8080

ユーザー登録 : http://localhost/register

ログイン : http://localhost/login