<?php

use Filament\Facades\Filament;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Filament\PanelRegistry;
use Illuminate\Database\Eloquent\Model;
use SteveLacey\UiAvatars\Filament\AvatarProvider;
use SteveLacey\UiAvatars\HasAvatarUrl;

class FilamentUser extends Model
{
    protected $guarded = [];
}

class FilamentNamedUser extends Model implements HasName
{
    protected $guarded = [];

    public function getFilamentName(): string
    {
        return 'Countess Lovelace';
    }
}

class FilamentTeam extends Model
{
    use HasAvatarUrl;

    protected $guarded = [];
}

beforeEach(function () {
    app(PanelRegistry::class)->register(Panel::make()->id('admin')->default()->defaultAvatarProvider(AvatarProvider::class));
});

test('it builds avatars for models without the trait', function () {
    $user = new FilamentUser(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    expect((new AvatarProvider)->get($user))->toBe(avatar_url(name: 'Ada Lovelace', email: 'ada@example.com'));
});

test('it uses filament names', function () {
    expect((new AvatarProvider)->get(new FilamentNamedUser))->toBe(avatar_url(name: 'Countess Lovelace'));
});

test('it uses the trait when available', function () {
    $team = new FilamentTeam(['name' => 'Analytical Engines', 'avatar' => 'https://cdn.example.com/team.png']);
    expect((new AvatarProvider)->get($team))->toBe($team->avatar_url);
});

test('filament uses the provider for users', function () {
    $user = new FilamentUser(['name' => 'Ada Lovelace']);
    expect(Filament::getUserAvatarUrl($user))->toBe(avatar_url(name: 'Ada Lovelace'));
});

test('filament uses the avatar url attribute for users with the trait', function () {
    $user = new FilamentTeam(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    expect(Filament::getUserAvatarUrl($user))->toBe($user->avatar_url);
});
