<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etud extends Model
{
    use HasFactory;
    protected $fillable = ['cin', 
    'apogee',
    'cne',
    'nom',
    'prenom',
    'nom_arabe',
    'prenom_arabe',
    'filiere',
    'option',
    'nationalite',
    'date_naiss',
    'lieu_naiss',
    'email_inst',
    ];
    

    public function demande(){
        return $this->hasMany(Demande::class, 'etudiant_cin', 'cin');
    }
    public function diplome(){
        return $this->hasMany(Diplome::class, 'etudiant_cin', 'cin');
    }
}
