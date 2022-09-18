<?php

namespace Spork\Core;

use Spork\Core\Events\Spork\ActionRegistered;
use Spork\Core\Events\Spork\AssetPublished;
use Spork\Core\Events\Spork\FeatureRegistered;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

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

            $basename = basename($file, '.php');

            if ($basename === 'ActionInterface') {
                continue;
            }

            if (stripos($contents, 'class '.$basename) === false) {
                continue;
            }

            preg_match('/namespace\s+(.*);/', $contents, $matches);

            $class = $matches[1].'\\'.$basename;

            $instance = new $class;

            $action = [
                'name' => $instance->getName(),
                'url' => $instance->getUrl(),
                'tags' => $instance->tags(),
            ];
            $actions[$feature][] = $action;
            Route::post($instance->getUrl(), $class);
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

    public static function fabricateWith ($componentPath) {
        if (!is_array($componentPath)) {
            $componentPath = [$componentPath];
        }
        $components = [];
        foreach ($componentPath as $path) {
            $paths = array_filter(scandir($path), fn ($path) => in_array($path, ['.', '..']) === false);
            foreach ($paths as $newPath) {
                preg_match('/export default {(.*\n)+}/', file_get_contents($fullPath = $path.'/'.$newPath), $matches);

                $script = escapeshellarg(str_replace('export default ', 'console.log(JSON.stringify(', $matches[0]). '));');
            
                exec("node -e ".$script, $output, $code);
                
                $component = json_decode($output[0] ?? '{}', true);

                $components[$fullPath] = $component;
                
            }
        }
        self::$components = array_merge(self::$components, $components);
    }

    public static function provides(): array
    {
        return array_reduce(static::$features, function ($provides, $feature) {
            return array_merge($provides, $feature['provides'] ?? []);
        }, []);
    }
}
