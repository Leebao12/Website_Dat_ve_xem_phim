<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use Notifiable;
    protected $fillable=['name','email','phone','password','role','status'];
    protected $hidden=['password','remember_token'];
    protected function casts(): array { return ['password'=>'hashed']; }
    public function bookings(){ return $this->hasMany(Booking::class); }
}
