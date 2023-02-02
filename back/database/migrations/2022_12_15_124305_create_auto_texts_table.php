<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id');
            $table->foreignId('user_id');
            $table->string('text');
            $table->string('suggested_codes')->nullable();
            $table->string('report_types')->nullable();
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
        Schema::dropIfExists('auto_texts');
    }
};
