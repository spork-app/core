<?php

namespace Spork\Core\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Kregel\LaravelAbstract\AbstractEloquentModel;
use Kregel\LaravelAbstract\AbstractModelTrait;
use Spatie\Tags\HasTags;
use Spork\Core\Events\FeatureCreated;
use Spork\Core\Events\FeatureDeleted;
use Spork\Core\Events\FeatureUpdated;
use Spork\Core\Spork;

class FeatureList extends Model implements AbstractEloquentModel
{
    use HasFactory, HasTags, AbstractModelTrait;

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

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($item) {
            if (auth()->check()) {
                $item->user_id = auth()->id();
            }
        });

        static::created(function ($item) {
            event(new FeatureCreated($item));
        });

        static::updated(function ($item) {
            event(new FeatureUpdated($item));
        });

        static::deleted(function ($item) {
            event(new FeatureDeleted($item));
        });
    }

    public function user()
    {
        return $this->belongsTo(config('spork-core.models.user'));
    }

    public function users()
    {
        return $this->belongsToMany(config('spork-core.models.user'), 'feature_list_users')->withPivot(['role']);
    }

    public static function forFeature(string $feature): Builder
    {
        return static::query()
            ->where('feature', $feature);
    }

    public function getValidationCreateRules(): array
    {
        return [
            'name' => 'required|string',
            'feature' => [
                'required',
                'string',
                Rule::in(Spork::provides()),
            ],
            'settings' => 'nullable|array',
        ];
    }

    public function getValidationUpdateRules(): array
    {
        return [
            'name' => 'string',
            'settings' => 'array',
        ];
    }

    public function getAbstractAllowedFilters(): array
    {
        return [
            'feature',
        ];
    }

    public function getAbstractAllowedRelationships(): array
    {
        return [];
    }

    public function getAbstractAllowedSorts(): array
    {
        return [];
    }

    public function getAbstractAllowedFields(): array
    {
        return [];
    }

    public function getAbstractSearchableFields(): array
    {
        return [];
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
