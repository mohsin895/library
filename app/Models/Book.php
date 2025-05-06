<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable=['bookshelf_id','title','author','published_year'];
  
    public function bookshelf() {
        return $this->belongsTo(Bookshelf::class);
    }
    
    public function chapters() {
        return $this->hasMany(Chapter::class);
    }
    
}
