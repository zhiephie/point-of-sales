<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    public $timestamps = false;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = ['url'];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function getImageUrlAttribute()
    {
        $url = '';
        if (strlen($this->url) > 5)
            $url = url('images/' . $this->url);

        return $url;
    }
}
