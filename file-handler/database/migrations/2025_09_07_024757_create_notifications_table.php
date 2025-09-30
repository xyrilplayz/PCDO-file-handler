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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->string('coop_id');
            $table->foreign('coop_id')
                  ->references('id')
                  ->on('cooperatives')
                  ->onDelete('cascade');
            $table->enum('type', ['before_due', 'due_today', 'overdue']);
            $table->timestamp('created_at')->useCurrent();
            $table->boolean('processed')->default(0);

            // Optional: if you want to keep track when it was archived
            $table->timestamp('archived_at')->useCurrent();

            // Indexes
            $table->index(['schedule_id', 'type']);
            $table->index('coop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
