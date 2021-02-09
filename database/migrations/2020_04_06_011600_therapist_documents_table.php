<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TherapistDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['1', '2', '3'])->comment('1: Address Proof, 2: Identity Proof, 3: Insurance, 4: Freelancer financial document, 5: Certificates, 6: CV, 7: Reference Latter, 8: Others');
            $table->string('file_name');
            $table->bigInteger('therapist_id')->unsigned();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
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
        Schema::dropIfExists('therapist_documents');
    }
}
