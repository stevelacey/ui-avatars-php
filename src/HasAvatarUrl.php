<?php

namespace SteveLacey\UiAvatars;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Stringable;

trait HasAvatarUrl
{
    // Override to use your own instance instead of the shared one
    public function getAvatarGenerator(): ?Avatars
    {
        return null;
    }

    public function getAvatarName(): string
    {
        $name = $this->readAvatarProperty('display_name')
            ?: $this->readAvatarProperty('full_name')
            ?: $this->readAvatarProperty('name')
            ?: trim(($this->readAvatarProperty('first_name') ?: '').' '.($this->readAvatarProperty('last_name') ?: ''))
            ?: $this->readAvatarProperty('username')
            ?: '';

        return trim(str_replace('_', ' ', $name));
    }

    public function getAvatarEmail(): string
    {
        return $this->readAvatarProperty('email') ?: '';
    }

    public function getAvatarImage(): string|Stringable|null
    {
        $image = $this->readAvatarProperty('avatar')
            ?: $this->readAvatarProperty('profile_photo_path')
            ?: $this->readAvatarProperty('profile_picture')
            ?: $this->readAvatarProperty('image')
            ?: null;

        // In Laravel, stored paths are resolved to absolute URLs on the configured disk
        if (is_string($image) && ! str_contains($image, '://') && class_exists(Storage::class) && Storage::getFacadeApplication()?->bound('filesystem')) {
            return url(Storage::disk(config('ui-avatars.disk'))->url($image));
        }

        return $image;
    }

    public function getAvatarUrl(mixed ...$options): string
    {
        $arguments = [$this->getAvatarName(), $this->getAvatarEmail(), $this->getAvatarImage(), ...$options];

        return $this->getAvatarGenerator()?->build(...$arguments) ?? Avatars::build(...$arguments);
    }

    // Eloquent accessor for $model->avatar_url
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getAvatarUrl());
    }

    // Works with plain properties and Eloquent attributes alike
    protected function readAvatarProperty(string $key): mixed
    {
        return $this->{$key} ?? null;
    }
}
