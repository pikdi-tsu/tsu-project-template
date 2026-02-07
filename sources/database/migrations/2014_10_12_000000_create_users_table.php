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
            // --- IDENTITAS SSO ---
            $table->uuid('sso_id')->nullable()->unique()->index()->comment('ID SSO dari TSU Homebase');
            $table->string('username')->unique()->nullable()->comment('Berisi NIM atau NIK dari Homebase');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar_url', 2048)->nullable();
            $table->string('unit')->nullable();
            $table->text('sso_access_token')->nullable()->comment('Token untuk request API ke Homebase');
            $table->text('sso_refresh_token')->nullable();
            $table->tinyInteger('isactive')->default(1)->comment('1=Aktif, 0=Non-Aktif');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', static function (Blueprint $table) {
            $table->string('id')->primary();
//            $table->foreignId('user_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('auth.providers.users.table'));
    }
}
