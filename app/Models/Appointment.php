<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $guarded = [];
    // protected $appends = ['formatted_start_time', 'formatted_end_time'];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => AppointmentStatus::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // public function formattedStartTime(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => $this->start_time->format('Y-m-d h:i A'),
    //     );
    // }

    // public function formattedEndTime(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => $this->end_time->format('Y-m-d h:i A'),
    //     );
    // }

    // public function formattedStartTime()
    // {
    //     // Logic to format the start time as needed
    //     return $this->start_time->format('Y-m-d h:i A');
    // }
    // public function formattedEndTime()
    // {
    //     // Logic to format the start time as needed
    //     return $this->end_time->format('Y-m-d h:i A');
    // }
}
