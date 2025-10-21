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
        Schema::create('coop_details', function (Blueprint $table) {
            $table->string('coop_id')->primary();
            $table->foreign('coop_id')
                ->references('id')
                ->on('cooperatives')
                ->onDelete('cascade');

            // ✅ Make nullable foreign key correctly
            $table->foreignId('municipality_id')
                ->nullable()
                ->constrained('municipalities')
                ->nullOnDelete();

            $table->enum('asset_size', ['Micro', 'Small', 'Medium', 'Large', 'Unclassified'])->nullable();
            $table->enum('coop_type', [
                'Credit',
                'Consumers',
                'Producers',
                'Marketing',
                'Service',
                'Multipurpose',
                'Advocacy',
                'Agrarian Reform',
                'Bank',
                'Diary',
                'Education',
                'Electric',
                'Financial',
                'Fishermen',
                'Health Services',
                'Housing',
                'Insurance',
                'Water Service',
                'Workers',
                'Others'
            ])->nullable();
            $table->enum('status_or_category', ['Reporting', 'Non-Reporting', 'New'])->nullable();
            $table->enum('bond_of_membership', ['Residential', 'Insitutional', 'Associational', 'Occupational', 'Unspecified'])->nullable();
            $table->enum('area_of_operation', ['Municipal', 'Provincial'])->nullable();
            $table->enum('citizenship', ['Filipino', 'Others'])->nullable();
            $table->bigInteger('members_count')->nullable();
            $table->bigInteger('total_asset')->nullable();
            $table->bigInteger('net_surplus')->nullable();
            $table->string('number')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coop_details');
    }
};