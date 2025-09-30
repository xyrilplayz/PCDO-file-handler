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
        Schema::create('coop_program_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coop_program_id')->constrained()->onDelete('cascade');
            $table->string('title'); // short title for report
            $table->text('description')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->longText('file_content')->nullable(); // or use storage path instead
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coop_program_progress');
    }
};
