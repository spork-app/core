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
        Config::set('spork.cores.enabled', true);

        Spork::addFeature('cores', 'icon', '/path', 'default', []);

        Event::assertDispatched(FeatureRegistered::class);

        $this->assertSame([
            'cores' => [
                'name' => 'Cores',
                'slug' => 'cores',
                'icon' => 'icon',
                'path' => '/path',
                'group' => 'default',
                'provides' => [],
                'enabled' => true,
            ],
        ], Spork::features());

        $this->assertSame(['core'], Spork::provides());

        $this->assertTrue(Spork::hasFeature('cores'));
    }

    public function testDoesntMakeFeatureAvailable()
    {
        Event::fake();
        Config::set('spork.core.enabled', false);

        Spork::addFeature('cores', 'icon', '/path', 'default', []);

        Event::assertDispatched(FeatureRegistered::class);

        $this->assertSame([
            'cores' => [
                'name' => 'Cores',
                'slug' => 'cores',
                'icon' => 'icon',
                'path' => '/path',
                'group' => 'default',
                'provides' => [],
                'enabled' => false,
            ],
        ], Spork::features());

        $this->assertSame(['core'], Spork::provides());

        $this->assertFalse(Spork::hasFeature('cores'));
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
                    'tags' => [],
                ],
            ],
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
                    'tags' => [],
                ],
            ],
        ], Spork::$actions);

        $response = $this->postJson('/api/route-app', [

        ]);

        $response->assertStatus(200);
    }
}
