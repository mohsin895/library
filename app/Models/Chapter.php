<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable=['book_id','title','chapter_number'];
    public function books()
{
    return $this->hasMany(Book::class);
}
public function pages()
{
    return $this->hasMany(Page::class);
}


}
