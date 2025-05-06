<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Book;

class Bookshelf extends Model
{
   protected $fillable=['name','location'];
   public function books()
   {
       return $this->hasMany(Book::class);
   }

   
}
