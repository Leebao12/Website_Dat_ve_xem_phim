<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Movie extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['release_date'=>'date']; }
    public function categories(){ return $this->belongsToMany(Category::class,'movie_category'); }
    public function showtimes(){ return $this->hasMany(Showtime::class); }
    public function getPosterUrlAttribute(){ return $this->poster ? (str_starts_with($this->poster,'assets/') ? asset($this->poster) : asset('storage/'.$this->poster)) : asset('assets/posters/default.svg'); }
}
