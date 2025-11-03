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
        Schema::create('ammortization_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coop_program_id')->constrained('coop_programs')->onDelete('cascade');
            $table->date('due_date');
            $table->integer('installment');
            $table->dateTime('date_paid')->nullable();
            $table->decimal('amount_paid', 15, 2)->nullable();
            $table->enum('status', ['Unpaid','Partial Paid', 'Paid', 'Near Due', 'Overdue', 'Resolved'])->default('Unpaid');
            $table->decimal('penalty_amount', 15, 2)->nullable();
            $table->decimal('balance', 15, 2)->nullable();
            $table->string('notes')->nullable();
            $table->binary('receipt_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ammortization_schedules');
    }
};
