<?php

namespace Spork\Core;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Spork\Core\Contracts\ActionInterface;
use Spork\Core\Events\Spork\ActionRegistered;
use Spork\Core\Events\Spork\AssetPublished;
use Spork\Core\Events\Spork\FeatureRegistered;

class Spork
{
    use Macroable;

    public static $assets = [
        'css' => [],
        'js' => [],
    ];

    public static $features = [];

    public static $actions = [];

    public static $components = [];

    public static $loadWith = [];

    public const NO_NEW_FEATURES = [];

    // This method should only be called in tests
    public static function reset()
    {
        static::$assets = [
            'css' => [],
            'js' => [],
        ];
        static::$features = [];
        static::$actions = [];
        static::$components = [];
        static::$loadWith = [];
    }

    public static function addFeature(string $featureName, string $icon, string $path, string $group = 'default', array $availableFeatures = [])
    {
        self::$features[Str::slug($featureName)] = [
            'name' => Str::title($featureName),
            'slug' => Str::slug($featureName),
            'icon' => $icon,
            'path' => $path,
            'enabled' => config('spork.'.Str::slug($featureName).'.enabled', false),
            'group' => $group,
            'provides' => $availableFeatures,
        ];

        event(new FeatureRegistered($featureName, $icon, $path, config('spork.'.Str::slug($featureName).'.enabled', false)));
    }

    public static function loadWith(array $relationships = [])
    {
        return static::$loadWith = array_values(array_unique(
            array_merge(static::$loadWith, $relationships)
        ));
    }

    public static function publish(string $type, string $asset = null)
    {
        if (empty($asset)) {
            return static::$assets[$type];
        }

        static::$assets[$type][] = $asset;
        event(new AssetPublished($type, $asset));
    }

    public static function actions(string $feature, string $path)
    {
        $feature = Str::slug($feature);

        $actions = [
            $feature => [],
        ];

        foreach (glob($path.'/*.php') as $file) {
            $contents = file_get_contents($file);

            $basename = str_replace('.php', '', basename($file));

            preg_match('/namespace\s+(.*);/', $contents, $matches);

            $class = $matches[1].'\\'.$basename;


            $instance = new $class;
            if (! ($instance instanceof ActionInterface)) {
                continue;
            }

            $action = [
                'name' => $instance->name(),
                'url' => $instance->route(),
                'tags' => $instance->tags(),
            ];

            $actions[$feature][] = $action;
            Route::post($instance->route(), $class);
            event(new ActionRegistered($feature, $action));
        }

        static::$actions = array_merge(
            static::$actions,
            $actions
        );

        return static::$actions;
    }

    public static function hasFeature(string $featureName)
    {
        $slug = Str::slug($featureName);

        return isset(self::$features[$slug]) && self::$features[$slug]['enabled'];
    }

    public static function provides(): array
    {
        return array_reduce(static::$features, function ($provides, $feature) {
            return array_merge($provides, $feature['provides'] ?? []);
        }, []);
    }
}
