<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_lists', function (Blueprint $table) {
            $table->id();
            $table->string('feature')->index();
            $table->string('name');
            $table->json('settings');

            $table->foreignIdFor(config('spork-core.models.user'), 'user_id');
            $table->timestamps();
        });
        Schema::create('feature_list_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(config('spork-core.models.feature_list'), 'feature_list_id');
            $table->foreignIdFor(config('spork-core.models.user'), 'user_id');
            $table->string('role')->default('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feature_lists');
        Schema::dropIfExists('feature_list_users');
    }
}
