@extends('layouts.app')

@section('title','ログイン')

@section('content')
  <h1 class="section-title" style="font-size:24px;text-align:center;margin-top:36px;">ログイン</h1>

  <div class="container" style="max-width:520px;margin:20px auto 60px;">
    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="mt16">
        <label>メールアドレス</label>
        <input type="email" name="email" required autofocus value="{{ old('email') }}">
        @error('email')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
      </div>

      <div class="mt16">
        <label>パスワード</label>
        <input type="password" name="password" required>
        @error('password')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
      </div>

      <div class="mt24">
        <button class="btn btn-block" type="submit">ログインする</button>
      </div>

      <div class="mt16" style="text-align:center;">
        <a href="{{ route('register') }}">会員登録はこちら</a>
      </div>
    </form>
  </div>
@endsection