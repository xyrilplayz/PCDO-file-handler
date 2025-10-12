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
         Schema::create('resolved', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coop_program_id')->constrained()->onDelete('cascade'); // reference the cooperative
            $table->binary('file_content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('resolved');
    }
};
