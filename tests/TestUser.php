<?php

namespace Spork\Core\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class TestUser extends Authenticatable
{
    use HasFactory, Notifiable;

    public $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected function profilePhotoDisk()
    {
        return 'public';
    }

    public function features()
    {
        return $this->hasMany(FeatureList::class);
    }
}
