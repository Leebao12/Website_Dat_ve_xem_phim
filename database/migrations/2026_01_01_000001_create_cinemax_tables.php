<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function(Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('phone',20)->nullable();
            $t->string('password'); $t->enum('role',['khach_hang','quan_ly','admin'])->default('khach_hang');
            $t->enum('status',['hoat_dong','khoa'])->default('hoat_dong'); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('movies', function(Blueprint $t) {
            $t->id(); $t->string('title'); $t->text('description')->nullable(); $t->unsignedInteger('duration'); $t->date('release_date');
            $t->string('poster')->nullable(); $t->string('trailer')->nullable();
            $t->enum('status',['sap_chieu','dang_chieu','da_ngung'])->default('sap_chieu'); $t->timestamps();
        });
        Schema::create('categories', function(Blueprint $t) {
            $t->id(); $t->string('name',100)->unique(); $t->timestamps();
        });
        Schema::create('movie_category', function(Blueprint $t) {
            $t->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $t->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $t->primary(['movie_id','category_id']);
        });
        Schema::create('rooms', function(Blueprint $t) {
            $t->id(); $t->string('name',50)->unique(); $t->unsignedInteger('total_seats')->default(0); $t->timestamps();
        });
        Schema::create('seats', function(Blueprint $t) {
            $t->id(); $t->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $t->string('seat_code',10); $t->enum('type',['thuong','vip'])->default('thuong'); $t->decimal('price',10,2);
            $t->unique(['room_id','seat_code']);
        });
        Schema::create('showtimes', function(Blueprint $t) {
            $t->id(); $t->foreignId('movie_id')->constrained('movies')->restrictOnDelete();
            $t->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $t->date('show_date'); $t->time('start_time');
            $t->enum('status',['dang_hoat_dong','da_dong','da_huy'])->default('dang_hoat_dong');
            $t->timestamps(); $t->index(['room_id','show_date','status']);
        });
        Schema::create('products', function(Blueprint $t) {
            $t->id(); $t->string('name'); $t->text('description')->nullable(); $t->decimal('price',10,2);
            $t->string('image')->nullable(); $t->enum('status',['dang_ban','ngung_ban'])->default('dang_ban'); $t->timestamps();
        });
        Schema::create('bookings', function(Blueprint $t) {
            $t->id(); $t->string('booking_code',50)->unique();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('showtime_id')->constrained('showtimes')->restrictOnDelete();
            $t->decimal('total_amount',10,2);
            $t->enum('status',['cho_thanh_toan','da_thanh_toan','da_huy'])->default('cho_thanh_toan');
            $t->timestamps(); $t->index(['showtime_id','status','created_at']);
        });
        Schema::create('booking_seats', function(Blueprint $t) {
            $t->id(); $t->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $t->foreignId('seat_id')->constrained('seats')->restrictOnDelete(); $t->decimal('price',10,2);
            $t->timestamp('created_at')->nullable(); $t->unique(['booking_id','seat_id']);
        });
        Schema::create('booking_products', function(Blueprint $t) {
            $t->id(); $t->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $t->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $t->unsignedInteger('quantity'); $t->decimal('price',10,2); $t->decimal('subtotal',10,2);
            $t->timestamp('created_at')->nullable(); $t->unique(['booking_id','product_id']);
        });
        Schema::create('payments', function(Blueprint $t) {
            $t->id(); $t->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $t->string('payment_method',50); $t->string('transaction_code',100)->nullable()->unique();
            $t->decimal('amount',10,2);
            $t->enum('status',['cho_thanh_toan','thanh_cong','that_bai'])->default('cho_thanh_toan');
            $t->timestamp('paid_at')->nullable(); $t->timestamps();
        });
    }
    public function down(): void
    {
        foreach (['payments','booking_products','booking_seats','bookings','products','showtimes','seats','rooms','movie_category','categories','movies','users'] as $name) Schema::dropIfExists($name);
    }
};
