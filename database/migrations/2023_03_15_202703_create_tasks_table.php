<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 50);
            $table->string('description');
            $table->string('slug')->nullable();
            $table->foreignUuid('project_id');
            $table->foreignUuid('assignee_id')->nullable();
            $table->smallInteger('difficulty');
            $table->string('priority');
            $table->string('status');
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table){
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('CASCADE');

            $table->foreign('assignee_id')
                ->references('id')
                ->on('users')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
