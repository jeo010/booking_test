<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'ticket_id',
        'quantity',
        'status',
    ];

    public function user(){
        return $this->belongsTo(\App\Models\User::class);
    }

    public function ticket(){
        return $this->belongsTo(\App\Models\Ticket::class);
    }
}
