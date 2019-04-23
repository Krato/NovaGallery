<?php

namespace EricLagarda\NovaGallery\Resources;

use App\Nova\Resource;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use EricLagarda\NovaGallery\GalleryResourceTool;
use EricLagarda\NovaGallery\Models\Album;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;

class AlbumResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Album::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Albums');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Album');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Avatar::make(__('Preview'), 'default_image')->thumbnail(function () {
                return $this->defaultPhoto();
            })->preview(function () {
                return $this->defaultPhoto();
            })->hideFromDetail()
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->disableDownload(),

            TextWithSlug::make(__('Name'), 'name')
                ->sortable()
                ->slug('Slug')
                ->rules('required'),

            Slug::make(__('Slug'), 'slug')
                ->rules('required'),

            Text::make(__('Images Count'), function () {
                return (string) $this->totalPhotos();
            })->hideFromDetail()
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Trix::make(__('Description'), 'description'),

            GalleryResourceTool::make(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'albums-resource';
    }
}
