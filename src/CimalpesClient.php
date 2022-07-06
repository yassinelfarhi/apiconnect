<?php

/**
 * CLASSE PERMETTANT DE SE CONNECTER A L'API cimalpes
 * FUNCTIONS PRRINCIPALES DE CETTE CLASSE :
 * 1- Extraire puis parser un flux de données XML
 * 2- Transformation des données en réspectant le schéma de la bd Mysql
 * 3- Insertion des données dans la db Mysql
 */


namespace Villanovo\Cimalpes;

use Villanovo\Cimalpes\Dtos\BienDto;

use Villanovo\Cimalpes\CimalpesPersist;

class CimalpesClient
{


	public $flux_xml;
	public $biens;
	public $details;
	public $disponibilites;

	const API_BIENS = "https://cimalpes.ski/fr/flux/?fonction=biens&login=xml@villanovo.com&pass=M4X876RV3N2D";

	const API_DETAIL = "https://cimalpes.ski/fr/flux/?fonction=detail&login=xml@villanovo.com&pass=M4X876RV3N2D&id_bien=";

	const API_DISPO = "https://cimalpes.ski/fr/flux/?fonction=infos&login=rentals@cimalpes.com&pass=Cimalpes74120&id_bien=";


	public function getBiens()
	{


		$this->flux_xml = new \DOMDocument();
		$this->flux_xml->load(self::API_BIENS);
		$this->biens = $this->flux_xml->getElementsByTagName("bien");
		$table_biens = [];
		foreach ($this->biens as $bien) {
			array_push($table_biens, BienDto::fromNodeListing($bien));
		}
		return  $table_biens;
	}


	public function getDetails($id_bien)
	{

		$this->flux_xml = new \DOMDocument();
		$this->flux_xml->load(self::API_DETAIL . $id_bien);

		return BienDto::fromNodeDetail($this->flux_xml->getElementsByTagName('detail')->item(0));

		// $this->details["id_bien"] = $id_bien;
		// $this->details["descriptif_court"] = $this->flux_xml->getElementsByTagName("descriptif_court")->item(0)->nodeValue;
		// $this->details["descriptif_bref"] = $this->flux_xml->getElementsByTagName("descriptif_bref")->item(0)->nodeValue;
		// $this->details["descriptif_bref_en"] = $this->flux_xml->getElementsByTagName("descriptif_bref_en")->item(0)->nodeValue;
		// $this->details["descriptif_court_en"] = $this->flux_xml->getElementsByTagName("descriptif_court_en")->item(0)->nodeValue;




		//return $this->details;
	}

	// public function getDisponibilites($id_bien){
	//      $this->flux_xml = new \DOMDocument();
	//      $table_sejour = [];
	//      $this->flux_xml->load(self::API_DISPO . $id_bien);

	//      $sejours = $this->flux_xml->getElementsByTagName("sejour");


	//      // foreach($sejours as $sejour){
	//      //   array_push( $table_biens, BienDto::fromNodeDisponibilites($sejour));
	//      // }

	//    //  for( $i = 0 ; $sejours->length ; $i++) {
	//    //    $this->disponibilites["date_debut"] = $this->flux_xml->getElementsByTagName("sejour")->item($i)->childNodes->item(0)->nodeValue;
	//    //    $this->disponibilites["date_fin"] = $this->flux_xml->getElementsByTagName("sejour")->item($i)->childNodes->item(1)->nodeValue;
	//    //   }

	//      return $this->disponibilites;
	// }



	public function persistData()
	{
	}
}
