<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    protected $guarded=[];
    public function getImageUrlAttribute(){ return $this->image ? (str_starts_with($this->image,'assets/')?asset($this->image):asset('storage/'.$this->image)) : null; }
}
