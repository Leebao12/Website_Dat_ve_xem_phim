<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
class Showtime extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['show_date'=>'date']; }
    public function movie(){ return $this->belongsTo(Movie::class); }
    public function room(){ return $this->belongsTo(Room::class); }
    public function bookings(){ return $this->hasMany(Booking::class); }
    public function getStartsAtAttribute(): Carbon { return Carbon::parse($this->show_date->format('Y-m-d').' '.$this->start_time); }
}
