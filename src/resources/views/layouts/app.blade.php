<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','COACHTECH フリマ')</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>
  {{-- ページ側で @section('header') があればそれを使い、無ければ通常ヘッダー --}}
  @hasSection('header')
    @yield('header')
  @else
    @include('components.header')
  @endif

  <main class="container">@yield('content')</main>

  @stack('scripts')
</body>
</html>