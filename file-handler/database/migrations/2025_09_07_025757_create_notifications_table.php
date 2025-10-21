<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // schedule_id -> still links to ammortization_schedules
            $table->foreignId('schedule_id')
                ->nullable()
                ->constrained('ammortization_schedules')
                ->onDelete('cascade');

            // coop_id -> must remain string to match cooperatives.id
            $table->string('coop_id')->nullable();
            $table->foreign('coop_id')
                ->references('id')
                ->on('cooperatives')
                ->onDelete('cascade');

            $table->enum('type', ['due_today', 'due_soon', 'overdue', 'due_in', 'enrolled','Has_Schedule']);

            // email fields
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();

            $table->boolean('processed')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps(); // created_at, updated_at

            // Indexes
            $table->index(['schedule_id', 'type']);
            $table->index('coop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
