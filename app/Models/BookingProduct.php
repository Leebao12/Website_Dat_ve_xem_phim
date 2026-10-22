<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BookingProduct extends Model
{
    public $timestamps=false;
    protected $guarded=[];
    public function booking(){ return $this->belongsTo(Booking::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}
