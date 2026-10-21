<?php

namespace SteveLacey\UiAvatars\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider as Contract;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use SteveLacey\UiAvatars\Avatars;
use SteveLacey\UiAvatars\HasAvatarUrl;

// Filament's default avatar provider for users and tenants
class AvatarProvider implements Contract
{
    public function get(Model $record): string
    {
        if (in_array(HasAvatarUrl::class, class_uses_recursive($record), true)) {
            return $record->getAvatarUrl();
        }

        return Avatars::build(
            name: Filament::getNameForDefaultAvatar($record),
            email: $record->getAttributeValue('email'),
        );
    }
}
