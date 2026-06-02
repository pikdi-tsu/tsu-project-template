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
        $tableName = config('app.table.data_mahasiswas');

        Schema::create($tableName, static function (Blueprint $table) use ($tableUsers) {
            $table->uuid('id')->primary();
            // RELASI KE AUTH (SSO)
            $table->foreignUuid('user_id')->nullable()->constrained($tableUsers)->onDelete('cascade');

            // --- DATA AKADEMIK ---
            $table->string('nim', 20)->unique();

            // Relasi ke Master Prodi
            $table->unsignedBigInteger('id_prodi')->nullable();

            // Relasi ke Master Jenjang
            $table->unsignedBigInteger('id_jenjang')->nullable();

            // Relasi ke Waktu Kuliah (Pagi/Sore/Karyawan)
            $table->bigInteger('id_waktu_kuliah')->nullable();

            // Kolom Akademik Feeder
            $table->string('id_periode_masuk', 5)->nullable();
            $table->string('id_status_mahasiswa')->default('A');
            $table->string('jalur_masuk')->nullable();

            // --- DATA PRIBADI ---
            $table->string('nik_ktp', 100)->unique()->nullable();
            $table->string('nisn', 20)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();

            // Kolom Pribadi Feeder
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable(); // Feeder wajib L/P
            $table->integer('id_agama')->nullable(); // Feeder wajib Angka (1=Islam, dll)
            $table->char('kewarganegaraan', 2)->default('ID'); // Standar ID (Indonesia)

            $table->string('no_hp', 25)->nullable();
            $table->string('email_pribadi')->nullable();

            // --- DATA WILAYAH (Domisili) ---
            $table->char('id_provinsi', 2)->collation('utf8mb4_0900_ai_ci')->nullable();
            $table->unsignedBigInteger('id_kabupaten')->nullable();

            // Tambahan Wilayah Spesifik Feeder
            $table->char('id_wilayah', 8)->nullable();
            $table->string('kelurahan')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('kodepos', 10)->nullable();

            // --- DATA ORANG TUA & SOSIAL ---
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('no_hp_ortu', 25)->nullable();

            // Kolom Sosial Feeder
            $table->tinyInteger('penerima_kps')->default(0); // 0=Tidak, 1=Ya

            $table->timestamps();

            // --- DEFINISI FOREIGN KEY ---
//            $table->foreign('id_prodi')
//                ->references('id')->on('pmb_master_jurusankuliah')
//                ->onDelete('restrict');
//            $table->foreign('id_jenjang')
//                ->references('id')->on('pmb_master_jenjang')
//                ->onDelete('restrict');
//
//            if (Schema::hasTable('pmb_master_provinsi')) {
//                $table->foreign('id_provinsi')
//                    ->references('idprov')->on('pmb_master_provinsi')
//                    ->onDelete('set null');
//            }
//            if (Schema::hasTable('pmb_master_kabupaten')) {
//                $table->foreign('id_kabupaten')
//                    ->references('id')->on('pmb_master_kabupaten')
//                    ->onDelete('set null');
//            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        $tableName = config('app.table.data_mahasiswas');

        Schema::dropIfExists($tableName);
    }
};
