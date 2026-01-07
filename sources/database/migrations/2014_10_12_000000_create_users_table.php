<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('auth.providers.users.table', 'users');

        Schema::create($tableName, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tsu_homebase_id')->nullable()->unique()->index()->comment('ID User asli dari TSU Homebase');
            $table->bigInteger('username')->unique()->nullable()->comment('Username berisi NIM/NIK dari TSU Homebase');
            $table->bigInteger('nidn')->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Password harus null
            $table->string('password')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('unit')->nullable();

            // Token SSO
            $table->text('sso_access_token')->nullable()->comment('Token untuk request API ke Homebase');
            $table->text('sso_refresh_token')->nullable();

            $table->tinyInteger('isactive')->default(1)->comment('1=Aktif, 0=Non-Aktif');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = config('auth.providers.users.table', 'users');
        Schema::dropIfExists($tableName);
    }
}
