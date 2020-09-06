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
        return true;
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

        $oldAlbumSlug = $album->getOriginal('slug');

        if ($oldAlbumSlug == $album->slug) {
            return true;
        }

        if ($storage->has($oldAlbumSlug)) {
            if ($this->renameFolder($oldAlbumSlug, $album->slug)) {
                $this->replacePhotosUrl($album, $oldAlbumSlug, $album->slug);

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
     * Rename folder moving all files to new location and then deleting old folder.
     * s3 and others adapters dont work just renaming the folder. Thats the way to do it.
     *
     * @param string $oldUrl
     * @param string $newUrl
     */
    private function renameFolder($oldUrl, $newUrl)
    {
        $storage = $this->getStorage();

        if ($storage->makeDirectory($newUrl)) {
            $files = $storage->files($oldUrl);

            foreach ($files as $file) {
                $newFile = str_replace($oldUrl, $newUrl, $file);
                $storage->move($file, $newFile);
                $storage->delete($file);
            }

            if ($storage->deleteDirectory($oldUrl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Album $album
     * @param $oldUrl
     * @param $newUrl
     */
    private function replacePhotosUrl(Album $album, $oldPath, $newPath)
    {
        $album->photos->each(function ($photo) use ($oldPath, $newPath) {
            $photo->path = str_replace($oldPath, $newPath, $photo->path);
            $photo->save();
        });
    }

    private function getStorage()
    {
        $disk = env('GALLERY_DISK', 'public');

        return Storage::disk($disk);
    }
}
