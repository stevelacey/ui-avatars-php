<?php

use SteveLacey\UiAvatars\Avatars;
use SteveLacey\UiAvatars\HasAvatarUrl;

#[AllowDynamicProperties]
class Person
{
    use HasAvatarUrl;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }
}

test('it uses name and email', function () {
    $person = new Person(['name' => 'Ada Lovelace', 'username' => 'ada', 'email' => 'ada@example.com']);
    expect($person->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace', email: 'ada@example.com'));
});

test('it falls back to username when name is empty', function () {
    $person = new Person(['username' => 'ada_lovelace']);
    expect($person->getAvatarName())->toBe('ada lovelace');
    expect($person->getAvatarUrl())->toBe(avatar_url(name: 'ada lovelace'));
});

test('it uses an uploaded avatar', function () {
    $person = new Person(['name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'avatar' => 'https://cdn.example.com/ada.png']);
    expect($person->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace', email: 'ada@example.com', image: 'https://cdn.example.com/ada.png'));
});

test('first and last name are combined', function () {
    $person = new Person(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    expect($person->getAvatarName())->toBe('Ada Lovelace');
    expect($person->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace'));
});

test('first name only is used when last name is missing', function () {
    expect((new Person(['first_name' => 'Ada']))->getAvatarName())->toBe('Ada');
});

test('last name only is used when first name is missing', function () {
    expect((new Person(['last_name' => 'Lovelace']))->getAvatarName())->toBe('Lovelace');
});

test('display name is preferred over full name', function () {
    expect((new Person(['display_name' => 'Ada', 'full_name' => 'Ignored']))->getAvatarName())->toBe('Ada');
});

test('full name is preferred over name', function () {
    expect((new Person(['full_name' => 'Ada Lovelace', 'name' => 'Ignored']))->getAvatarName())->toBe('Ada Lovelace');
});

test('name is preferred over first and last name', function () {
    expect((new Person(['name' => 'Ada Lovelace', 'first_name' => 'Ignored']))->getAvatarName())->toBe('Ada Lovelace');
});

test('empty name falls through to username', function () {
    expect((new Person(['name' => '', 'username' => 'ada']))->getAvatarName())->toBe('ada');
});

test('email is optional', function () {
    $person = new Person(['name' => 'Ada Lovelace']);
    expect($person->getAvatarEmail())->toBe('');
    expect($person->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace'));
});

test('underscores are replaced in any name', function () {
    expect((new Person(['name' => 'ada_lovelace']))->getAvatarName())->toBe('ada lovelace');
});

test('name is empty without any name properties', function () {
    expect((new Person)->getAvatarName())->toBe('');
});

test('whitespace only name is ignored', function () {
    expect((new Person(['name' => '  ']))->getAvatarName())->toBe('');
});

test('image property is used when avatar is missing', function () {
    $person = new Person(['name' => 'Ada Lovelace', 'image' => 'https://cdn.example.com/photo.png']);
    expect($person->getAvatarImage())->toBe('https://cdn.example.com/photo.png');
    expect($person->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace', image: 'https://cdn.example.com/photo.png'));
});

test('profile picture property is used when avatar is missing', function () {
    $person = new Person([
        'name' => 'Ada Lovelace',
        'profile_picture' => 'https://cdn.example.com/profile.png',
        'image' => 'https://cdn.example.com/image.png',
    ]);
    expect($person->getAvatarImage())->toBe('https://cdn.example.com/profile.png');
});

test('profile photo path is preferred over profile picture', function () {
    $person = new Person([
        'name' => 'Ada Lovelace',
        'profile_photo_path' => 'https://cdn.example.com/photo.png',
        'profile_picture' => 'https://cdn.example.com/profile.png',
    ]);
    expect($person->getAvatarImage())->toBe('https://cdn.example.com/photo.png');
});

test('avatar is preferred over image', function () {
    $person = new Person([
        'name' => 'Ada Lovelace',
        'avatar' => 'https://cdn.example.com/avatar.png',
        'image' => 'https://cdn.example.com/image.png',
    ]);
    expect($person->getAvatarImage())->toBe('https://cdn.example.com/avatar.png');
});

test('empty avatar falls through to image', function () {
    $person = new Person(['name' => 'Ada Lovelace', 'avatar' => '', 'image' => 'https://cdn.example.com/photo.png']);
    expect($person->getAvatarImage())->toBe('https://cdn.example.com/photo.png');
});

test('get avatar name can be overridden', function () {
    $author = new class extends Person
    {
        public function getAvatarName(): string
        {
            return 'Pen Name';
        }
    };

    expect($author->getAvatarName())->toBe('Pen Name');
    expect($author->getAvatarUrl())->toBe(avatar_url(name: 'Pen Name'));
});

test('get avatar email can be overridden', function () {
    $author = new class(['name' => 'Ada Lovelace']) extends Person
    {
        public function getAvatarEmail(): string
        {
            return 'pen@example.com';
        }
    };

    expect($author->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace', email: 'pen@example.com'));
});

test('get avatar image can be overridden', function () {
    $author = new class(['name' => 'Ada Lovelace']) extends Person
    {
        public function getAvatarImage(): ?string
        {
            return 'https://cdn.example.com/photo.png';
        }
    };

    expect($author->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace', image: 'https://cdn.example.com/photo.png'));
});

test('get avatar url passes through options', function () {
    $person = new Person(['name' => 'Ada Lovelace']);
    expect($person->getAvatarUrl(size: 64, rounded: true))->toBe(avatar_url(name: 'Ada Lovelace', size: 64, rounded: true));
});

test('it defaults to the shared avatars instance', function () {
    expect((new Person)->getAvatarGenerator())->toBeNull();
});

test('it respects shared avatars configuration', function () {
    Avatars::configure(size: 256, rounded: true);
    $person = new Person(['name' => 'Ada Lovelace']);
    expect($person->getAvatarUrl())->toBe(avatar_url(name: 'Ada Lovelace'))->toContain('/256/');
});

test('a class can use a custom avatars instance', function () {
    $author = new class(['name' => 'Ada Lovelace']) extends Person
    {
        public function getAvatarGenerator(): Avatars
        {
            return new Avatars(size: 64, rounded: true);
        }
    };

    expect($author->getAvatarUrl())->toBe((new Avatars(size: 64, rounded: true))->build(name: 'Ada Lovelace'));
    expect($author->getAvatarUrl())->not->toBe(avatar_url(name: 'Ada Lovelace'));
});

test('username of only underscores is ignored', function () {
    $person = new Person(['username' => '___']);
    expect($person->getAvatarName())->toBe('');
    $person->getAvatarUrl();
})->throws(InvalidArgumentException::class, 'requires at least one');
