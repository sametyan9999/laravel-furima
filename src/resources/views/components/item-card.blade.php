@props(['item'])
<a href="{{ route('items.show', $item) }}" class="card">
  <div class="card__thumb">
    <img src="{{ $item->image }}" alt="{{ $item->name }}">
    @if($item->status === 'sold')
      <span class="badge badge--sold">SOLD</span>
    @endif
  </div>
  <div class="card__meta">
    <p class="card__name">{{ $item->name }}</p>
  </div>
</a>