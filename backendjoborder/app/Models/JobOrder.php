<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\JobOrderImage;

class JobOrder extends Model
{
    use HasFactory;

    protected $table = 'job_orders';

    protected $fillable = [
        'job_order_number',
        'customer_type',
        'customer_name',
        'contact_number',
        'customer_address',
        'problem',
        'date_created',
        'laptop_model',
        'status',
        'pullout_date',
        'pulled_out_by',
        'ram',
        'ssd',
        'hdd',
        'has_battery',
        'has_wifi_card',
        'others',
        'without',
        // New pricing fields - will be ignored if columns don't exist
        'repair_price',
        'parts_cost',
        'labor_cost',
        'repair_notes',
    ];

    protected $casts = [
        'pullout_date' => 'date',
        'has_battery' => 'boolean',
        'has_wifi_card' => 'boolean',
        'ram' => 'array',
        'ssd' => 'array',
    ];

    // Relationships
    public function images()
    {
        return $this->hasMany(JobOrderImage::class);
    }

    // Only add repair history relationship if the model exists
    public function repairHistories()
    {
        if (class_exists('App\Models\RepairHistory')) {
            return $this->hasMany(\App\Models\RepairHistory::class)->orderBy('performed_at', 'desc');
        }
        return null;
    }

    // Boot method for auto-generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobOrder) {
            // Only generate if not already set
            if (empty($jobOrder->job_order_number)) {
                $prefix = $jobOrder->customer_type === 'technician-customer' ? 'T' : '';
                $latestJobOrder = self::where('customer_type', $jobOrder->customer_type)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestJobOrder) {
                    if ($prefix) {
                        $lastNumber = intval(substr($latestJobOrder->job_order_number, 1));
                    } else {
                        $lastNumber = intval($latestJobOrder->job_order_number);
                    }
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                $jobOrder->job_order_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // Format for display
    public function toArray()
    {
        $array = parent::toArray();
        
        // Format dates safely
        if (isset($array['created_at'])) {
            $array['created_at'] = Carbon::parse($this->created_at)->format('Y-m-d H:i:s');
        }
        if (isset($array['updated_at'])) {
            $array['updated_at'] = Carbon::parse($this->updated_at)->format('Y-m-d H:i:s');
        }
        if (isset($array['pullout_date']) && $this->pullout_date) {
            $array['pullout_date'] = Carbon::parse($this->pullout_date)->format('Y-m-d');
        }
        
        // Add default values for pricing if columns don't exist
        if (!isset($array['repair_price'])) {
            $array['repair_price'] = 0;
        }
        if (!isset($array['parts_cost'])) {
            $array['parts_cost'] = 0;
        }
        if (!isset($array['labor_cost'])) {
            $array['labor_cost'] = 0;
        }
        
        return $array;
    }
}