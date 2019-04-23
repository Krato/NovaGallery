<?php

namespace EricLagarda\NovaGallery\Models;

use EricLagarda\NovaGallery\Models\Album;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    /**
     * @var string
     */
    protected $table = 'el_photos';

    /**
     * @var array
     */
    protected $appends = ['image'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * @param $disk
     */
    public function getImageAttribute()
    {
        return Storage::disk(env('GALLERY_DISK', 'public'))->url($this->path);
    }
}
