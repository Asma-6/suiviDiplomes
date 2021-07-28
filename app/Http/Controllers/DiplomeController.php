<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Demande;
use App\Models\Diplome;
use Illuminate\Http\Request;
use App\Mail\NotificationDiplome;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DiplomeController extends Controller
{




    /**
     * Display a listing of diplomes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'diplomes' => Diplome::with('demande','etudiant')->paginate(7)
                        ->sortByDesc('date_creationDossier_envoiAuServiceDiplome')
        ]); 
    }




    /**
     * creer un diplome avec date de cretation par GuichetDroitArabe,
     *           GuichetDroitFrancais ou GuichetEconomie
     *
     * @param  int  $demande_id
     * @return \Illuminate\Http\Response
     */
    public function store($demande_id)
    {
        $demande=Demande::with('etudiant')->find($demande_id);
        $demande->statut=1;
        $demande->save();
        $statut='creé et envoyé au service diplomes';
        $diplome=Diplome::create(array(
            'demande_id' => $demande_id,
            'etudiant_cin'=>$demande->etudiant_cin,
            'statut'=>$statut,
            'date_creationDossier_envoiAuServiceDiplome'=>Carbon::today()->format('Y-m-d'),
        ));
        return response()->json([
            'diplomeCree'=> $diplome,
        ]);
    }

    /**
     * Display the specified diplome.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json([
           'diplome' => Diplome::with('demande','etudiant')->find($id)
        ]);
    }

    /**
     * Update DateImpression du diplome par service de diplomes.
     *
     * @param  \App\Models\Diplome $diplome
     * @return \Illuminate\Http\Response
     */
    public function updateDateImpression(Diplome $diplome)
    {
       // test if the specified date is null 
       if( ! $diplome->date_impression_envoiAuDecanat)
        {
            $diplome->update([
                'statut' =>'imprimé et envoyé au decanat',
                'date_impression_envoiAuDecanat' => Carbon::today()->format('Y-m-d'),
            ]);
        }
        return response()->json([
            'diplome'=> $diplome
        ]);
    }

    /**
     * Update DateSignature du diplome par decanat.
     *
     * @param  \App\Models\Diplome $diplome
     * @return \Illuminate\Http\Response
     */
    public function updateDateSignature(Diplome $diplome)
    {
        // test if the specified date is null and the previous date not null
        if( ! $diplome->date_singature_renvoiAuServiceDiplome 
            and $diplome->date_impression_envoiAuDecanat)
        {
            $diplome->update([
                'statut' => 'signé et renvoyé au service de diplomes',
                'date_singature_renvoiAuServiceDiplome' => Carbon::today()->format('Y-m-d'),
            ]);
        }
        return response()->json([
            'diplome'=> $diplome,
        ]);
    }



     /**
     * Update DateEnvoi du diplome a la presidence par service de diplomes.
     *
     * @param  \App\Models\Diplome $diplome
     * @return \Illuminate\Http\Response
     */
    public function updateDateEnvoiApresidence(Diplome $diplome)
    {
        // test if the specified date is null and the previous dates not null
        if( ! $diplome->date_generationBorodeaux_envoiApresidence 
            and $diplome->date_singature_renvoiAuServiceDiplome 
            and $diplome->date_impression_envoiAuDecanat)
        {
            $diplome->update([
                'statut' => 'envoyé à la présidence',
                'date_generationBorodeaux_envoiApresidence' => Carbon::today()->format('Y-m-d'),
            ]);
        }
        return response()->json([
            'diplome'=> $diplome,
        ]);
    }




     /**
     * Update DateReception du diplome par bureau d'ordre.
     *
     * @param  \App\Models\Diplome $diplome
     * @return \Illuminate\Http\Response
     */
    public function updateDateReceptionParBureauOrdre(Diplome $diplome)
    {
        // test if the specified date is null and the previous dates not null
        if( ! $diplome->date_receptionParBureauOrdre_envoiAuGuichetRetrait 
            and $diplome->date_generationBorodeaux_envoiApresidence 
            and $diplome->date_singature_renvoiAuServiceDiplome 
            and $diplome->date_impression_envoiAuDecanat)
        {
            $diplome->update([
                'statut' => 'recu et envoyé au ghuichet de retrait',
                'date_receptionParBureauOrdre_envoiAuGuichetRetrait' => Carbon::today()->format('Y-m-d'),
            ]);
        }
        return response()->json([
            'diplome'=> $diplome,
        ]);
    }




     /**
     * Update DateRetrait du diplome et envoi du dossier au arhives par guichet de retrait.
     *
     * @param  \App\Models\Diplome $diplome
     * @return \Illuminate\Http\Response
     */
    public function updateDateRetraitDiplomeArchiveDossier(Diplome $diplome)
    {
        // test if the specified date is null and the previous dates not null
        if( ! $diplome->date_retraitDiplome_archiveDossier 
            and $diplome->date_notificationEtudiant
            and $diplome->date_receptionParBureauOrdre_envoiAuGuichetRetrait 
            and $diplome->date_generationBorodeaux_envoiApresidence 
            and $diplome->date_singature_renvoiAuServiceDiplome 
            and $diplome->date_impression_envoiAuDecanat)
        {
            $diplome->update([
                'statut' => 'diplome retiré et dossier archivé',
                'date_retraitDiplome_archiveDossier' => Carbon::today()->format('Y-m-d'),
            ]);
        }
        return response()->json([
            'diplome'=> $diplome,
        ]);
    }
    




     /**
     * Search diplome by cin, cne or pogee
     *
     * @param  string $mc
     * @return \Illuminate\Http\Response
     */
    public function search($mc)
    {
        $res = array();
        $diplomes = DB::table('diplomes as dip')
                        ->join('etudiants as e', 'dip.etudiant_cin','=','e.cin')
                        ->join('demandes as d', 'dip.demande_id','=','d.id')
                        ->where('e.cin', 'like', '%'.$mc.'%')
                        ->orWhere('e.cne', 'like', '%'.$mc.'%')
                        ->orWhere('e.apogee', 'like', '%'.$mc.'%')
                        ->paginate(7)
                        ->sortByDesc('date_creationDossier_envoiAuServiceDiplome');

        // filter searched diplomes by statut for each role
        if(Auth::user()->hasRole('admin')) {
            $res = $diplomes;
        } elseif(Auth::user()->hasRole('guichet_droit_arabe|guichet_droit_francais|guichet_economie')) {
            foreach ( $diplomes as $diplome ) 
            {
                if($diplome->statut == 'creé et envoyé au service diplomes') 
                {
                    $res[] = $diplome;
                }
            }    
        } elseif(Auth::user()->hasRole('service_diplomes')) {
            foreach ( $diplomes as $diplome ) 
            {
                if($diplome->statut == 'creé et envoyé au service diplomes' or 
                   $diplome->statut == 'imprimé et envoyé au decanat' or 
                   $diplome->statut == 'signé et renvoyé au service de diplomes' or 
                   $diplome->statut == 'envoyé à la présidence') 
                {
                    $res[] = $diplome;
                }
            }    
        } elseif(Auth::user()->hasRole('decanat')) {
            foreach ( $diplomes as $diplome ) 
            {
                if($diplome->statut == 'imprimé et envoyé au decanat' or 
                   $diplome->statut == 'signé et renvoyé au service de diplomes') 
                {
                    $res[] = $diplome;
                }
            }    
        } elseif(Auth::user()->hasRole('bureau_ordre')) {
            foreach ( $diplomes as $diplome ) 
            {
                if($diplome->statut == 'envoyé à la présidence' or 
                   $diplome->statut == 'recu et envoyé au ghuichet de retrait') 
                {
                    $res[] = $diplome;
                }
            }  
        } elseif(Auth::user()->hasRole('guichet_retrait')) {
            foreach ( $diplomes as $diplome ) 
            {
                if($diplome->statut == 'recu et envoyé au ghuichet de retrait' or 
                   $diplome->statut == 'diplome retiré et dossier archivé') 
                {
                    $res[] = $diplome;
                }
            }
        }
        
        return response()->json([
            'diplomes' => $res
        ]);
    }

    /**
     * filtrer les diplomes selon leur statut
     *
     * @param  string $statut
     * @return \Illuminate\Http\Response
     */
    public function filterByStatut($statut)
    {
        return response()->json([
            'diplomes' => Diplome::with('demande','etudiant')
                       ->where('statut',$statut)
                       ->paginate(7)
                       ->sortByDesc('date_creationDossier_envoiAuServiceDiplome')  
         ]);
    }

    /**
     * Notify etudent by sending notif to his email with update date_notificationEtudiant
     *
     * @param  int $id_diplome
     * @return \Illuminate\Http\Response
     */
    public function sendMAil($id_diplome)
    {
        $diplome = Diplome::with('etudiant','demande')->find($id_diplome);
        $mail=[
            'object' => 'Notification de diplôme',
            'body' => 'Bonjour '.$diplome->etudiant->nom.' '.$diplome->etudiant->prenom.',  Votre ' .$diplome->demande->type_demande. ' est prêt, 
                       vous pous pouvez venir pour le récupérer auprès du guichet de retrait des diplômes dans un délai de 3 jours au maximum!',
        ];

        // test if the specified date is null and the previous dates not null
        if( ! $diplome->date_notificationEtudiant
            and $diplome->date_receptionParBureauOrdre_envoiAuGuichetRetrait 
            and $diplome->date_generationBorodeaux_envoiApresidence 
            and $diplome->date_singature_renvoiAuServiceDiplome 
            and $diplome->date_impression_envoiAuDecanat)
        {
            $diplome->update([
                'date_notificationEtudiant' => Carbon::today()->format('Y-m-d'),
            ]);
            // notify etudiant
            // Mail::to($diplome->etudiant->email_inst)->send(new NotificationDiplome($mail));
            Mail::to('gouzasalma@gmail.com')->send(new NotificationDiplome($mail));

            return response()->json([
                'response' => 'email sent to '.$diplome->etudiant->email_inst,
            ]);
        }

        return response()->json([
            'response' => 'cannot send email!',
        ]);

        
    }

}
