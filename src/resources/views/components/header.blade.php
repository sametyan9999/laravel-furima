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

    {{-- 右上ナビ：常にログイン＆マイページを表示 --}}
    <nav class="gt-nav">
      <a href="{{ route('login') }}" class="gt-link">ログイン</a>
      <a href="{{ route('mypage.index') }}" class="gt-link">マイページ</a>
      <a href="{{ route('items.create') }}" class="gt-btn gt-btn--sell">出品</a>
    </nav>
  </div>
</header>