<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pending_notifications', function (Blueprint $table) {
            $table->id();

            // schedule_id -> links to ammortization_schedules
            $table->foreignId('schedule_id')
                ->constrained('ammortization_schedules')
                ->onDelete('cascade');

            // coop_id -> must be string because cooperatives.id is string
            $table->string('coop_id');
            $table->foreign('coop_id')
                ->references('id')
                ->on('cooperatives')
                ->onDelete('cascade');

            $table->enum('type', ['due_today', 'due_soon', 'overdue']);
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->boolean('processed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_notifications');
    }
};
