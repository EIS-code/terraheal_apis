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
            $table->enum('type', ['1', '2', '3', '4','5','6','7','8','9','10'])->comment('1: Address Proof, 2: Identity Proof Front, 3: Identity Proof Back, 4: Insurance, 5: Freelancer financial document, 6: Certificates, 7: CV, 8: Reference Latter, 9: Personal experience, 10: Others');
            $table->string('file_name');
            $table->text('description')->nullable();
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
