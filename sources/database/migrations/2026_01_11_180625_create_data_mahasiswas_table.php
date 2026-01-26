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
        $tableUsers = config('auth.providers.users.table', 'users');
        $tableName = config('app.module.name', 'template');

        Schema::create($tableName . '_data_mahasiswas', static function (Blueprint $table) use ($tableUsers) {
            $table->uuid('id')->primary();
            // RELASI KE AUTH (SSO)
            $table->foreignUuid('user_id')->constrained($tableUsers)->onDelete('cascade');

            // --- DATA AKADEMIK ---
            $table->string('nim', 20)->unique();

            // --- DATA PRIBADI (Diambil dari pmb_biodata) ---
            $table->string('nik_ktp', 100)->unique()->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable(); // L/P
            $table->string('agama', 20)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->string('email_pribadi')->nullable(); // Cadangan selain email kampus
            $table->text('alamat_lengkap')->nullable();

            // --- DATA ORANG TUA ---
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('no_hp_ortu', 25)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists(config('app.module.name') . '_data_mahasiswas');
    }
};
