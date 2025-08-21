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
        // Add repair_price to existing job_orders table
        Schema::table('job_orders', function (Blueprint $table) {
            $table->decimal('repair_price', 10, 2)->nullable()->after('problem');
            $table->decimal('parts_cost', 10, 2)->nullable()->after('repair_price');
            $table->decimal('labor_cost', 10, 2)->nullable()->after('parts_cost');
            $table->text('repair_notes')->nullable()->after('labor_cost');
        });

        // Create repair_histories table
        Schema::create('repair_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_order_id');
            $table->string('action_type'); // 'diagnosis', 'repair', 'part_replaced', 'testing', 'completed'
            $table->text('description');
            $table->string('performed_by')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
            $table->index(['job_order_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['repair_price', 'parts_cost', 'labor_cost', 'repair_notes']);
        });
        
        Schema::dropIfExists('repair_histories');
    }
};