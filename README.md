# Nova Gallery 

Photo gallery tool for Laravel Nova.


![preview](https://user-images.githubusercontent.com/74367/56567874-1870a700-65b6-11e9-90a3-502acc857f12.png)


## Installation

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:


```bash
composer require ericlagarda/nova-gallery
```

Then, register the tool in NovaServiceProvider.php

```php

use EricLagarda\NovaGallery\NovaGallery;

public function tools()
{
    return [
        // ...
 
        new NovaGallery
    ];
}
```

Then, publish the migration and migrate the tables:

```bash
php artisan vendor:publish --tag=gallery-migration
php artisan migrate
```

And set the Gallery Storage disk on your env file:
```
GALLERY_DISK=my_custom_storage
```


## Use

You can create albums and add photos into the albums. You can:

* Change the name of each photo
* Change the description of each photo
* Reorder photos
