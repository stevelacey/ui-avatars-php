<?php

namespace SteveLacey\UiAvatars\Jetstream;

use Laravel\Jetstream\HasProfilePhoto as JetstreamHasProfilePhoto;
use SteveLacey\UiAvatars\HasAvatarUrl;

// Drop-in replacement for Jetstream's trait that falls back to our avatar URL
trait HasProfilePhoto
{
    use HasAvatarUrl, JetstreamHasProfilePhoto;

    protected function defaultProfilePhotoUrl()
    {
        return $this->getAvatarUrl();
    }
}
