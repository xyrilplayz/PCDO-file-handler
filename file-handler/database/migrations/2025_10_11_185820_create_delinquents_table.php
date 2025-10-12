<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delinquents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coop_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('ammortization_schedule_id')
                ->nullable()
                ->constrained('ammortization_schedules')
                ->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->date('date_paid')->nullable();
            $table->string('status')->default('Delinquent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delinquents');
    }
};
