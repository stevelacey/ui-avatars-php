<?php

use Illuminate\Database\Eloquent\Model;
use SteveLacey\UiAvatars\Avatars;
use SteveLacey\UiAvatars\HasAvatarUrl;

class User extends Model
{
    use HasAvatarUrl;

    protected $guarded = [];
}

test('avatar url attribute uses model attributes', function () {
    $user = new User(['name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'avatar' => 'https://cdn.example.com/ada.png']);
    expect($user->avatar_url)->toBe(avatar_url(name: 'Ada Lovelace', email: 'ada@example.com', image: 'https://cdn.example.com/ada.png'));
});

test('missing attributes fall through', function () {
    $user = new User(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    expect($user->getAvatarName())->toBe('Ada Lovelace');
    expect($user->getAvatarEmail())->toBe('');
    expect($user->getAvatarImage())->toBeNull();
});

test('full name accessor is used', function () {
    $user = new class(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'username' => 'ada']) extends User
    {
        public function getFullNameAttribute()
        {
            return 'Countess '.$this->last_name;
        }
    };

    expect($user->getAvatarName())->toBe('Countess Lovelace');
});

test('it uses the shared instance', function () {
    Avatars::configure(size: 64);
    expect((new User(['name' => 'Ada Lovelace']))->avatar_url)->toContain('/64/');
});

test('stored image paths are resolved on the public disk', function () {
    $user = new User(['name' => 'Ada Lovelace', 'avatar' => 'avatars/ada.png']);
    expect($user->getAvatarImage())->toBe('http://localhost/storage/avatars/ada.png');
    expect($user->avatar_url)->toContain('http%3A%2F%2Flocalhost%2Fstorage%2Favatars%2Fada.png');
});

test('stored image paths are resolved on the configured disk', function () {
    config(['filesystems.disks.avatars' => ['driver' => 'local', 'root' => '/tmp', 'url' => 'https://cdn.example.com']]);
    config(['ui-avatars.disk' => 'avatars']);

    expect((new User(['image' => 'ada.png']))->getAvatarImage())->toBe('https://cdn.example.com/ada.png');
});

test('image urls are not resolved on the disk', function () {
    expect((new User(['avatar' => 'https://cdn.example.com/ada.png']))->getAvatarImage())->toBe('https://cdn.example.com/ada.png');
});

test('avatar url attribute is visible to filament', function () {
    $user = new User(['name' => 'Ada Lovelace']);
    expect($user->getAttributeValue('avatar_url'))->toBe($user->getAvatarUrl());
});

test('jetstream profile photo paths are resolved', function () {
    $user = new User(['name' => 'Ada Lovelace', 'profile_photo_path' => 'profile-photos/ada.png']);
    expect($user->getAvatarImage())->toBe('http://localhost/storage/profile-photos/ada.png');
});
