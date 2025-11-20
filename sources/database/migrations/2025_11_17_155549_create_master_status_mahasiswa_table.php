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
        Schema::create('master_status_mahasiswa', function (Blueprint $table) {
            $table->tinyIncrements('id'); // Pakai tinyIncrements (angka 1-255) sudah lebih dari cukup

            // Kolom ini dari PDDikti (Aktif, Cuti, Lulus, DO, dll)
            $table->string('nama_status', 50)->unique();

            // Kolom "metadata" yang bisa di-update via Admin Panel
            $table->string('deskripsi')->nullable();
            $table->boolean('bisa_login')->default(true);

            // Kolom PDDikti (jika perlu mapping kode resmi)
            $table->string('kode_pddikti', 10)->nullable()->unique();
            $table->timestamps();
        });

        // [PENTING] Langsung isi datanya
        // Ini adalah data baku PDDikti yang tidak boleh dihapus
        DB::table('master_status_mahasiswa')->insert([
            ['id' => 1, 'nama_status' => 'Aktif', 'deskripsi' => 'Mahasiswa mengambil KRS', 'bisa_login' => true, 'kode_pddikti' => 'A'],
            ['id' => 2, 'nama_status' => 'Cuti', 'deskripsi' => 'Mahasiswa cuti resmi', 'bisa_login' => false, 'kode_pddikti' => 'C'],
            ['id' => 3, 'nama_status' => 'Non-Aktif', 'deskripsi' => 'Mahasiswa mangkir atau belum registrasi', 'bisa_login' => true, 'kode_pddikti' => 'N'],
            ['id' => 4, 'nama_status' => 'Lulus', 'deskripsi' => 'Sudah yudisium', 'bisa_login' => false, 'kode_pddikti' => 'L'],
            ['id' => 5, 'nama_status' => 'Drop Out', 'deskripsi' => 'Dikeluarkan dari universitas', 'bisa_login' => false, 'kode_pddikti' => 'D'],
            ['id' => 6, 'nama_status' => 'Meninggal Dunia', 'deskripsi' => 'Mahasiswa meninggal dunia', 'bisa_login' => false, 'kode_pddikti' => 'M'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_status_mahasiswa');
    }
};
