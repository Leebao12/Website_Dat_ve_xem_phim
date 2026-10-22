<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{MovieController,BookingController,AccountController,WebhookController};
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Manager\{DashboardController,MovieController as ManagerMovieController,CategoryController,RoomController,ShowtimeController,ProductController,OrderController};
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

Route::get('/',[MovieController::class,'home'])->name('home');
Route::get('/phim',[MovieController::class,'index'])->name('movies.index');
Route::get('/phim/{movie}',[MovieController::class,'show'])->name('movies.show');
Route::get('/phong-chieu',[MovieController::class,'rooms'])->name('rooms.index');
Route::get('/phong-chieu/{room}',[MovieController::class,'room'])->name('rooms.show');
Route::middleware('guest')->group(function(){
    Route::get('/dang-nhap',[AuthController::class,'loginForm'])->name('login');
    Route::post('/dang-nhap',[AuthController::class,'login'])->name('login.store');
    Route::get('/dang-ky',[AuthController::class,'registerForm'])->name('register');
    Route::post('/dang-ky',[AuthController::class,'register'])->name('register.store');
});
Route::post('/dang-xuat',[AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth','active'])->group(function(){
    Route::get('/tai-khoan',[AccountController::class,'profile'])->name('account.profile');
    Route::put('/tai-khoan',[AccountController::class,'update'])->name('account.update');
    Route::put('/doi-mat-khau',[AccountController::class,'password'])->name('account.password');
    Route::get('/ve-cua-toi',[AccountController::class,'orders'])->name('account.orders');
    Route::get('/dat-ve/{showtime}',[BookingController::class,'create'])->name('booking.create');
    Route::post('/dat-ve/{showtime}',[BookingController::class,'store'])->name('booking.store');
    Route::get('/don-hang/{booking}',[BookingController::class,'show'])->name('booking.show');
    Route::post('/don-hang/{booking}/sandbox-pay',[BookingController::class,'pay'])->name('booking.pay');
    Route::post('/don-hang/{booking}/huy',[BookingController::class,'cancel'])->name('booking.cancel');
});
Route::middleware(['auth','active','role:quan_ly'])->prefix('quan-ly')->name('manager.')->group(function(){
    Route::get('/',[DashboardController::class,'index'])->name('dashboard');
    Route::get('doanh-thu',[DashboardController::class,'revenue'])->name('revenue');
    Route::resource('movies',ManagerMovieController::class)->except(['show']);
    Route::resource('categories',CategoryController::class)->only(['index','store','update','destroy']);
    Route::resource('rooms',RoomController::class)->only(['index','store','show','update','destroy']);
    Route::post('rooms/{room}/seats',[RoomController::class,'addSeat'])->name('rooms.seats.store');
    Route::put('rooms/{room}/seats/{seat}',[RoomController::class,'updateSeat'])->name('rooms.seats.update');
    Route::delete('rooms/{room}/seats/{seat}',[RoomController::class,'deleteSeat'])->name('rooms.seats.destroy');
    Route::resource('showtimes',ShowtimeController::class)->except(['show']);
    Route::resource('products',ProductController::class)->except(['show']);
    Route::get('orders',[OrderController::class,'index'])->name('orders.index');
});
Route::middleware(['auth','active','role:admin'])->prefix('admin')->name('admin.')->group(function(){
    Route::get('/tai-khoan',[UserController::class,'index'])->name('users.index');
    Route::put('/tai-khoan/{user}',[UserController::class,'update'])->name('users.update');
});
Route::post('/api/payments/webhook',[WebhookController::class,'receive'])->withoutMiddleware([ValidateCsrfToken::class])->name('webhook.payment');
