@extends('layouts.app')

@section('title','プロフィール設定')

@section('content')
  <h1 class="section-title" style="text-align:center">プロフィール設定</h1>
  <div class="container" style="max-width:720px;margin:20px auto 60px;">
    <form method="POST" action="{{ route('mypage.profile.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="flex" style="align-items:center">
        <div style="width:100px;height:100px;border-radius:50%;background:#ddd"></div>
        <div><label class="btn" style="margin-left:16px;cursor:pointer;">
          画像を選択する <input type="file" name="avatar" accept=".jpg,.jpeg,.png" style="display:none">
        </label></div>
      </div>

      <div class="mt24">
        <label>ユーザー名</label>
        <input type="text" name="user_name" value="{{ old('user_name', auth()->user()->name ?? '') }}">
      </div>

      <div class="mt16">
        <label>郵便番号</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}">
      </div>

      <div class="mt16">
        <label>住所</label>
        <input type="text" name="address1" value="{{ old('address1', $profile->address_line1 ?? '') }}">
      </div>

      <div class="mt16">
        <label>建物名</label>
        <input type="text" name="address2" value="{{ old('address2', $profile->address_line2 ?? '') }}">
      </div>

      <div class="mt24">
        <button class="btn btn-block">更新する</button>
      </div>
    </form>
  </div>
@endsection