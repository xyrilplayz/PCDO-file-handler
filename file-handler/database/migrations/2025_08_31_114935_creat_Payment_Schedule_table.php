<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->date('due_date');
            $table->decimal('amount_due', 12, 2);
            $table->boolean('is_paid')->default(false);
            $table->date('paid_at')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0); // Track actual paid
            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};

?>