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
            $table->string('nidn', 50)->nullable()->unique()->comment('Nomor Induk Dosen Nasional');
            $table->string('nip', 50)->nullable()->comment('Nomor Induk Pegawai PNS');
            
            // DATA PRIBADI
            $table->string('gelar_depan', 20)->nullable();
            $table->string('nama')->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('no_hp', 25)->nullable();
            
            // DATA JABATAN STRUKTURAL
            $table->string('jabatan_struktural')->nullable()->comment('Dekan, Wakil Dekan, Kaprodi, dll');
            
            // DATA JABATAN FUNGSIONAL
            $table->string('jabatan_fungsional')->nullable()->comment('Asisten Ahli, Lektor, dll');
            $table->string('pangkat_jabatan_fungsional')->nullable()->comment('Pangkat Golongan');

            // Department
            $table->string('unit')->nullable();

            // DATA STATUS KEPEGAWAIAN
            $table->string('status_karyawan', 50)->nullable()->comment('TETAP, KONTRAK, LB, DLL');
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
