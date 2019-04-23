<?php

namespace EricLagarda\NovaGallery\Http\Observers;

use EricLagarda\NovaGallery\Models\Album;
use Illuminate\Support\Facades\Storage;

class AlbumObserver
{
    /**
     * @param Album $album
     */
    public function creating(Album $album)
    {
        $storage = $this->getStorage();

        if ($storage->makeDirectory($album->slug)) {
            return true;
        }

        return false;
    }

    /**
     * Handle the Page "updating" event.
     *
     * Saves no column fields into data column
     * and translated fields into extras column
     *
     * @param  EricLagarda\NovaGallery\Models\Album  $album
     *
     * @return bool
     */
    public function updating(Album $album)
    {
        $storage = $this->getStorage();

        $oldAlbumList = $album->getOriginal('slug');

        if ($storage->has($oldAlbumList)) {
            if ($storage->rename($oldAlbumList, $album->slug)) {
                $this->replacePhotosUrl($album, $oldAlbumList, $album->slug);

                return true;
            }
        }

        return false;
    }

    /**
     * Delete files when Album is deleted
     *
     * @param Album $album
     */
    public function deleted(Album $album)
    {
        $storage = $this->getStorage();

        $storage->deleteDirectory($album->slug);
    }

    /**
     * @param Album $album
     * @param $oldUrl
     * @param $newUrl
     */
    private function replacePhotosUrl(Album $album, $oldUrl, $newUrl)
    {
        $album->photos->each(function ($photo) use ($oldUrl, $newUrl) {
            $photo->path = str_replace($oldUrl, $newUrl, $photo->path);
            $photo->save();
        });
    }

    private function getStorage()
    {
        $disk = env('GALLERY_DISK', 'public');

        return Storage::disk($disk);
    }
}
