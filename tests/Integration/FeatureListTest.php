<?php

namespace Spork\Core\Tests\Integration;

use Illuminate\Support\Facades\Event;
use Spork\Core\Events\FeatureCreated;
use Spork\Core\Events\FeatureDeleted;
use Spork\Core\Events\FeatureUpdated;
use Spork\Core\Models\FeatureList;
use Spork\Core\Tests\TestCase;
use Spork\Core\Tests\TestUser;
use Spork\Core\Tests\Traits\UseTestBenchDatabase;

class FeatureListTest extends TestCase
{
    use UseTestBenchDatabase;

    public function setUp(): void
    {
        parent::setUp();
        FeatureList::$extendedRelations = [];
    }
    
    public function testFeatureCreatedEventIsLaunched()
    {
        $this->expectsEvents([
            FeatureCreated::class,
            FeatureUpdated::class,
            FeatureDeleted::class,
        ]);
        $feature = FeatureList::factory()->create([
            'feature' => 'core',
        ]);

        $feature->update(['name' => 'Hello world']);

        $feature->delete();
    }

    public function testFeatureCreatedStoresUserIfLoggedIn()
    {
        auth()->login($user = TestUser::factory()->create());
        $feature = FeatureList::factory()->create([
            'feature' => 'core',
        ]);
        auth()->logout();

        $this->assertNotNull($user->id);
        $this->assertSame($user->id, $feature->user->id);
    }

    public function testFeatureUsersCanShareAccess()
    {   
        $feature = FeatureList::factory()->create([
            'feature' => 'core',
        ]);

        $response = $this->postJson('api/core/share', [
            'feature_list_id' => $feature->id,
            'email' => 'user@fake.tools'
        ]);

        $response->assertStatus(204);
    }

    public function testExtendingForDynamicRelationshipsWork()
    {
        FeatureList::extend('featureUsers', fn () => $this->belongsToMany(config('spork-core.models.user'), 'feature_list_users', 'feature_list_id', 'user_id'));
        $feature = FeatureList::factory()->create([
            'feature' => 'core',
        ]);

        $testUser = TestUser::factory()->create();

        $feature->featureUsers()->sync([$testUser->id]);

        $featureWithUsers = FeatureList::with('featureUsers')->first();

        $this->assertSame($feature->id, $featureWithUsers->id);
        $this->assertCount(1, $featureWithUsers->featureUsers);
    }
}