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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable();
            $table->foreignId('organization_id');
            $table->foreignId('clinic_id');
            $table->foreignId('appointment_type_id')->nullable();
            $table->foreignId('specialist_id')->index();
            $table->foreignId('room_id')->nullable();
            $table->foreignId('anesthetist_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->boolean('is_wait_listed')->default(false);
            $table
                ->enum('procedure_approval_status', [
                    'NOT_ASSESSED',
                    'NOT_APPROVED',
                    'APPROVED',
                    'NOT_RELEVANT',
                    'CONSULT_REQUIRED'
                ])
                ->default('NOT_ASSESSED');
            $table
                ->enum('confirmation_status', [
                    'PENDING',
                    'CONFIRMED',
                    'CANCELED',
                    'MISSED',
                ])
                ->default('PENDING');
            $table->text('confirmation_status_reason')->nullable();
            $table
                ->enum('attendance_status', [
                    'NOT_PRESENT',
                    'WAITING',
                    'CHECKED_IN',
                    'CHECKED_OUT',
                ])
                ->default('NOT_PRESENT');
            $table->date('date');
            $table->time('arrival_time');
            $table->time('start_time');
            $table->time('end_time');
            $table
                ->enum('charge_type', [
                    'self-insured',
                    'private-health-excess',
                    'private-health-excess-0',
                    'private-health-pension',
                    'pension-card',
                    'healthcare-card',
                    'department-veteran',
                    'work-cover',
                    'transport-accident',
                ])
                ->default('self-insured');
            $table->text('note')->nullable();
            $table->string('collecting_person_name')->nullable();
            $table->string('collecting_person_phone')->nullable();
            $table->string('collecting_person_alternate_contact')->nullable();
            $table->boolean('draft_status')->nullable();
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
        Schema::dropIfExists('appointments');
    }
};
