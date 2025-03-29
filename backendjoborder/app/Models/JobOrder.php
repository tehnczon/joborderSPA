<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JobOrder extends Model
{
    use HasFactory;

    protected $table = 'job_orders';

    protected $fillable = [
        'job_order_number',
        'customer_type',
        'customer_name',
        'contact_number', // Added contact_number
        'customer_address',
        'problem',
        'date_created', // Automatically managed by Laravel
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
        'ram' => 'array',
        'ssd' => 'array',
    ];

    // Automatically generate a job order number before saving
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobOrder) {
            $prefix = $jobOrder->customer_type === 'technician-customer' ? 'T-' : '';
            $latestJobOrder = self::where('customer_type', $jobOrder->customer_type)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = $latestJobOrder ? (intval(substr($latestJobOrder->job_order_number, 2)) + 1) : 1;
            $jobOrder->job_order_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }

    public function toArray()
{
    return array_merge(parent::toArray(), [
        'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
        'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
    ]);
}
}
