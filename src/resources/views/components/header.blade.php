<header class="gt-header">
  <div class="gt-header__inner container">
    {{-- ロゴ：クリックでトップへ --}}
    <a href="{{ route('items.index', ['reset' => 1]) }}" class="gt-logo" aria-label="COACHTECH top">
      <img src="{{ asset('images/coachtech-logo.svg') }}" alt="COACHTECH">
    </a>

    @if (!request()->routeIs('verification.*'))
      {{-- 通常ページのみ表示 --}}
      <form action="{{ route('items.index') }}" method="get" class="gt-search" role="search" aria-label="商品検索">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="なにをお探しですか？">
      </form>

      <nav class="gt-nav">
        @auth
          <a href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
             class="gt-link">ログアウト</a>
        @else
          <a href="{{ route('login') }}" class="gt-link">ログイン</a>
        @endauth

        <a href="{{ route('mypage.index') }}" class="gt-link">マイページ</a>
        <a href="{{ route('items.create') }}" class="gt-btn gt-btn--sell">出品</a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
          @csrf
        </form>
      </nav>
    @endif
  </div>
</header>