@extends('layouts.app')
@section('title','商品の出品')

@section('content')
<h1 class="section-title" style="text-align:center;margin-top:24px;">商品の出品</h1>

<form class="mt24" action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
  @csrf

  {{-- 画像アップロード --}}
  <div class="mt16">
    <label class="muted">商品画像</label>
    <div style="border:1px dashed #bbb;border-radius:8px;padding:20px;text-align:center;">
      <label class="btn" style="cursor:pointer;">
        画像を選択する
        <input type="file" name="images[]" accept=".jpg,.jpeg,.png" multiple style="display:none">
      </label>
    </div>
    @error('images.*')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
  </div>

  <hr class="mt32">

  {{-- 商品の詳細 --}}
  <div class="section-title">商品の詳細</div>

  {{-- カテゴリー（チップ）--}}
  <div class="mt8">
    <label class="muted">カテゴリー</label>
    <div class="mt8" style="display:flex;flex-wrap:wrap;gap:10px;">
      @foreach($categories as $cat)
        <label class="badge" style="border:1px solid #e2504e;color:#e2504e;cursor:pointer;">
          <input type="radio" name="category_id" value="{{ $cat->id }}" style="display:none"
                 {{ old('category_id')==$cat->id ? 'checked' : '' }}>
          {{ $cat->name }}
        </label>
      @endforeach
    </div>
    @error('category_id')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
  </div>

  {{-- 状態（セレクト）--}}
  <div class="mt24">
    <label class="muted">商品の状態</label>
    <select name="condition_id" required>
      <option value="">選択してください</option>
      @foreach($conditions as $cond)
        <option value="{{ $cond->id }}" {{ old('condition_id')==$cond->id ? 'selected' : '' }}>
          {{ $cond->name }}
        </option>
      @endforeach
    </select>
    @error('condition_id')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
  </div>

  <hr class="mt32">

  {{-- 商品名と説明 --}}
  <div class="mt8">
    <label class="muted">商品名</label>
    <input type="text" name="name" value="{{ old('name') }}" required>
    @error('name')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
  </div>

  <div class="mt16">
    <label class="muted">ブランド名</label>
    <input type="text" name="brand" value="{{ old('brand') }}">
    @error('brand')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
  </div>

  <div class="mt16">
    <label class="muted">商品の説明</label>
    <textarea name="description">{{ old('description') }}</textarea>
    @error('description')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
  </div>

  {{-- 価格 --}}
  <div class="mt16">
    <label class="muted">販売価格</label>
    <div style="display:flex;align-items:center;gap:8px;">
      <span>¥</span>
      <input type="number" name="price" min="0" step="1" value="{{ old('price') }}" required>
    </div>
    @error('price')<div class="muted" style="color:#e2504e">{{ $message }}</div>@enderror
  </div>

  <div class="mt32">
    <button class="btn btn-block" type="submit">出品する</button>
  </div>
</form>
@endsection