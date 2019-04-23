<?php

namespace EricLagarda\NovaGallery\Models;

use EricLagarda\NovaGallery\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    /**
     * @var string
     */
    protected $table = 'el_albums';

    /**
     * @var array
     */
    protected $fillable = ['name', 'description', 'order'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('position');
    }

    /**
     * Return default photo for album
     *
     * @return string
     */
    public function defaultPhoto()
    {
        if ($this->photos()->count()) {
            return $this->photos->first()->image;
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function totalPhotos()
    {
        return $this->photos()->count();
    }
}
