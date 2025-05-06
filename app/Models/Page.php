<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable=['chapter_id','page_number','content'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
    

}
