@extends('layouts.app')

@section('title','商品の出品')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endpush

@section('content')
<div class="sell-wrapper">
  <h1 class="sell-title">商品の出品</h1>

  <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data" class="sell-form">
    @csrf

    {{-- 商品画像 --}}
    <section class="sell-section">
      <h2 class="sell-label">商品画像</h2>

      <div class="sell-image-box">
        <div class="sell-image-frame" id="image_frame" aria-live="polite">
          <img id="image_preview_img" alt="">
          <label for="image_file" class="sell-image-label" id="image_pick_btn">画像を選択する</label>
        </div>

        <input
          id="image_file"
          type="file"
          name="image_file"
          accept="image/png,image/jpeg"
          style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;"
        >
      </div>
      @error('image_file')<p class="error">{{ $message }}</p>@enderror
    </section>

    {{-- 商品の詳細 --}}
    <section class="sell-section">
      <h2 class="sell-label">商品の詳細</h2>

      {{-- 🔥 カテゴリー（複数選択対応） --}}
      <div class="sell-subtitle">カテゴリー</div>
      <div class="category-chip-list">
        @php
          // old()で再表示時にチェック状態を保持
          $oldCats = collect(old('category_ids', []))->map(fn($v)=>(int)$v)->all();
        @endphp

        @foreach($categories as $cat)
          <label class="chip">
            <input type="checkbox"
                   name="category_ids[]"
                   value="{{ $cat->id }}"
                   {{ in_array($cat->id, $oldCats, true) ? 'checked' : '' }}>
            <span>{{ $cat->name }}</span>
          </label>
        @endforeach
      </div>

      {{-- バリデーションメッセージ --}}
      @error('category_ids')
        <p class="error">{{ $message }}</p>
      @enderror
      @error('category_ids.*')
        <p class="error">{{ $message }}</p>
      @enderror

      {{-- 状態 --}}
      <div class="form-group mt24">
        <label for="condition_id" class="sell-subtitle">商品の状態</label>
        <select id="condition_id" name="condition_id" required>
          @unless(old('condition_id'))
            <option value="" disabled selected hidden>選択してください</option>
          @endunless
          @foreach($conditions as $cond)
            <option value="{{ $cond->id }}" {{ (string)old('condition_id')===(string)$cond->id ? 'selected':'' }}>
              {{ $cond->name }}
            </option>
          @endforeach
        </select>
        @error('condition_id')<p class="error">{{ $message }}</p>@enderror
      </div>
    </section>

    {{-- 商品名・説明など --}}
    <section class="sell-section">
      <h2 class="sell-label">商品名と説明</h2>

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

    {{-- 出品ボタン --}}
    <div class="form-actions">
      <button class="btn-submit" type="submit">出品する</button>
    </div>
  </form>
</div>

{{-- プレビュー制御スクリプト --}}
<script>
  (function () {
    const input = document.getElementById('image_file');
    const frame = document.getElementById('image_frame');
    const img   = document.getElementById('image_preview_img');

    if (!input || !frame || !img) return;

    function applyState() {
      const hasSrc = !!img.getAttribute('src');
      frame.classList.toggle('has-image', hasSrc);
    }

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) {
        img.removeAttribute('src');
        applyState();
        return;
      }
      img.src = URL.createObjectURL(file);
      applyState();
    });

    applyState();
  })();
</script>
@endsection