<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','COACHTECH フリマ')</title>

  {{-- ベース共通CSS（ヘッダー等のスタイル） --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  {{-- ページ側から差し込む追加CSS（auth.css / login.css / register.css など） --}}
  @stack('styles')
</head>
<body>
  {{-- 認証レイアウト用ヘッダー：ロゴのみ（検索欄・右上ボタンなし） --}}
  <header class="gt-header">
    <div class="gt-header__inner container">
      <a href="{{ route('items.index') }}" class="gt-logo">
        <img src="{{ asset('images/coachtech-logo.svg') }}" alt="COACHTECH">
      </a>
    </div>
  </header>

  <main class="container">
    @yield('content')
  </main>
</body>
</html>