<?php

namespace Spork\Core\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;
use Spork\Core\Spork;

class FeatureList extends Model
{
    use HasFactory, HasTags;

    public static $extendedRelations = [];

    protected $fillable = [
        'name',
        'feature',
        // Settings are different from normal properties. Settings should be assumed to be nullable
        // so values that are required for something to exist (like the dollar amount of a transaction)
        // should go on a dedicated model.
        'settings',
    ];

    protected $appends = ['slug'];

    protected $casts = [
        'settings' => 'json',
    ];

    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(config('spork.core.models.user'));
    }

    public function users()
    {
        return $this->belongsToMany(config('spork.core.models.user'), 'feature_list_users', 'feature_list_id', 'user_id', 'id')->withPivot(['role']);
    }

    public static function forFeature(string $feature): Builder
    {
        return static::query()
            ->where('feature', $feature);
    }

    public function getSlugAttribute(): string
    {
        return Str::slug($this->name);
    }

    public function allFeatureTypes()
    {
        return Spork::provides();
    }

    public function __call($method, $parameters)
    {
        if (isset($this::$extendedRelations[$method])) {
            $function = $this::$extendedRelations[$method];

            return Closure::bind($function, $this)(...$parameters);
        }

        return parent::__call($method, $parameters);
    }

    public static function extend(string $methodName, callable $closure)
    {
        static::$extendedRelations[$methodName] = $closure;
    }
}
