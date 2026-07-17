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
        $tableUsers = config('app.table.users');
        $tableName = config('app.table.data_dosen_tendiks');

        Schema::create($tableName, static function (Blueprint $table) use ($tableUsers) {
            $table->uuid('id')->primary();

            // RELASI KE AUTH (SSO)
            $table->foreignUuid('user_id')->nullable()->constrained($tableUsers)->onDelete('set null');

            // DATA KEPEGAWAIAN UTAMA
            $table->string('nik', 50)->nullable()->unique()->comment('Nomor Induk Kepegawaian Internal TSU');
            
            // DATA PRIBADI
            $table->string('nama_lengkap', 255)->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('no_hp', 25)->nullable();
            
            // PENEMPATAN DAN PERAN
            $table->string('unit_kerja', 255)->nullable();
            $table->string('jabatan_aktif', 255)->nullable();

            // DATA STATUS KEPEGAWAIAN
            $table->string('status_karyawan', 50)->nullable()->comment('TETAP dan KONTRAK');
            $table->tinyInteger('is_active')->default(1)->comment('1=Aktif, 0=Non-Aktif');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('app.table.data_dosen_tendiks');

        Schema::dropIfExists($tableName);
    }
};
