<?php

namespace App\Modules\SubscriptionBilling\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'interval',
        'interval_count',
        'trial_period_days',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
