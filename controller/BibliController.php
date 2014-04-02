<?php
namespace controller ;
use utils\HttpRequest;

class BibliController extends Controller {
	protected static $_listActions=array('detailLivre' => 'detailLivre',
										'detailLivre_isbn' => 'detailLivre_isbn',
										'recherche' => 'recherche',
										'emprunt' => 'empruntLivre',
										'retour' => 'rendreLivre',
										'inscriptionForm' => 'inscriptionForm',
										'inscription' => 'inscription',
										'accueil' => 'accueil',
										'listeEmprunt' => 'listeEmprunt',
										'changeEmail' => 'changeEmail',
										'rechercheAv' => 'rechercheAv'
                                         );

	public function __construct () {
    
    }
 
    public function defaultAction(array $request) {
        $this->afficheLivres($request);
    }

    public function afficherLivres(  array $request ) {
        
        echo "BibliController :: afficherLivres";
        var_dump($request);
    }

	public function detailLivre(  array $request ) {
		$livre = \model\Livre::where('idLivre', '=',$request[0])->get();
		$indisponible = $this->getEtat($livre);//array();
		
		//Récolte des mots-clés du livre
		//Select Mots.libelle from mots, mots_livre where mots.idMot = mots_livre.idMot and idLivre = $request[0])->get()
		//$mots = \model\MotsLivre::where('idLivre', '=',$request[0])->get();
		$mots = \model\MotsLivre::leftJoin('Mots', function ($join) {
				$join->on('Mots.idMot', '=', 'mots_livre.idMot');
				})->select('Mots.libelle')->whereRaw('idLivre ='. $request[0])->get();
					
		$v = new \view\DetailView('templates/css/bootstrap.css', $livre[0], $request[1], $indisponible, $mots) ;
        $v->display();
    }

    public function detailLivre_isbn(  array $request ) {
		$livre = \model\Livre::where('isbn', '=',$request[0])->get();
		$indisponible = $this->getEtat($livre);//array();
		
		//Récolte des mots-clés du livre
		//Select Mots.libelle from mots, mots_livre where mots.idMot = mots_livre.idMot and idLivre = $request[0])->get()
		//$mots = \model\MotsLivre::where('idLivre', '=',$request[0])->get();
		$mots = \model\MotsLivre::leftJoin('Mots', function ($join) {
				$join->on('Mots.idMot', '=', 'mots_livre.idMot');
				})->select('Mots.libelle')->whereRaw('idLivre ='.$livre[0]->idLivre)->get();
					
		$v = new \view\DetailView('templates/css/bootstrap.css', $livre[0], $request[1], $indisponible, $mots) ;
	    $v->display();
    }
	
	public function inscriptionForm(  array $request ) {
		$v = new \view\InscriptionView('templates/css/bootstrap.css', $request[0], $request[1]) ;
	    $v->display();
    }
	
	public function inscription(  array $request ) {
			//Vérification si l'utilisateur n'est pas déjà inscrit
			$exist = \model\Users::where('nom', '=',$request[0]['login'])->get();
			if(!isset($exist[0])){ //Si il n'est pas dans la base alors
				$User = new \model\Users();
				$User->nom = $request[1];
				$User->prenom = htmlspecialchars($request[0]['nom']);
				$User->droit = "C";
				$User->mail = htmlspecialchars($request[0]['email']);
				$User->save();	
			}
    }
	
	public function changeEmail(  array $request ) {
			$request[1]->mail = htmlspecialchars($request[0]['email']);
			$request[1]->save();	
    }
	
	public function recherche(  array $request ) {
        //Récupération du type d'affichage
		$tri = (isset($request[0]['tri']) == 1 ?$request[0]['tri']:"1");
		$text = (isset($request[0]['text']) == 1 ?$request[0]['text']:"");
		$SortType = "";
		switch ($tri)
		{
			case 1:
			default:
				$SortType = "annee";
			break;
			
			case 2:
				$SortType = "DateAjout";
			break;
			
			case 3:
				$SortType = "titre";
			break;
		}	
		
		//récupération des mots clés		
		$listeMot = array();
		if ($text != "") {
			$escape = ',';
			$chaineMot = $text;
			if ($chaineMot[strlen($chaineMot)-1] != $escape) {
				$chaineMot = $chaineMot.$escape;
			}
			$iPos = strpos($chaineMot, $escape);
			while ($iPos !== false) {
				$listeMot[] = substr($chaineMot, 0, $iPos);
				$chaineMot = substr($chaineMot, $iPos+1, strlen($chaineMot));
				$iPos = strpos($chaineMot, $escape);
			}
		}
		
		$listeLivre = array();
		$chaineLivre = "-1";
		//Parcours des mots clé (mots clés, isbn, titre)
		for ($i = 0; $i < count($listeMot); $i++) {
			//titre/isbn
			/*$livre = \model\Livre::whereRaw('idLivre NOT IN ('.$chaineLivre.') AND (titre like %'.$listeMot[$i].'% OR isbn like '.$listeMot[$i].')')->get();
			for ($j = 0; $j < count($livre); $j++) {
				$listeLivre[] = $livre[$i]->idLivre;
				$chaineLivre = $chaineLivre.','.$livre[$i]->idLivre;
			}*/
			//mots clée
			//$livre = \model\MotsLivre::with('mots')->get(); print_r($livre);
			//$livre = \model\MotsLivre::with('mots')->whereRaw('idLivre NOT IN ('.$chaineLivre.') AND libelle like "%'.$listeMot[$i].'%"')->get();
			$livre = \model\MotsLivre::leftJoin('Mots', function ($join) {
				$join->on('Mots.idMot', '=', 'mots_livre.idMot');
				})->select('idLivre')->whereRaw('idLivre NOT IN ('.$chaineLivre.') AND libelle like "%'.$listeMot[$i].'%"')->get();
			for ($j = 0; $j < count($livre); $j++) {
				$listeLivre[] = $livre[$j]['idLivre'];
				$chaineLivre = $chaineLivre.','.$livre[$j]['idLivre'];
			}
		}
		
		//Récupération du mode d'affichage
		$SortMode = (isset($request[0]['mode']) == 1 ?'DESC':'ASC');
		//Récupération des livres		
		$livres = \model\Livre::whereRaw('idLivre IN ('.$chaineLivre.')')->orderBy($SortType, $SortMode)->get();
		$indisponible = $this->getEtat($livres);
		
		$idUser = \model\Users::where('nom', 'like',$request[1])->first()->idUser;
		
		$v = new \view\RechercheView('/BibliApp_dev/templates/stylesheets/screen.css', $livres, $indisponible, $idUser, $request[2]) ;
        $v->display();
    }
	
	public function empruntLivre(  array $request ) {
		//Contruction redirection
		$monUrl = $_SERVER['SCRIPT_NAME']; 
		$monUrl = substr_replace($monUrl, '', -9);
		$redirection = explode('BibliApp/', $_SERVER['HTTP_REFERER'], 2);
		$monUrl = $monUrl.$redirection[1];
		
		//Verifier si le livre est disponible
		$pEmprunt = \model\Emprunt::whereRaw('idLivre = '.$request[1].' AND dateRetour IS NULL')->get();
		if (count($pEmprunt) == 0) {
			$emprunt = new \model\Emprunt();
			$emprunt->idLivre = $request[1];
			
			$idUser = \model\Users::where('nom', 'like', $request[0])->get();
			
			$emprunt->idUser = $idUser[0]['idUser'];
			$emprunt->dateEmprunt = date("Y-m-d H:i:s");
			$emprunt->save();
			
			//Envoi sur l'accueil??			
			//$this->accueil($request);
			echo '<script type="text/javascript"> alert("livre emprunté!");</script>';
			$request[2]->redirect($monUrl);
		}
		else {
			//Envoi sur l'accueil??	
			$this->accueil($request);
			echo '<script type="text/javascript"> alert("livre déjà emprunté!");</script>';
			$request[2]->redirect($monUrl);
		}
    }
	
	public function rendreLivre(  array $request ) {
		//Contruction redirection
		$monUrl = $_SERVER['SCRIPT_NAME']; 
		$monUrl = substr_replace($monUrl, '', -9);
		$redirection = explode('BibliApp/', $_SERVER['HTTP_REFERER'], 2);
		$monUrl = $monUrl.$redirection[1];
		
		$idUser = \model\Users::where('nom', 'like',$request[0])->first()->idUser;
		$pEmprunt = \model\Emprunt::whereRaw('idLivre = '.$request[1].' AND dateRetour IS NULL AND idUser = '.$idUser)->get();
		if (count($pEmprunt) != 0) {
			$pEmprunt[0]->dateRetour = date("Y-m-d H:i:s");
			$pEmprunt[0]->save();
			
			//Envoi sur l'accueil??			
			$request[2]->redirect($monUrl);
			echo '<script type="text/javascript"> alert("livre rendu!");</script>';
		}
		else{
			//Envoi sur l'accueil??	
			$request[2]->redirect($monUrl);
			echo '<script type="text/javascript"> alert("Vous n\'avez pas emprunté ce livre!");</script>';
		}
    }
	
	public function accueil(  array $request ) {
        $livres = \model\Livre::orderBy('dateAjout', 'asc')->get();
		$indisponible = $this->getEtat($livres);//array();
		//print_r($livres[0]['Discipline']->nom);
		$idUser = \model\Users::where('nom', 'like',$request[0])->first()->idUser;
        $v = new \view\AccueilView('templates/css/bootstrap.css', $livres, $indisponible, $idUser, $request[1]) ;
        $v->display();
    }
	
	public function listeEmprunt(  array $request ) {
    	//  à revoir
       	$idUser = \model\Users::where('nom', 'like',$request[0])->first()->idUser;
       	$emprunt = \model\Emprunt::whereRaw('idUser = '.$idUser.' AND dateRetour IS NULL')->get();
       	for ($i = 0; $i < count($emprunt); $i++) {
       		$livre[] = \model\Livre::where('idLivre','=',$emprunt[$i]['idLivre'])->get();
       		$discipline[] = \model\Discipline::where('idDiscipline','=',$livre[$i][0]['idDiscipline'])->get();
			
		}     	
		$v = new \view\empruntUserView('',$emprunt ,$idUser, $livre,$discipline,$request[1]) ;
        $v->display();

    }

    public function rechercheAv(  array $request ) {
		//Récupération de l'utilisateur
		$idUser = \model\Users::where('nom', 'like',$request[1])->first()->idUser;
		//Récupération des différentes disciplines pour les affecter dans le template
		$tabDiscipline = \model\Discipline::all();
		$options = array();
		foreach ($tabDiscipline as $dis) {
			$options[] = $dis;
		}
		
		//Récupération des différents champs à traiter
		$titre = (isset($request[0]['titre']) ?$request[0]['titre']:'');
		$auteur = (isset($request[0]['auteur']) ?$request[0]['auteur']:'');
		$isbn = (isset($request[0]['isbn']) && $request[0]['isbn']!= '' ?'= '.$request[0]['isbn']:'IS NOT NULL ');
		$discipline = (isset($request[0]['discipline']) && $request[0]['discipline']!= '' ?'= '.$request[0]['discipline']:'IS NOT NULL ');
		$edition = (isset($request[0]['edition']) ?$request[0]['edition']:'');
		$mots = (isset($request[0]['mots']) ?$request[0]['mots']:'');
		//Pour envoyer une page vide
		//!! Utiliser directement le liens pour voir ce qu'il contient ( = BibliApp_dev/rechercheAv)
		if ($titre != "" || $auteur != "" || $isbn != "IS NOT NULL " || $discipline != "IS NOT NULL " || $edition != ""|| $mots != "") {
			$where = 'titre like "%'.$titre.'%" and auteur like "%'.$auteur.'%" and isbn '.$isbn.' and idDiscipline '.$discipline;
			$where.= ' and Edition.nom like "%'.$edition.'%"';
			
			//Extraction des différents mots
			if ($mots != "") {
				$escape = ',';
				$chaineMot = $mots;
				if ($chaineMot[strlen($chaineMot)-1] != $escape) {
					$chaineMot = $chaineMot.$escape;
				}
				$iPos = strpos($chaineMot, $escape);
				$listeMot = array();
				$where.= ' and ';
				while ($iPos !== false) {
					$where.= 'libelle like "%'.substr($chaineMot, 0, $iPos).'%"';
					$chaineMot = substr($chaineMot, $iPos+1, strlen($chaineMot));
					$iPos = strpos($chaineMot, $escape);
					$where.= ' or ';
				}
				$where = substr($where, 0, strlen($where)-4);
			}
			
			$livre = \model\Livre::leftJoin('mots_livre', function ($join){
					$join->on('mots_livre.idLivre', '=', 'Livre.idLivre');
					})->leftJoin('Mots', function ($join) {
						$join->on('Mots.idMot', '=', 'mots_livre.idMot');
					})->leftJoin('Edition', function ($join) use ($edition) {	
						$join->on('Edition.idEdition', '=', 'Livre.idEdition');
					})->select('Livre.*')->distinct()->whereRaw($where)->get();
		}
		else {
			$livre = new \Illuminate\Database\Eloquent\Collection();
		}
		$indisponible = $this->getEtat($livre);
		
        $v = new \view\RechAvView('templates/css/bootstrap.css', $livre, $indisponible, $idUser, $request[2], $options) ;
        $v->display();
    }
	
	//Passe une liste de livres en paramètre, renvoi la liste équivalente avec pour chaque entrée
	//le champ texte null ou un objet de type Emprunt
	private function getEtat(\Illuminate\Database\Eloquent\Collection $livres) {
		$indisponible = array();
		for ($i = 0; $i < count($livres); $i++) {
			$livres[$i]->discipline;
			$emprunt = \model\Emprunt::whereRaw('idLivre = '.$livres[$i]->idLivre.' AND dateRetour IS NULL')->get();
			$indisponible[] = (count($emprunt) > 0 ? $emprunt[0] : "null");
		}
		return $indisponible;
	}

}
?>