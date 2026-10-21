<?php

use Illuminate\Database\Eloquent\Model;
use SteveLacey\UiAvatars\Jetstream\HasProfilePhoto;

class JetstreamUser extends Model
{
    use HasProfilePhoto;

    protected $guarded = [];
}

test('profile photo url falls back to the avatar url', function () {
    $user = new JetstreamUser(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    expect($user->profile_photo_url)->toBe(avatar_url(name: 'Ada Lovelace', email: 'ada@example.com'));
});

test('profile photo url uses an uploaded photo', function () {
    $user = new JetstreamUser(['name' => 'Ada Lovelace', 'profile_photo_path' => 'profile-photos/ada.png']);
    expect($user->profile_photo_url)->toBe('/storage/profile-photos/ada.png');
});

test('avatar url is available too', function () {
    $user = new JetstreamUser(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    expect($user->avatar_url)->toBe($user->profile_photo_url);
});
