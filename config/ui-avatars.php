<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Avatar Defaults
    |--------------------------------------------------------------------------
    |
    | These options configure the shared instance used by avatar_url(),
    | Avatars::build() and the HasAvatarUrl trait. Leave an option null to use
    | its default, and see the readme for what each option does.
    |
    */

    'alpha' => null,

    'background' => null,

    'bold' => null,

    'colors' => null,

    'font_color' => null,

    'font_size' => null,

    'format' => null,

    'hash' => null,

    'host' => null,

    'length' => null,

    'mask' => null,

    'proxy' => null,

    'region' => null,

    'rounded' => null,

    'size' => null,

    'source' => null,

    'uppercase' => null,

    /*
    |--------------------------------------------------------------------------
    | Image Disk
    |--------------------------------------------------------------------------
    |
    | The HasAvatarUrl trait resolves image paths stored on your models, e.g. an
    | avatar column, to URLs on this filesystem disk.
    |
    */

    'disk' => 'public',

];
