<?php

namespace Spork\Core\Models;

use App\Models\User;
use Closure;
use Spork\Core\Events\FeatureCreated;
use Spork\Core\Events\FeatureDeleted;
use Spork\Core\Events\FeatureUpdated;
use Spork\Core\Spork;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\Rule;
use Kregel\LaravelAbstract\AbstractEloquentModel;
use Kregel\LaravelAbstract\AbstractModelTrait;
use Spatie\Tags\HasTags;

/**
 * Class FeatureList
 *
 * @mixin Model
 */
class FeatureList extends Model implements AbstractEloquentModel
{
    use HasFactory, HasTags, AbstractModelTrait;

    public static $extendedRelations = [];

    protected $fillable = [
        'name',
        'feature',
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

        static::addGlobalScope('user', function (Builder $builder) {
            if (! auth()->check()) {
                return;
            }

            $builder->where('user_id', auth()->id())
                ->orWhereHas('users', function (Builder $builder) {
                    $builder->where('user_id', auth()->id());
                });
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'feature_list_users', )->withPivot(['role']);
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
            'settings.body' => 'nullable|string',
            'settings.links' => 'array|min:0',
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
        return ['repeatable.users.user', 'notes', 'accounts', 'conditionals'];
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
