@extends('layouts.app')

@section('title', 'メール認証')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth-verify.css') }}?v={{ filemtime(public_path('css/auth-verify.css')) }}">
@endpush

@section('content')
<div class="verify-wrap">
  <div class="verify-panel">
    <p class="verify-heading">
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール内のリンクをクリックして認証を完了してください。
    </p>

    {{-- ✅ テスト手順②: 「認証はこちらから」= メール認証サイトを開く（開発はMailHog） --}}
    <a href="{{ config('services.mailhog.url', 'http://localhost:8025') }}"
       target="_blank" rel="noopener" class="verify-btn">
      認証はこちらから
    </a>

    {{-- 認証メールの再送 --}}
    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="verify-resend">認証メールを再送する</button>
    </form>

    @if (session('status') === 'verification-link-sent')
      <p class="verify-flash">認証リンクを再送しました。</p>
    @endif
  </div>
</div>
@endsection