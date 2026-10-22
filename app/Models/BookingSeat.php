<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BookingSeat extends Model
{
    public $timestamps=false;
    protected $guarded=[];
    public function booking(){ return $this->belongsTo(Booking::class); }
    public function seat(){ return $this->belongsTo(Seat::class); }
}
