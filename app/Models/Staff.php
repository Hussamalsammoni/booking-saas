<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

   protected $fillable = [
    'user_id',
    'title',
    'working_hours',
    'is_active',
    'can_view_all_bookings',
];

protected $casts = [
    'working_hours' => 'array',
    'is_active' => 'boolean',
    'can_view_all_bookings' => 'boolean',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
{
    return $this->belongsToMany(Service::class, 'staff_service');
}

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}