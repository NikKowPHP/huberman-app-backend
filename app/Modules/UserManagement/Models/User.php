<?php

namespace App\Modules\UserManagement\Models;

// Import the correct factory namespace
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture_url', // Added previously
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deleted_at' => 'datetime', // Added by SoftDeletes trait
    ];

    /**
     * Create a new factory instance for the model.
     *
     * Explicitly point to the correct factory class location.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return UserFactory::new(); // Tell Laravel to use Database\Factories\UserFactory
    }

    // Define relationships here if they exist (as tested in UserTest)
    public function subscriptions()
    {
        // Example: return $this->hasMany(Subscription::class); // Replace with actual Subscription model if/when it exists
        // For the test to pass without the actual model, we need a placeholder:
        return $this->hasMany(\App\Modules\SubscriptionBilling\Models\Subscription::class); // Placeholder for test
    }

    public function notes()
    {
        // Example: return $this->hasMany(Note::class); // Replace with actual Note model
        return $this->hasMany(\App\Modules\NotesService\Models\Note::class); // Placeholder for test
    }

    public function reminders()
    {
         // Example: return $this->hasMany(Reminder::class); // Replace with actual Reminder model
        return $this->hasMany(\App\Modules\ProtocolEngine\Models\UserReminder::class); // Placeholder for test
    }

    public function trackingLogs() // Matches test name
    {
        // Example: return $this->hasMany(TrackingLog::class); // Replace with actual TrackingLog model
        return $this->hasMany(\App\Modules\ProtocolEngine\Models\TrackingLog::class); // Placeholder for test
    }
}
