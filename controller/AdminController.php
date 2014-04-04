<?php
namespace controller ;
use utils\HttpRequest;

class AdminController extends Controller {
	protected static $_listActions=array('choixAdmin' => 'choixAdmin',
										'ajoutLivre' => 'ajoutLivre',
										'saisieISBN' => 'saisieISBN',
										'recapitulatif' => 'recapitulatif',
										'formImportation' => 'formImportation',
										"import" => "import",
										"export" => "export",
										'suivi' => 'suivi',
										'statistiques' => 'statistiques',
										'modificationForm' => 'modificationForm',
										'modification' => 'modification',
										'relance' => 'relance',
										'relanceDate' =>'relanceDate',
										'supressionLivre' =>'supressionLivre'
                                         );

	public function __construct () {
    
    }
    
    public function defaultAction(array $request) {
        $this->afficheLivres($request);
    }

    public function afficherLivres(  array $request ) {
        
        echo "PictoController :: displayAllCommand<br>";
        var_dump($request);
    }
	
	
	public function choixAdmin(  array $request ) {
        
        $v = new \view\ChoixAdminView('/BibliApp_dev/templates/stylesheets/screen.css', $request[0]) ;
        $v->display();

    }
	
	public function saisieISBN(  array $request ) {
        
        echo "AdminController :: saisieISBN";

    }
	
	public function recapitulatif(  array $request ) {
		//Affichage d'un message
		if (isset($request[0])){
			$message = $request[0];
		}
		Else{
			$message = "";
		}
		
        //Récolte des disciplines
		$disciplines = \model\Discipline::all();
        $v = new \view\RecapitulatifView('templates/stylesheets/styles.css', $disciplines, $message, $request[1]) ;
        $v->display();
    }
	
	public function modificationForm(  array $request ) {
		$livre = \model\Livre::find($request[1]);
        $v = new \view\ModificationView('templates/stylesheets/styles.css', $livre, $request[0]) ;
        $v->display();
    }
	
	public function modification(  array $request ) {
		$Livre = \model\Livre::find($request[1]);
		//Si image inférieure à 1 mo ou pas d'image
		if(intval($_FILES['fichier']['size']) < 1048576 || intval($_FILES['fichier']['size']) == 0){
			//Si l'image est valide (.jpg, .jpeg ou .gif) ou pas d'image
			if (substr($_FILES['fichier']['name'], -4) == '.jpg' || substr($_FILES['fichier']['name'], -4) == '.gif' || substr($_FILES['fichier']['name'], -5) == '.jpeg' || intval($_FILES['fichier']['size']) == 0){	
					//Upload d'une image---------------------------------------------------------------------------------------------
					$filename = basename($_FILES['fichier']['name']);
					if(file_exists('../upload/'. $filename)){
						$data = "0"; //Si le fichier existe déjà on renvoie une erreur
					}
					//Si l’upload a réussi et que le fichier est correctement posé sur le serveur
					else if (move_uploaded_file($_FILES['fichier']['tmp_name'], '../upload/'.$filename)) {
						$data = "1"; //le retour sera à  1      
					}
					//Si l’upload du fichier à  échoué
					else {
						$data = "2"; //La valeur de retour sera à  0
					}
				
					//Enregistrement du livre
					$Livre->description =  htmlspecialchars($request[0]['resume']);
					$Livre->auteur =  htmlspecialchars($request[0]['auteur']);
					$Livre->langue =  htmlspecialchars($request[0]['langue']);
					$Livre->annee = $request[0]['publication'];
					//S'il y a eu un upload
					if ($data == 1){
						$Livre->couverture = $filename ;
					}

					$Livre->save();	
					
					//Renommage de de la couverture
					if ($data == 1){	
						rename('../upload/'.$Livre->couverture,'../upload/'. $Livre->idLivre .'.jpg');
						$Livre->couverture = $Livre->idLivre .'.jpg' ;
						$Livre->save();
					}
				
			}
		}
    }
	
	public function ajoutLivre(  array $request ) {
		//Si image inférieure à 1 mo ou pas d'image
		if(intval($_FILES['fichier']['size']) < 1048576 || intval($_FILES['fichier']['size']) == 0){
			//Si l'image est valide (.jpg, .jpeg ou .gif) ou pas d'image
			if (substr($_FILES['fichier']['name'], -4) == '.jpg' || substr($_FILES['fichier']['name'], -4) == '.gif' || substr($_FILES['fichier']['name'], -5) == '.jpeg'){
				//Vérification si l'ISBN n'a jamais été saisi.
				$isbn = \model\Livre::where('isbn', '=', $request[0]['isbn'])->get();
				if(!isset($isbn[0])){
				
					//Vérification de l'édition
					$edition = \model\Edition::where('nom', '=', $request[0]['edition'])->get();

					//Si elle existe alors
					if(isset($edition[0])){
						$idEdition = $edition[0]->idEdition;
					}
					//Si elle n'existe pas alors créer l'édition
					Else{
						$newEdition = new \model\Edition();
						$newEdition->nom = $request[0]['edition'];
						$newEdition->save();
						$tempEdition = \model\Edition::where('nom', '=', $request[0]['edition'])->get();
						$idEdition = $tempEdition[0]->idEdition;
					}
					
					
					//Upload d'une image---------------------------------------------------------------------------------------------
					$filename = basename($_FILES['fichier']['name']);
					if(file_exists('../upload/'. $filename)){
						$data = "0"; //Si le fichier existe déjà on renvoie une erreur
					}
					//Si l’upload a réussi et que le fichier est correctement posé sur le serveur
					else if (move_uploaded_file($_FILES['fichier']['tmp_name'], '../upload/'.$filename)) {
						$data = "1"; //le retour sera à  1      
					}
					//Si l’upload du fichier à  échoué
					else {
						$data = "2"; //La valeur de retour sera à  0
					}
				
					//Enregistrement du livre
					$Livre = new \model\Livre();
					$Livre->isbn = $request[0]['isbn'];
					$Livre->titre =  htmlspecialchars($request[0]['titre']);
					$Livre->description =  htmlspecialchars($request[0]['resume']);
					$Livre->auteur =  htmlspecialchars($request[0]['auteur']);
					$Livre->idEdition = $idEdition;
					$Livre->langue =  htmlspecialchars($request[0]['langue']);
					$Livre->annee = $request[0]['publication'];
					$Livre->idDiscipline = $request[0]['discipline'];
					$Livre->dateAjout = date("Y-m-d");  ;
					$Livre->couverture = "defaut";
					//S'il y a eu un upload
					if ($data == 1){
						$Livre->couverture = $filename ;
					}
					Else{
						$Livre->couverture = "defaut.jpg";
					}
					$Livre->save();	
					
					//Renommage de de la couverture
					if ($data == 1){	
						rename('../upload/'.$Livre->couverture,'../upload/'. $Livre->idLivre .'.jpg');
						$Livre->couverture = $Livre->idLivre .'.jpg' ;
						$Livre->save();
					}
					
					//Ajout des mots clés pour le livre ------------------------------------
					//Ajout de l'isbn
					$MotISBN = new \model\Mots();
					$MotISBN->libelle = $Livre->isbn;
					$MotISBN->save();
					$LienISBN = new \model\MotsLivre();
					$LienISBN->idLivre = $Livre->idLivre;
					$LienISBN->idMot = $MotISBN->idMot;
					$LienISBN->save();
					//Ajout du titre
					$MotTitre = new \model\Mots();
					$MotTitre->libelle = $Livre->titre;
					$MotTitre->save();
					$LienTitre = new \model\MotsLivre();
					$LienTitre->idLivre = $Livre->idLivre;
					$LienTitre->idMot = $MotTitre->idMot;
					$LienTitre->save();
					//Ajout de la discipline
					$MotDiscipline = \model\Discipline::find($request[0]['discipline']);
					$MotTemp = \model\Mots::where('libelle', '=', $MotDiscipline->nom)->get();
					if(!isset($MotTemp[0])){
						$MotCle = new \model\Mots();
						$MotCle->libelle = htmlspecialchars($MotDiscipline->nom);
						$MotCle->save();
						$LienMot = new \model\MotsLivre();
						$LienMot->idLivre = $Livre->idLivre;
						$LienMot->idMot = $MotCle->idMot;
						$LienMot->save();
					}
					Else{
						$LienMot = new \model\MotsLivre();
						$LienMot->idLivre = $Livre->idLivre;
						$LienMot->idMot = $MotTemp[0]['idMot'];
						$LienMot->save();
					}
					
					//Ajout de l'input "mots clés"
					//On parse la chaîne pour séparer les mots
					$listeMots = explode(",", $request[0]['mots']);
					foreach ($listeMots as $mot) {
						$MotTemp = \model\Mots::where('libelle', '=', $mot)->get();
						if(!isset($MotTemp[0])){
							$MotCle = new \model\Mots();
							$MotCle->libelle = htmlspecialchars($mot);
							$MotCle->save();
							$LienMot = new \model\MotsLivre();
							$LienMot->idLivre = $Livre->idLivre;
							$LienMot->idMot = $MotCle->idMot;
							$LienMot->save();
						}
						Else{
							$LienMot = new \model\MotsLivre();
							$LienMot->idLivre = $Livre->idLivre;
							$LienMot->idMot = $MotTemp[0]['idMot'];
							$LienMot->save();
						}
					}
					
					//Fin ajout mots clés --------------------------------------------------
					
					//Redirection et message d'ajout du livre
					$message = "Le livre a été ajouté";
					$a = new \controller\AdminController();
					$a->callAction('recapitulatif', array($message));
				}
				//Si l'isbn existe alors pas d'ajout dans la base
				if(isset($isbn[0])){
					$message = "Le livre n'a pas été ajouté, car l'isbn existe déja dans la base";
					$a = new \controller\AdminController();
					$a->callAction('recapitulatif', array($message));
				}
			}
			Else{
				$message = "Votre image n'est pas compatible (.jpg, .jpeg, .gif)";
				$a = new \controller\AdminController();
				$a->callAction('recapitulatif', array($message));
			}
		}
		Else{
			$message = "Votre image est supérieure à 1 mo";
			$a = new \controller\AdminController();
			$a->callAction('recapitulatif', array($message));
		}
    }
	
	public function supressionLivre(  array $request ) {
		$Livre = \model\Livre::find($request[1]);
		$emprunts = \model\Emprunt::whereRaw('idLivre ='.$Livre['idLivre']);
		$mots_livre = \model\MotsLivre::whereRaw('idLivre ='.$Livre['idLivre']);
		$Livre->delete();
		$emprunts->delete();
		$mots_livre->delete();

	}
	public function formImportation(  array $request ) {
        
        $v = new \view\ImportationView('/templates/stylesheets/styles.css', $request[0]) ;
        $v->display();

    }

   public function import(  array $post ) {
        // importation de livre à partir d'un fichier
		$filename = basename($_FILES['fichier_import']['name']); 
		$extension=strrchr($filename,'.');   
		if (($handle = fopen($_FILES['fichier_import']['tmp_name'], "r")) !== FALSE) {
			if ( $extension == '.csv')
			{
	    		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    			if ($data[1]!= 'isbn')
	    			{
		    			$isbn = \model\Livre::where('isbn', '=', $data[1])->get();
						if(!isset($isbn[0])){
						var_dump($data);	

				 			// Enregistrement du livre
							$Livre = new \model\Livre();
							$Livre->isbn = htmlspecialchars($data[1]);
							$Livre->titre =  htmlspecialchars($data[2]);
							$Livre->description =  htmlspecialchars($data[3]);
							$Livre->idEdition = htmlspecialchars($data[5]);
							$Livre->auteur =  htmlspecialchars($data[4]);
							$Livre->langue =  htmlspecialchars($data[6]);
							$Livre->annee = htmlspecialchars($data[7]);
							$Livre->dateAjout = date("Y-m-d");  ;
							$Livre->couverture = "defaut";
				    		$Livre->save();
			    		}
			    	}	
				}
			}
			else if ( $extension == '.xml'){
				$xml = simplexml_load_file($_FILES['fichier_import']['tmp_name']);
				$json_string = json_encode($xml);   
				$result_array = json_decode($json_string, TRUE);
				$j = 0 ;
				for ($i=0; $i <count($result_array['Livre']) ; $i++) { 
					$isbn = \model\Livre::where('isbn', '=', $result_array['Livre'][$i]['isbn'])->get();
						if(!isset($isbn[0])){	

				 			// Enregistrement du livre
							$Livre = new \model\Livre();
							$Livre->isbn = htmlspecialchars($result_array['Livre'][$i]['isbn']);
							$Livre->titre =  htmlspecialchars($result_array['Livre'][$i]['titre']);
							$Livre->description =  htmlspecialchars($result_array['Livre'][$i]['description']);
							$Livre->idEdition = htmlspecialchars($result_array['Livre'][$i]['auteur']);
							$Livre->auteur =  htmlspecialchars($result_array['Livre'][$i]['auteur']);
							$Livre->idEdition = htmlspecialchars($result_array['Livre'][$i]['idEdition']);
							$Livre->langue =  htmlspecialchars($result_array['Livre'][$i]['langue']);
							$Livre->annee = htmlspecialchars($result_array['Livre'][$i]['annee']);
							$Livre->dateAjout = date("Y-m-d");  ;
							$Livre->couverture = "defaut";
				    		$Livre->save();
				    		$j++;
				    	}
				}
				echo " $j livres ajouté";

			}
			else echo "Ce type de fichier n'est pas reconnu" ;      		
    	}	
    fclose($handle);
	}
		
	public function export( array $post ) {
		// Exportation de la table livre
		if ($post[0]['select'] == 'CSV'){
			$livres = \model\Livre::all() ;
			$filename = sys_get_temp_dir().'/livre.csv';
			//Récupèration du nom des colonnes de la table
			$tab_attribute = array_keys($livres[0]['attributes']);
			//Création du fichier csv
			$file = fopen($filename, 'w');
			//Ecriture des colonnes
			fputcsv($file, $tab_attribute);
			//Ecriture des ligne de la table livre
			foreach ($livres as $row) {
        		fputcsv($file, $row->toArray());
    		}
    		fclose($file);
    		header('Content-Disposition: attachment; filename="Bibliapp'.date("Y-m-d").'.csv"');
    		readfile($filename);
    		
		}
		//problème UTF8
		if ($post[0]['select'] == 'JSON'){
			$livres = \model\Livre::all()->toJson();
			header('Content-type: text/json');
			header('Content-type: application/json');
			print_r($livres);

		}

		if ($post[0]['select'] == 'XML'){
			$livres = \model\Livre::all();
			$tab_attribute = array_keys($livres[0]['attributes']);
			$filename = sys_get_temp_dir().'/livre.xml';
			$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
			$root_element = $livres[0]['table']."s"; //Livres
			//Création balise racine
			$xml.= "<$root_element>";
			//Création du fichier xml
			$file = fopen($filename, 'w');

			for ($i=0; $i <count($livres) ; $i++) { 
				$tab_ligne = $livres[$i]->toArray();
        		$xml .= "<".$livres[0]['table'].">";
        		foreach ($tab_ligne as $key => $value) {
        			//$key holds the table column name
	         		$xml .= "<$key>";
	         		//embed the SQL data in a CDATA element to avoid XML entity issues
	         		$xml .= "$value"; 
	         		//and close the element
	         		$xml .= "</$key>";
        		}
        		$xml .= "</".$livres[0]['table'].">";

 			}
 			//close the root element
			$xml .= "</$root_element>";
			//ecriture dans le fichier xml
			fwrite($file,$xml);
			fclose($file);
    		header('Content-Disposition: attachment; filename="Bibliapp'.date("Y-m-d").'.xml"');
			readfile($filename);
    		
		}

				
	}
	
	
	
	public function suivi(  array $request ) {
        //Récolte des emprunts
		$emprunts = \model\Emprunt::whereRaw('dateRetour IS NULL')->orderBy('dateEmprunt', 'asc')->get();
		for ($i = 0; $i < count($emprunts); $i++) {
			$emprunts[$i]->livre;
			$emprunts[$i]["livre"]->discipline;
			$emprunts[$i]->users;
		}
        $v = new \view\EmpruntView('/templates/stylesheets/styles.css', $emprunts, $request[0]) ;
        $v->display();
		
    }

    public function relance ( array $request ) {
    	//on récupère l'id des emprunts à relancer
  		$tab_id = array_keys($request[0],'on');
  		$this->mail($tab_id);
		  


    }

    public function relanceDate ( array $request ) {
	    	//récuperation des emprunts
		$dateRelance = htmlspecialchars($request[0]['dateRelance']);
    	if (!empty($dateRelance)){	
			$date = new \DateTime($dateRelance);
			//conversion au format datetime
			$date = $date->format('Y-d-m H:i:s');
			$emprunt = \model\Emprunt::whereRaw('dateEmprunt <="'.$date.'"')->get();
	    	for ($i=0; $i <count($emprunt) ; $i++) { 	
	    		$tab[] = $emprunt[$i]['idEmprunt'];
	    	}
	   		$this->mail($tab);
    	}

	}

	public function mail ( array $request ) {
		$tab_id = $request;
		//Création et envoi des emails
  		date_default_timezone_set('Europe/Paris');
		$mail = new \PHPMailer() ;
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.free.fr';  // Specify main and backup server
		$mail->SMTPAuth = false;                               // Enable SMTP authentication
		#$mail->Username = 'jswan';                            // SMTP username
		#$mail->Password = 'secret';                           // SMTP password
		$mail->CharSet = 'UTF-8';		  
		// Expediteur :
		$mail->SetFrom("test@localhost.com");
		for ($i = 0; $i < count($tab_id); $i++) {
			$emprunt = \model\Emprunt::where('idEmprunt','=',$tab_id[$i])->get();
			$emprunt[0]->users;
			$emprunt[0]->livre;
			$mail->addAddress($emprunt[0]['users']->mail, "Destinataire");
			$mail->Subject = "Avis de retard";
			// Le message
			$mail->Body = "Monsieur/Madame ".$emprunt[0]['users']->nom."\n";
			$mail->Body .= "Titre du livre :".$emprunt[0]['livre']->titre."\n";
			$mail->Body .= "Date de l'emprunt :".$emprunt[0]->dateEmprunt."\n";
			$mail->Body .= "Nous vous remercions de bien vouloir rapporter au plus vite ce document à la bibliothèque.\n";

			if ( !$mail->Send() ) {
	   			echo "Echec de l'envoi du mail, Erreur: " . $mail->ErrorInfo;
			} 
			else {
	    		echo "Message envoyé!";
			}
		};	
	}
	public function statistiques(  array $request ) {
        
        echo "AdminController :: statistiques";

    }
	
}
?>