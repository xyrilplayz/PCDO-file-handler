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
        Schema::create('coop_program_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coop_program_id')->constrained('coop_programs')->onDelete('cascade');
            $table->foreignId('program_checklist_id')->constrained('program_checklists')->onDelete('cascade');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->binary('file_content')->nullable(); // use longBlob to avoid encoding errors this is the code "ALTER TABLE cooperative_uploads MODIFY file_content LONGBLOB;"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coop_program_checklists');
    }
};
