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
        Schema::disableForeignKeyConstraints();

        Schema::create('lesmomenten', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('activiteit_id');
            $table->foreign('activiteit_id')->references('id')->on('activiteiten');
            $table->dateTime('start_tijd');
            $table->dateTime('eind_tijd');
            $table->unsignedInteger('max_deelnemers');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesmomenten');
    }
};
