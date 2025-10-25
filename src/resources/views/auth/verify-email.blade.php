@extends('layouts.app')

@section('title','メール認証')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="container verify-container">
  <p class="verify-text">
    登録していただいたメールアドレスに認証メールを送付しました。<br>
    メール認証を完了してください。
  </p>

  <form method="POST" action="{{ route('verification.notice') }}" class="inline">
    @csrf
    <a class="btn btn-gray" href="{{ route('verification.verify') }}">認証はこちらから</a>
  </form>

  <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
    @csrf
    <button class="link" type="submit">認証メールを再送する</button>
  </form>

  @if (session('status') == 'verification-link-sent')
    <p class="flash success">認証リンクを再送しました。</p>
  @endif
</div>
@endsection