<?php

namespace EricLagarda\NovaGallery\Http\Controllers;

use EricLagarda\NovaGallery\Models\Album;
use EricLagarda\NovaGallery\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    /**
     * @var mixed
     */
    protected $disk;

    public function __construct()
    {
        $this->disk = env('GALLERY_DISK', 'public');
    }

    /**
     * @param Album $album
     * @return mixed
     */
    public function getAlbumPhotos(Album $album)
    {
        return $album->photos;
    }

    /**
     * @param Request $request
     */
    public function storeFiles(Request $request)
    {
        $album = Album::findOrFail($request->album);

        $path = $request->file('file')->store(
            $album->slug,
            $this->disk
        );

        $fileName = $request->file('file')->getClientOriginalName();

        if ($photo = $this->createPhotoModel($album->id, $path, $fileName)) {
            return response()->json(['success' => true, 'photo' => $photo]);
        }

        return response()->json(['error' => true]);
    }

    /**
     * @param Photo $photo
     * @param $type
     */
    public function updatePhotoData(Photo $photo, String $type, Request $request)
    {
        if ($request->has('data')) {
            $photo->{$type} = $request->get('data');
            $photo->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => true]);
    }

    /**
     * @param Request $request
     */
    public function orderPhotos(Request $request)
    {
        if ($request->has('data')) {
            $photos = $request->get('data');

            foreach ($photos as $photoData) {
                $photo = Photo::find($photoData['id']);
                $photo->position = $photoData['position'];
                $photo->save();
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => true]);
    }

    /**
     * @param Photo $photo
     */
    public function destroyPhoto(Photo $photo)
    {
        $path = $photo->path;
        $photo->delete();

        $storage = Storage::disk($this->disk);

        if ($storage->exists($path)) {
            $storage->delete($path);
        }

        return response()->json(['success' => true]);
    }

    /**
     * @param Album $album
     * @param $imagePath
     */
    private function createPhotoModel($albumId, $imagePath, $fileName)
    {
        $photo = new Photo();
        $photo->album_id = $albumId;
        $photo->path = $imagePath;
        $photo->name = $this->getNameFromFile($fileName);
        $photo->position = $this->getOrder();
        $photo->save();

        return $photo->fresh();
    }

    /**
     * Get name from path
     *
     * @param $path
     *
     * @return string
     */
    private function getNameFromFile($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Get order from path
     *
     * @param $path
     *
     * @return null
     */
    private function getOrder()
    {
        return Photo::max('position') + 1;
    }
}
