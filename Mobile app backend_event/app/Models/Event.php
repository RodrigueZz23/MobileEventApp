<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Reservation;

class Event extends Model
{
    use HasFactory;

     protected $fillable = [
        'picture',
        'type',
        'title',
        'date',
        'localisation',
        'business',
        'price',
        'categorie',
        'rating',
        'description',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }


}


