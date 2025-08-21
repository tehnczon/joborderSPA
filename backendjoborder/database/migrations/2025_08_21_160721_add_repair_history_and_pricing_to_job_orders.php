<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add pricing columns to job_orders if they don't exist
        Schema::table('job_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('job_orders', 'repair_price')) {
                $table->decimal('repair_price', 10, 2)->nullable()->after('problem');
                $table->decimal('parts_cost', 10, 2)->nullable()->after('repair_price');
                $table->decimal('labor_cost', 10, 2)->nullable()->after('parts_cost');
                $table->text('repair_notes')->nullable()->after('labor_cost');
            }
        });

        // Create repair_histories table if it doesn't exist
        if (!Schema::hasTable('repair_histories')) {
            Schema::create('repair_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_order_id');
                $table->string('action_type');
                $table->text('description');
                $table->string('performed_by')->nullable();
                $table->decimal('cost', 10, 2)->nullable();
                $table->timestamp('performed_at');
                $table->timestamps();

                $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['repair_price', 'parts_cost', 'labor_cost', 'repair_notes']);
        });
        
        Schema::dropIfExists('repair_histories');
    }
};