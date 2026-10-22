<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Booking extends Model
{
    protected $guarded=[];
    public function user(){ return $this->belongsTo(User::class); }
    public function showtime(){ return $this->belongsTo(Showtime::class); }
    public function bookingSeats(){ return $this->hasMany(BookingSeat::class); }
    public function bookingProducts(){ return $this->hasMany(BookingProduct::class); }
    public function payments(){ return $this->hasMany(Payment::class); }
    public function getExpiredAttribute(): bool { return $this->status==='cho_thanh_toan' && $this->created_at->lt(now()->subMinutes(config('cinemax.hold_minutes'))); }
}
