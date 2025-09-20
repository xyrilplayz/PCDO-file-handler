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
        Schema::create('coop_details', function (Blueprint $table) {
            $table->string('coop_id')->primary();
            $table->foreign('coop_id')->references('id')->on('cooperatives')->onDelete('cascade');
            $table->foreignId('municipality_id')->constrained('municipalities')->onDelete('cascade');
            $table->enum('asset_size', ['Micro', 'Small', 'Medium', 'Large', 'Unclassified']);
            $table->enum('coop_type', ['Credit', 'Consumers', 'Producers', 'Marketing', 'Service', 'Multipurpose', 'Advocacy', 'Agrarian Reform', 'Bank', 'Diary', 'Education', 'Electric', 'Financial', 'Fishermen', 'Health Services', 'Housing', 'Insurance', 'Water Service', 'Workers', 'Others']);
            $table->enum('status_or_category', ['Reporting', 'Non-Reporting', 'New']);
            $table->enum('bond_of_membership', ['Residential', 'Insitutional', 'Associational', 'Occupational', 'Unspecified']);
            $table->enum('area_of_operation',['Municipal', 'Provincial']);
            $table->enum('citizenship', ['Filipino', 'Others']);
            $table->bigInteger('members_count');
            $table->bigInteger('total_asset');
            $table->bigInteger('net_surplus');            
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