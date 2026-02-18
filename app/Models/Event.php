<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'created_by',
        'title',
        'description',
        'date',
        'location',
    ];

    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function tickets(){
        return $this->hasMany(\App\Models\Ticket::class);
    }
}
