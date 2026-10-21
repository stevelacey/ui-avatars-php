<?php

use SteveLacey\UiAvatars\Avatars;

if (! function_exists('avatar_url')) {
    function avatar_url(mixed ...$options): string
    {
        return Avatars::build(...$options);
    }
}
