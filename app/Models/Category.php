<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model
{
    protected $guarded=[];
    public function movies(){ return $this->belongsToMany(Movie::class,'movie_category'); }
}
