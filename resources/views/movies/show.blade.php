@extends('layouts.site')
@section('title',$movie->title.' | CINEMAX')
@section('content')
<section class="detail-hero"><div class="container"><div class="detail-grid"><div class="detail-poster"><img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}"></div><div><a href="{{ route('movies.index') }}" class="text-orange">← Danh sách phim</a><span class="overline d-block mt-3">NOW SHOWING · CINEMAX</span><h1>{{ $movie->title }}</h1><div class="movie-meta"><span>{{ $movie->duration }} phút</span><span>Khởi chiếu {{ $movie->release_date->format('d/m/Y') }}</span><span>{{ $movie->categories->pluck('name')->join(' / ') }}</span></div><p class="lead text-muted-light">{{ $movie->description }}</p>@if($movie->trailer)<a class="btn btn-outline-light" target="_blank" rel="noopener noreferrer" href="{{ $movie->trailer }}">▶ Xem trailer</a>@endif</div></div></div></section>
<div class="container section-block"><span class="overline">PICK A DATE</span><h2>Lịch chiếu trong 7 ngày</h2><div class="date-tabs">@foreach($dates as $date)<a href="#date-{{ $date->format('Y-m-d') }}" class="date-tab" data-date="{{ $date->format('Y-m-d') }}"><strong>{{ $date->format('d/m') }}</strong><small>{{ ['CN','T2','T3','T4','T5','T6','T7'][$date->dayOfWeek] }}</small></a>@endforeach</div>
@foreach($dates as $date)<div class="day-card" data-date="{{ $date->format('Y-m-d') }}" id="date-{{ $date->format('Y-m-d') }}"><h3>{{ ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'][$date->dayOfWeek] }}, {{ $date->format('d/m/Y') }}</h3><div class="d-flex gap-2 flex-wrap">@forelse($showtimes->get($date->format('Y-m-d'),collect()) as $s)<a href="{{ route('booking.create',$s) }}" class="time-pill"><strong>{{ $s->starts_at->format('H:i') }}</strong><small>{{ $s->room->name }}</small></a>@empty<span class="quiet-text">Chưa có suất chiếu trong ngày này.</span>@endforelse</div></div>@endforeach</div>
@endsection

@push('scripts')
<script>
(() => {
  const tabs=[...document.querySelectorAll('.date-tab[data-date]')];
  const days=[...document.querySelectorAll('.day-card[data-date]')];
  if(!tabs.length)return;
  function select(date){
    tabs.forEach(t=>t.classList.toggle('active',t.dataset.date===date));
    days.forEach(d=>d.hidden=d.dataset.date!==date);
  }
  tabs.forEach(t=>t.addEventListener('click',e=>{e.preventDefault();select(t.dataset.date)}));
  select(tabs[0].dataset.date);
})();
</script>
@endpush
