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
            $table->string('nik', 50)->nullable()->unique(); // NIK Internal TSU
            $table->string('nidn', 50)->nullable()->unique();
            $table->string('nip', 50)->nullable(); // NIP PNS
            $table->string('nuptk', 100)->nullable();
            $table->string('status_pegawai', 50)->nullable(); // TETAP, KONTRAK, LB
            $table->string('keilmuan_inti', 100)->nullable();

            // DATA PRIBADI
            $table->string('gelar_depan', 20)->nullable();
            $table->string('nama')->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            $table->string('nik_ktp', 50)->nullable();
            $table->string('no_npwp', 50)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('status_perkawinan', 30)->nullable();
            $table->tinyInteger('jumlah_anak')->default(0)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->text('alamat_lengkap')->nullable(); // Sesuai KTP
            $table->text('alamat_domisili')->nullable(); // Tempat tinggal sekarang

            // DATA JABATAN STRUKTURAL
            $table->string('jabatan_struktural')->nullable();
            $table->date('tgl_mulai_jabatan_struktural')->nullable();
            $table->string('periode_jabatan_struktural')->nullable();

            // DATA JABATAN FUNGSIONAL
            $table->string('jabatan_fungsional')->nullable(); // Asisten Ahli, Lektor, dll
            $table->string('pangkat_jabatan_fungsional')->nullable();
            $table->date('tmt_jabatan_fungsional')->nullable();
            $table->string('sk_jabatan_fungsional')->nullable(); // Nomor SK-nya

            // DOKUMEN / BERKAS DIGITAL (Menyimpan path/URL file)
            $table->text('scan_ktp')->nullable();
            $table->text('scan_kk')->nullable();
            $table->text('scan_npwp')->nullable();
            $table->text('scan_ijazah')->nullable();

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
