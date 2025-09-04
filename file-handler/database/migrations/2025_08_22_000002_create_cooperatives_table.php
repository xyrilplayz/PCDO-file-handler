<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('with_grace')->default(4);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cooperatives');
    }
};
