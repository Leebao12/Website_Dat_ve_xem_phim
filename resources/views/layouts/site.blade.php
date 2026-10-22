<!doctype html><html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title','CINEMAX – Trải nghiệm vượt xa điện ảnh')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/cinemax.css') }}">
</head><body>
<header class="main-header"><div class="container navbar-shell"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('assets/brand/cinemax-icon.png') }}" alt="Cinemax"><span>CINE<span>MAX</span></span></a>
<nav class="nav-links"><a href="{{ route('home') }}">Trang chủ</a><a href="{{ route('movies.index') }}">Phim</a><a href="{{ route('rooms.index') }}">Phòng chiếu</a>
@auth
@if(auth()->user()->role==='khach_hang')<a href="{{ route('account.orders') }}">Vé của tôi</a>@endif
@if(auth()->user()->role==='quan_ly')<a href="{{ route('manager.dashboard') }}">Bảng quản lý</a>@endif
@if(auth()->user()->role==='admin')<a href="{{ route('admin.users.index') }}">Quản trị</a>@endif
@endauth</nav>
<div class="nav-account">@auth<a class="hello-name" href="{{ route('account.profile') }}">{{ auth()->user()->name }}</a><form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-light btn-sm">Đăng xuất</button></form>@else<a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Đăng nhập</a><a class="btn btn-hot btn-sm" href="{{ route('register') }}">Đăng ký</a>@endauth</div>
</div></header>
<main class="main-content">@if(session('success'))<div class="container pt-4"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="container pt-4"><div class="alert alert-danger">{{ session('error') }}</div></div>@endif
@if($errors->any())<div class="container pt-4"><div class="alert alert-danger"><strong>Vui lòng kiểm tra lại:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
@yield('content')</main>
<footer class="site-footer"><div class="container d-flex justify-content-between flex-wrap gap-3"><div><b>CINEMAX</b><p>Trải nghiệm vượt xa điện ảnh.</p></div><div>Đồ án Lập trình mã nguồn mở · Laravel · MySQL<br><small>Hệ thống demo phục vụ học tập, thanh toán sandbox.</small></div></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>@stack('scripts')
</body></html>
