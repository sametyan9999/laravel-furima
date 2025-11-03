# COACHTECHフリマ

## 環境構築
- `git clone <repo>`
- `docker compose up -d --build`
- `docker compose exec php bash`
- `composer install`
- `cp .env.example .env` （DBは .env に同梱のDocker値をセット済み）
- `php artisan key:generate`
- `php artisan migrate --seed`
- `php artisan storage:link`

## 開発URL
- アプリ: http://localhost/
- phpMyAdmin: http://localhost:8080/

## 使用技術（実行環境）
- PHP 8.1 / Laravel 8.x / MySQL 8.4 / nginx 1.27 / Docker

## ER図
![ER](docs/er.png)  <!-- 画像パスは任意 -->