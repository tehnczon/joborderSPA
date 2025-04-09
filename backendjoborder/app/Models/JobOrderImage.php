<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrderImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_order_id',
        'path',
    ];

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class);
    }
}
