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

        Schema::create('activiteiten', function (Blueprint $table) {
            $table->increments('id');
            $table->string('naam', 255);
            $table->text('beschrijving')->nullable();
            $table->unsignedBigInteger('gebruiker_id');
            $table->foreign('gebruiker_id')->references('id')->on('users');
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
        Schema::dropIfExists('activiteiten');
    }
};
