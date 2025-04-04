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
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_order_number')->unique();
            $table->enum('customer_type', ['customer', 'technician-customer']);
            $table->string('customer_name');
            $table->string('customer_address')->nullable();
            $table->string('problem');
            $table->string('contact_number'); // 👈 Added here after customer_name
            $table->string('laptop_model');
            $table->timestamp('date_created')->useCurrent();
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Unrepairable/pullout', 'Completed/claimed'])->default('Pending');
            $table->date('pullout_date')->nullable();

            // Parts
            $table->text('ram')->nullable(); // Example: "16GB DDR4"
            $table->text('ssd')->nullable(); // Example: "512GB M.2 NVMe"
            $table->text('hdd')->nullable(); // Example: "1TB 2.5 SATA"
            $table->boolean('has_battery')->default(false);
            $table->boolean('has_wifi_card')->default(false);

            $table->text('others')->nullable();
            $table->text('without')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
