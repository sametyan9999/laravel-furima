<header class="gt-header">
  <div class="gt-header__inner container">
    {{-- ロゴ --}}
    <a href="{{ route('items.index') }}" class="gt-logo">
      <img src="{{ asset('images/coachtech-logo.svg') }}" alt="COACHTECH">
    </a>

    {{-- 検索フォーム --}}
    <form action="{{ route('items.index') }}" method="get" class="gt-search">
      <input type="search" name="q" value="{{ request('q') }}" placeholder="なにをお探しですか？">
    </form>

    {{-- 右上ナビ --}}
    <nav class="gt-nav">
      @auth
        {{-- ✅ ログイン中：ログアウト表示 --}}
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="gt-link">ログアウト</a>
      @else
        {{-- ✅ 未ログイン時：ログイン表示 --}}
        <a href="{{ route('login') }}" class="gt-link">ログイン</a>
      @endauth

      {{-- ✅ マイページは常に表示 --}}
      <a href="{{ route('mypage.index') }}" class="gt-link">マイページ</a>

      {{-- ✅ 出品ボタンも常に表示 --}}
      <a href="{{ route('items.create') }}" class="gt-btn gt-btn--sell">出品</a>

      {{-- ✅ ログアウト用フォーム --}}
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
      </form>
    </nav>
  </div>
</header>