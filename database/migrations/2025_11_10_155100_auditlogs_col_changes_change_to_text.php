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
        Schema::table('tbl_auditlogs', function (Blueprint $table) {

             // Change 'changes' column to longText for large JSON data
            $table->longText('changes')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_auditlogs', function (Blueprint $table) {
             // Revert to original size if needed (example: VARCHAR(255))
            $table->string('changes', 255)->change();
        });
    }
};
