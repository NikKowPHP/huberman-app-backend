<?php

namespace App\Modules\TrackingService\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\UserManagement\Models\User;
use App\Modules\ContentManagement\Models\Protocol;
use Database\Factories\TrackingLogFactory; // Assuming you create a factory later

class TrackingLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Assuming the table name from the migration plan.
     *
     * @var string
     */
    protected $table = 'user_protocol_tracking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'protocol_id',
        'tracked_at', // The date the protocol was marked as done
        'notes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tracked_at' => 'date', // Cast to Carbon date object
        'metadata' => 'array', // Cast JSON DB column to PHP array
    ];

    /**
     * Get the user that owns the tracking log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the protocol that the tracking log belongs to.
     */
    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }

    /**
     * Create a new factory instance for the model.
     * (Optional: If you plan to use factories for testing/seeding)
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    // protected static function newFactory()
    // {
    //     return TrackingLogFactory::new();
    // }
}