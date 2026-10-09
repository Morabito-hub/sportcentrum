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

        Schema::create('wachtlijst', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('gebruiker_id');
            $table->foreign('gebruiker_id')->references('id')->on('users');
            $table->unsignedInteger('lesmoment_id');
            $table->foreign('lesmoment_id')->references('id')->on('lesmomenten');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['gebruiker_id', 'lesmoment_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wachtlijst');
    }
};
