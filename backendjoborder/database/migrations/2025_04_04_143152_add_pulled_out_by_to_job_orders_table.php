<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('job_orders', function (Blueprint $table) {
        $table->string('pulled_out_by')->nullable()->after('pullout_date');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            //
        });
    }
};
