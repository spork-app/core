<?php

namespace Spork\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Spork\Core\Events\Spork\AssetPublished;
use Spork\Core\Events\Spork\FeatureRegistered;
use Spork\Core\Spork;
use Spork\Core\Tests\TestCase;

class SporkTest extends TestCase
{
    public function setUp(): void 
    {
        parent::setUp();
        Spork::reset();
    }

    public function testAddFeatureFiresFeatureRegisteredEvent()
    {
        Event::fake();
        Config::set('spork.core.enabled', true);

        Spork::addFeature('core', 'icon', '/path', 'default', []);

        Event::assertDispatched(FeatureRegistered::class);

        $this->assertSame([
            'core' => [
                'name' => 'Core',
                'slug' => 'core',
                'icon' => 'icon',
                'path' => '/path',
                'enabled' => true,
                'group' => 'default',
                'provides' => [],
            ],
        ], Spork::$features);

        $this->assertSame([], Spork::provides());

        $this->assertTrue(Spork::hasFeature('core'));
    }

    public function testDoesntMakeFeatureAvailable()
    {
        Event::fake();
        Config::set('spork.core.enabled', false);

        Spork::addFeature('core', 'icon', '/path', 'default', []);

        Event::assertDispatched(FeatureRegistered::class);

        $this->assertSame([
            'core' => [
                'name' => 'Core',
                'slug' => 'core',
                'icon' => 'icon',
                'path' => '/path',
                'enabled' =>false,
                'group' => 'default',
                'provides' => [],
            ],
        ], Spork::$features);

        $this->assertSame([], Spork::provides());

        $this->assertFalse(Spork::hasFeature('core'));
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

    public function testPublishSavesAssetsToSpork()
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

    public function testPublishCanReturnString()
    {
        Event::fake();

        Spork::publish('css', '/app/app.css');

        Event::assertDispatched(AssetPublished::class);

        $this->assertSame([
            '/app/app.css',
        ], Spork::publish('css'));
    }

    public function testActionsCanRegisterEverything()
    {
        Event::fake();

        $this->assertSame([], Spork::$actions);

        Spork::actions('core', __DIR__);

        $this->assertSame([
            'core' => [
                [
                    'name' => 'Fake Action',
                    'url' => '/api/route-app',
                    'tags' => []
                ]
            ]
        ], Spork::$actions);
    }

    public function testActionsCanRegisterAndTheRouteExists()
    {
        Event::fake();

        $this->assertSame([], Spork::$actions);

        Spork::actions('core', __DIR__);

        $this->assertSame([
            'core' => [
                [
                    'name' => 'Fake Action',
                    'url' => '/api/route-app',
                    'tags' => []
                ]
            ]
        ], Spork::$actions);

        $response = $this->postJson('/api/route-app', [
            
        ]);

        $response->assertStatus(200);
    }
}
