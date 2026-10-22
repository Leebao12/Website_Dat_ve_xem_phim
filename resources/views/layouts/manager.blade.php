@extends('layouts.site')
@section('content')
<div class="container py-4"><div class="admin-head"><div><span class="overline">CINEMAX BACK OFFICE</span><h1>@yield('heading','Quản lý hệ thống')</h1></div><span class="role-chip">{{ auth()->user()->role==='admin'?'QUẢN TRỊ VIÊN':'QUẢN LÝ RẠP' }}</span></div>
<div class="row g-4"><aside class="col-lg-3"><div class="admin-menu">
@if(auth()->user()->role==='quan_ly')
<a href="{{ route('manager.dashboard') }}">Tổng quan</a><a href="{{ route('manager.movies.index') }}">Quản lý phim</a><a href="{{ route('manager.categories.index') }}">Thể loại phim</a><a href="{{ route('manager.rooms.index') }}">Phòng & ghế</a><a href="{{ route('manager.showtimes.index') }}">Suất chiếu</a><a href="{{ route('manager.products.index') }}">Bắp nước & combo</a><a href="{{ route('manager.orders.index') }}">Đơn đặt vé</a><a href="{{ route('manager.revenue') }}">Doanh thu</a>
@else<a href="{{ route('admin.users.index') }}">Tài khoản người dùng</a>@endif
</div></aside><section class="col-lg-9">@yield('panel')</section></div></div>
@endsection
