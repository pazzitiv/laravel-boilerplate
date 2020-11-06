<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('users_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('fk_group_id')->constrained('groups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users_groups');
    }
}
