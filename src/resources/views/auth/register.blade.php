@extends('layouts.app')
@section('title','会員登録')

@section('content')
<h1 class="section-title" style="text-align:center;margin-top:36px;">会員登録</h1>

<div class="container" style="max-width:520px;margin:20px auto 60px;">
  <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mt16">
      <label>ユーザー名</label>
      <input type="text" name="name" value="{{ old('name') }}" required autofocus>
      @error('name')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
    </div>

    <div class="mt16">
      <label>メールアドレス</label>
      <input type="email" name="email" value="{{ old('email') }}" required>
      @error('email')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
    </div>

    <div class="mt16">
      <label>パスワード</label>
      <input type="password" name="password" required>
      @error('password')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
    </div>

    <div class="mt16">
      <label>確認用パスワード</label>
      <input type="password" name="password_confirmation" required>
    </div>

    <div class="mt24"><button class="btn btn-block" type="submit">登録する</button></div>

    <div class="mt16" style="text-align:center;">
      <a href="{{ route('login') }}">ログインはこちら</a>
    </div>
  </form>
</div>
@endsection