<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
    use HasFactory;

    protected $table = 'job_orders';

    protected $fillable = [
        'job_order_number',
        'customer_type',
        'customer_name',
        'laptop_model',
        'status',
        'pullout_date',
        'ram',
        'ssd',
        'hdd',
        'has_battery',
        'has_wifi_card',
        'others',
        'without',
    ];

    protected $casts = [
        'pullout_date' => 'date',
        'has_battery' => 'boolean',
        'has_wifi_card' => 'boolean',
    ];

    // Automatically generate a job order number before saving
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobOrder) {
            $prefix = $jobOrder->customer_type === 'technician-customer' ? 'T-' : 'C-';
            $latestJobOrder = self::where('customer_type', $jobOrder->customer_type)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = $latestJobOrder ? (intval(substr($latestJobOrder->job_order_number, 2)) + 1) : 1;
            $jobOrder->job_order_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
