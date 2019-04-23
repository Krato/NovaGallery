<?php

namespace EricLagarda\NovaGallery;

use Laravel\Nova\ResourceTool;

class GalleryResourceTool extends ResourceTool
{

    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return __('Photos');
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'album';
    }
}
