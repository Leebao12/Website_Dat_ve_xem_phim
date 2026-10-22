<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['paid_at'=>'datetime']; }
    public function booking(){ return $this->belongsTo(Booking::class); }
}
