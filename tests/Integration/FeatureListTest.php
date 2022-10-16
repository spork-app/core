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
        config([
            'spork.core.models.user' => TestUser::class,
        ]);
    }

    public function testFeatureCreatedEventIsLaunched()
    {
        Event::fake();
        $user = TestUser::factory()->create();

        $this->actingAs($user)->postJson('/api/core/feature-list', [
            'name' => 'Test feature',
            'feature' => 'core',
            'settings' => [],
        ]);

        $this->actingAs($user)->putJson('/api/core/feature-list/1', [
            'name' => 'A feature',
        ]);

        $this->actingAs($user)->deleteJson('/api/core/feature-list/1');

        Event::assertDispatched(FeatureCreated::class);
        Event::assertDispatched(FeatureUpdated::class);
        Event::assertDispatched(FeatureDeleted::class);
    }

    public function testFeatureCreatedStoresUserIfLoggedIn()
    {
        $user = TestUser::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/core/feature-list', [
            'name' => 'Test feature',
            'feature' => 'core',
            'settings' => [],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'user_id' => $user->id,
        ]);
    }

    public function testFeatureUsersCantShareAccessIfUserDoestExist()
    {
        $user = TestUser::factory()->create();
        $feature = FeatureList::factory()->create([
            'feature' => 'core',
            'user_id' => $user->id,
        ]);
        $response = $this->actingAs($user)->postJson('api/core/share', [
            'feature_list_id' => $feature->id,
            'email' => 'user@fake.tools',
        ]);

        $response->assertStatus(412);
    }

    public function testFeatureUsersCanShareAccess()
    {
        $user = TestUser::factory()->create();
        $userToShareWith = TestUser::factory()->create([
            'email' => 'user@fake.tools',
        ]);
        $feature = FeatureList::factory()->create([
            'feature' => 'core',
            'user_id' => $user->id,
        ]);
        $response = $this->actingAs($user)->postJson('api/core/share', [
            'feature_list_id' => $feature->id,
            'email' => 'user@fake.tools',
        ]);

        $response->assertStatus(200);

        $this->assertSame([

        ], $userToShareWith->features()->get()->toArray());
    }

    public function testExtendingForDynamicRelationshipsWork()
    {
        FeatureList::extend('featureUsers', fn () => $this->belongsToMany(TestUser::class, 'feature_list_users', 'feature_list_id', 'user_id'));

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
