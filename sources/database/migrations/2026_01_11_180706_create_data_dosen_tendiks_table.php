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
        $tableUsers = config('auth.providers.users.table');
        $tableName = config('app.module.name');

        Schema::create($tableName . '_data_dosen_tendiks', function (Blueprint $table) use ($tableUsers) {
            $table->uuid('id')->primary();
            // RELASI KE AUTH
            $table->foreignUuid('user_id')->constrained($tableUsers)->onDelete('cascade');

            // --- DATA KEPEGAWAIAN ---
            $table->string('nik')->unique(); // Nomor Induk Karyawan
            $table->string('nidn', 20)->nullable()->unique();
            $table->string('nip', 25)->nullable(); // NIP PNS (jika ada)
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->string('jabatan_fungsional')->nullable(); // Asisten Ahli, Lektor
            $table->string('status_pegawai')->nullable(); // TETAP, KONTRAK, LB

            // --- DATA PRIBADI ---
            $table->string('nik_ktp', 100)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->text('alamat_domisili')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('app.module.name') . '_data_dosen_tendiks');
    }
};
