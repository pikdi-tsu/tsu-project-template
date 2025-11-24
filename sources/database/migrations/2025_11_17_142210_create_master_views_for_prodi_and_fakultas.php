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
        DB::statement("
            CREATE VIEW master_prodi AS
            SELECT * FROM pmb_master_jurusankuliah
        ");

        DB::statement("
            CREATE VIEW master_fakultas AS
            SELECT * FROM pmb_master_fakultas
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS master_prodi");
        DB::statement("DROP VIEW IF EXISTS master_fakultas");
    }
};
