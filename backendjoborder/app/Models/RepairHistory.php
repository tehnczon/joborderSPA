<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairHistory extends Model
{
    protected $fillable = [
        'job_order_id',
        'action_type',
        'description',
        'performed_by',
        'cost',
        'performed_at',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'performed_at' => 'datetime',
    ];

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class);
    }
}