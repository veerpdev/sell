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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id');
            $table->string('title');
            $table->enum('type', ['LETTER', 'REPORT', 'CLINICAL_NOTE', 'PATHOLOGY_REPORT', 'REFERRAL', 'AUDIO', 'USB_CAPTURE', 'OTHER']);
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
        Schema::dropIfExists('document_templates');
    }
};
