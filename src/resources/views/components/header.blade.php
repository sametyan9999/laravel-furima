<header>
  <div class="container bar">
    <div class="brand">COACHTECH</div>
    <div class="search">
      <form method="GET" action="{{ route('items.index') }}">
        <input name="q" value="{{ request('q') }}" placeholder="なにをお探しですか？" />
      </form>
    </div>
    <nav class="nav">
      @auth
        <form class="inline" method="POST" action="{{ route('logout') }}">@csrf
          <button type="submit">ログアウト</button>
        </form>
        <a href="{{ route('mypage.index') }}">マイページ</a>
        <a class="sell" href="{{ route('items.create') }}">出品</a>
      @else
        <a href="{{ route('login') }}">ログイン</a>
        <a href="{{ route('mypage.index') }}">マイページ</a>
        <a class="sell" href="{{ route('items.create') }}">出品</a>
      @endauth
    </nav>
  </div>
</header>