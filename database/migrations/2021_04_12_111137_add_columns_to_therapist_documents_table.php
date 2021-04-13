<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTherapistDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_documents', function (Blueprint $table) {
            $table->string('doc_name')->after('id')->nullable();
            $table->enum('is_expired',['0','1'])->default('0')->comment('0: Nope, 1: Yes')->after('therapist_id');
            $table->date('expired_date')->after('is_expired')->nullable();
            $table->bigInteger('uploaded_by')->unsigned()->nullable()->after('expired_date');
            $table->foreign('uploaded_by')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_documents', function (Blueprint $table) {
            $table->dropColumn('doc_name');
            $table->dropColumn('is_expired');
            $table->dropColumn('expired_date');
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn('uploaded_by');
        });
    }
}
