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
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('issue');
            $table->unsignedInteger('pl')->nullable();
            $table->json('communicator')->nullable();
            $table->json('programmer')->nullable();
            $table->json('designer')->nullable();
            $table->json('reviewer')->nullable();
            $table->string('ticket_link');
            $table->json('related_links')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('time_used')->nullable();
            $table->boolean('isActive')->default(true);
            $table->boolean('isAssign')->default(false);
            $table->unsignedInteger('creator');
            $table->unsignedInteger('updater');
            $table->timestamps();
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
