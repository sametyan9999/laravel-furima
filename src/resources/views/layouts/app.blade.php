<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','COACHTECH フリマ')</title>

  {{-- 共通CSS --}}
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">

  {{-- 画面ごとの追加CSSは各ビューで @push('styles') --}}
  @stack('styles')
</head>
<body>
  @include('components.header')
  <main class="container">@yield('content')</main>
</body>
</html>