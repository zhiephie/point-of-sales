<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory, Searchable;

    protected $guarded = ['id'];

    protected $hidden = ['image'];

    protected $appends = ['image_url'];

    public $searchable = ['name', 'barcode', 'description'];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value);
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function getImageUrlAttribute()
    {
        $url = '';
        if (strlen($this->image) > 5)
            $url = url('images/' . $this->image->url);

        return $url;
    }
}
