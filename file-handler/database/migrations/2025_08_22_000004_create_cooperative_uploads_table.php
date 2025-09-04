<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cooperative_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cooperative_id');
            $table->unsignedBigInteger('checklist_item_id');
            $table->foreign('cooperative_id')->references('id')->on('cooperatives')->onDelete('cascade');
            $table->foreign('checklist_item_id')->references('id')->on('checklist_items')->onDelete('cascade');
            $table->string('file_name');
            $table->string('mime_type');
            $table->binary('file_content'); // use longBlob to avoid encoding errors this is the code "ALTER TABLE cooperative_uploads MODIFY file_content LONGBLOB;"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_uploads');
    }
};
