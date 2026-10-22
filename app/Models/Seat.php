<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Seat extends Model
{
    public $timestamps=false;
    protected $guarded=[];
    public function room(){ return $this->belongsTo(Room::class); }
    public function bookingSeats(){ return $this->hasMany(BookingSeat::class); }
}
