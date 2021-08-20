<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\ExportEtudiants;
use App\Exports\ExportParcoursDetaille;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EtudiantController extends Controller
{   

    /**
     * Display a listing of etudiants.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $etudiants=Etudiant::with('demande','diplome')->get();
        return response()->json([
            'etudiants' => $etudiants,
        ]); 
    }

    /**
     * Display the specified etdiant.
     *
     * @param string $cin
     * @return \Illuminate\Http\Response
     */
    public function show($cin)
    {
        $etudiant = Etudiant::with('demande','diplome')->where('cin',$cin)->first();

         return response()->json([
            'etudiant' => $etudiant
         ]);
    }

    /**
     * Update the specified etudiant in storage
     *
     * @param Illuminate\Http\Request $request
     * @param string $cin
     * @return \Illuminate\Http\Response 
     */
    public function update(Request $request, $cin)
    {
        Etudiant::with('demande','diplome')->where('cin',$cin)->update($request->all()); 
        return response()->json([
            'etudiant' => Etudiant::with('demande','diplome')->where('cin',$request->cin)->first()
        ]);
    }
//  /**
//      * search etudiant by CNE,CIN or APPOGE
//      *
//      * @param string $mc
//      * @return \Illuminate\Http\Response 
//      */
//     function search($mc = '')
//     {
//         $etudiantsList = array();
//         $etudiants = Etudiant::with('demande','diplome')
//                     ->where('cin', 'like', '%'.$mc.'%')
//                     ->orWhere('cne', 'like', '%'.$mc.'%')
//                     ->orWhere('apogee', 'like', '%'.$mc.'%')
//                     ->get();
//         foreach($etudiants as $etudiant){
//             $etudiant=[
//                 'id' => $etudiant->diplome->id,
//                 'cin' => $etudiant->cin,
//                 'apogee' => $etudiant->apogee,
//                 'cne' => $etudiant->cne,
//                 'nom' => $etudiant->etudiant->nom,
//                 'prenom' => $etudiant->prenom,
//                 'filiere' => $etudiant->filiere,
//                 'type_demande' => $etudiant->demande->type_demande,

//             ];
//             array_push($etudiantsList,$etudiant);
//         }
//         return response()->json([
 
//             'diplomes' =>$etudiantsList

//         ]); 

//     }

    /**
     * filter etudiant by filiere
     *
     * @param string $filiere
     * @return \Illuminate\Http\Response 
     */
    public function filterByFiliere($filiere)
    {
        return response()->json([
            'etudiants' => Etudiant::with('demande','diplome')->where('filiere',$filiere)->paginate(7)
        ]);
        
    }

    /**
     * @param string $type
     * @param string $filiere
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function exportEtudiants($type, $filiere) 
    {
        $prefix = Str::random(1);
        return Excel::download(new ExportEtudiants($type, $filiere),
                $prefix.'_etudiants_'.$filiere.'_'.$type.'.xlsx');
    }

    /**
     * @param string $statut
     * @param string $type
     * @param string $filiere
     * @return Maatwebsite\Excel\Facades\Excel
     */
    public function exportParcoursDetaille($statut, $type, $filiere) 
    {
        $prefix = Str::random(1);
        return Excel::download(new ExportParcoursDetaille($statut, $type, $filiere),
                $prefix.'_parcours_detaille_'.$filiere.'_'.$type.'.xlsx');
    }

}