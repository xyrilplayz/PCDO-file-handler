<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pending_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('payment_schedules')->onDelete('cascade');
            $table->foreignId('coop_id')->constrained('cooperatives')->onDelete('cascade');
            $table->enum('type', ['before_due','due_today','overdue']);
            $table->timestamp('created_at')->useCurrent();
            $table->boolean('processed')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_notifications');
    }
};
