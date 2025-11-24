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
        Schema::create('siakad_mahasiswa', function (Blueprint $table) {

            $table->id();
            $table->string('nim', 20)->unique();
            $table->unsignedBigInteger('id_prodi')->nullable();
            $table->string('nik_wali_dosen', 50)->nullable();
            $table->unsignedTinyInteger('id_status_mahasiswa')->default(1); // Default 'Aktif' (ID 1)
            $table->string('angkatan', 4);
            $table->string('photo_profile')->nullable();
            $table->string('nama_lengkap');
            $table->string('nik_ktp', 20)->unique();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->text('alamat_asal')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('no_hp_ortu', 25)->nullable();
            $table->text('alamat_domisili')->nullable();
            $table->timestamps();

            // ---- Definisi Foreign Keys ----

            // 1. Ke Auth Mahasiswa (Homebase)
            $table->foreign('nim')
                ->references('nim')->on('users_mahasiswa')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            // 2. Ke Master Prodi (via VIEW)
            $table->foreign('id_prodi')
                ->references('id')->on('master_prodi') // Mengarah ke VIEW
                ->onDelete('set null');

            // 3. Ke Auth Dosen (Dosen Wali)
            $table->foreign('nik_wali_dosen')
                ->references('nik')->on('users_dosen_tendik')
                ->onDelete('set null');

            // ---- FOREIGN KEY BARU ----
            // 4. Ke Master Status Mahasiswa
            $table->foreign('id_status_mahasiswa')
                ->references('id')->on('master_status_mahasiswa')
                ->onDelete('restrict'); // Jangan biarkan status dihapus jika masih dipakai
            // --------------------------
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siakad_mahasiswa');
    }
};
