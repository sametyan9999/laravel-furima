@extends('layouts.app')
@section('title','メール認証のお願い')

@section('content')
  <div class="container" style="max-width:720px;margin:60px auto;text-align:center;">
    <p style="font-weight:700;">
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール認証を完了してください。
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="mt24">
      @csrf
      <button class="btn">認証メールを再送する</button>
    </form>

    <div class="mt24">
      <a class="muted" href="{{ url('/') }}">トップへ戻る</a>
    </div>
  </div>
@endsection