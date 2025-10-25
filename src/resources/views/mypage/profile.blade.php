@extends('layouts.app')
@section('title','プロフィール設定')

@section('content')
  <div class="form-box">
    <h1 class="form-title">プロフィール設定</h1>
    <form method="post" action="{{ route('mypage.profile.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="profile-avatar">
        <div class="avatar lg"></div>
        <label class="gt-btn gt-btn--white ml-12">
          画像を選択する <input type="file" name="avatar" hidden>
        </label>
      </div>

      <label class="mt-16">ユーザー名</label>
      <input name="name" value="{{ old('name',auth()->user()->name) }}">

      <label class="mt-12">郵便番号</label>
      <input name="postal_code" value="{{ old('postal_code',$profile->postal_code) }}">

      <label class="mt-12">住所</label>
      <input name="address_line1" value="{{ old('address_line1',$profile->address_line1) }}">

      <label class="mt-12">建物名</label>
      <input name="address_line2" value="{{ old('address_line2',$profile->address_line2) }}">

      <button class="gt-btn gt-btn--primary mt-24">更新する</button>
    </form>
  </div>
@endsection