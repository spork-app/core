<?php

namespace Spork\Core\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Spork\Core\Events\Spork\AssetPublished;
use Spork\Core\Events\Spork\FeatureRegistered;
use Spork\Core\Spork;
use Spork\Core\Tests\TestCase;

class SporkTest extends TestCase
{
    public function testAddFeatureFiresFeatureRegisteredEvent()
    {
        Event::fake();

        Spork::addFeature('core', 'icon', '/path', 'default', []);

        Event::assertDispatched(FeatureRegistered::class);

        $this->assertSame([
            'core' => [
                'name' => 'Core',
                'slug' => 'core',
                'icon' => 'icon',
                'path' => '/path',
                'enabled' => false,
                'group' => 'default',
                'provides' => [],
            ]
        ], Spork::$features);
    }

    public function testLoadWithAddsAsitGetsCalledAndWontDuplicateValues()
    {
        $this->assertSame([], Spork::$loadWith);
        Spork::loadWith(['hello']);
        $this->assertSame(['hello'], Spork::$loadWith);
        Spork::loadWith(['world']);
        $this->assertSame(['hello', 'world'], Spork::$loadWith);
        Spork::loadWith(['world']);
        $this->assertSame(['hello', 'world'], Spork::$loadWith);
    }
    public function testPublish()
    {
        Event::fake();

        Spork::publish('css', '/app/app.css');

        Event::assertDispatched(AssetPublished::class);

        $this->assertSame([
            'css' => [
                '/app/app.css',
            ],
            'js' => [],
        ], Spork::$assets);
    }
}