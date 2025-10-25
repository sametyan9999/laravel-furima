@extends('layouts.app')

@section('title','商品の出品')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endpush

@section('content')
<div class="container sell-container">
  <h1 class="page-title">商品の出品</h1>

  <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data" class="form">
    @csrf

    {{-- 画像アップロード --}}
    <section class="section">
      <h2 class="section-title">商品画像</h2>
      <div class="image-uploader">
        <label class="btn btn-outline">
          画像を選択する
          <input type="file" name="image" accept="image/png,image/jpeg" hidden>
        </label>
        @error('image')<p class="error">{{ $message }}</p>@enderror
      </div>
    </section>

    {{-- 詳細 --}}
    <section class="section">
      <h2 class="section-title">商品の詳細</h2>

      {{-- カテゴリー（タグ風） --}}
      <div class="category-chip-list">
        @foreach($categories as $cat)
          <label class="chip">
            <input type="radio" name="category_id" value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'checked':'' }}>
            <span>{{ $cat->name }}</span>
          </label>
        @endforeach
      </div>
      @error('category_id')<p class="error">{{ $message }}</p>@enderror

      {{-- 状態 --}}
      <div class="form-group mt24">
        <label for="condition_id">商品の状態</label>
        <select id="condition_id" name="condition_id" required>
          <option value="" disabled {{ old('condition_id') ? '' : 'selected' }}>選択してください</option>
          @foreach($conditions as $cond)
            <option value="{{ $cond->id }}" {{ old('condition_id')==$cond->id?'selected':'' }}>{{ $cond->name }}</option>
          @endforeach
        </select>
        @error('condition_id')<p class="error">{{ $message }}</p>@enderror
      </div>
    </section>

    {{-- 名前・説明・ブランド・価格 --}}
    <section class="section">
      <div class="form-group">
        <label for="name">商品名</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
        @error('name')<p class="error">{{ $message }}</p>@enderror
      </div>

      <div class="form-group">
        <label for="brand">ブランド名</label>
        <input id="brand" name="brand" type="text" value="{{ old('brand') }}">
        @error('brand')<p class="error">{{ $message }}</p>@enderror
      </div>

      <div class="form-group">
        <label for="description">商品の説明</label>
        <textarea id="description" name="description" rows="5">{{ old('description') }}</textarea>
        @error('description')<p class="error">{{ $message }}</p>@enderror
      </div>

      <div class="form-group price">
        <label for="price">販売価格</label>
        <div class="price-input">
          <span class="yen">¥</span>
          <input id="price" name="price" type="number" min="0" step="1" value="{{ old('price') }}" required>
        </div>
        @error('price')<p class="error">{{ $message }}</p>@enderror
      </div>
    </section>

    <div class="form-actions">
      <button class="btn btn-primary btn-wide" type="submit">出品する</button>
    </div>
  </form>
</div>
@endsection