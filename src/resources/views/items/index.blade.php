@extends('layouts.app')

@section('title','商品一覧')

@section('content')
  {{-- タブ（おすすめ / マイリスト） --}}
  @php $tab = request('tab') === 'mylist' ? 'mylist' : 'recommend'; @endphp
  <div class="tabs">
    <a href="{{ route('items.index') }}" class="{{ $tab==='recommend' ? 'active' : '' }}">おすすめ</a>
    <a href="{{ route('items.index', ['tab'=>'mylist']) }}" class="{{ $tab==='mylist' ? 'active' : '' }}">マイリスト</a>
  </div>

  {{-- 商品グリッド --}}
  <div class="grid mt24">
    @forelse($items as $item)
      @include('components.item-card', ['item' => $item])
    @empty
      <p>商品がありません。</p>
    @endforelse
  </div>

  {{-- ページネーション --}}
  <div class="mt24">
    {{ $items->withQueryString()->links() }}
  </div>
@endsection