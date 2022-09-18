<?php

namespace Spork\Core\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Userable extends Pivot
{
    protected $table = 'userable';

    protected $fillable = [
        'settings',
        'role',
    ];

    protected $casts = [
        'settings' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
