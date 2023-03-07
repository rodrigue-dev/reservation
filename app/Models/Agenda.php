<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{

    use HasFactory;
    protected $table="agenda";
    protected $fillable = [
        'date_jour',
        'libelle_jour',
        'heure_debut',
        'heure_fin',
        'type_jour',
        'reservations',
    ];
}
