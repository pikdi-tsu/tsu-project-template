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
        $tableName = config('app.module.name', 'template');

        Schema::create($tableName . '_menu_sidebars', static function (Blueprint $table) use ($tableName) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->enum('type', ['item', 'header', 'divider'])->default('item');
            $table->string('route')->nullable();
            $table->string('permission_name')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('isactive')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on($tableName . '_menu_sidebars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_menu_sidebars');
    }
};
