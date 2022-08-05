<?php

	//error_reporting(0);
	// namespace Villanovo\ThirdParties;

	class db {

		var $DEFAULT_HOST = "bqro.myd.infomaniak.com";
		var $DEFAULT_DB   = "bqro_test";
		var $DEFAULT_USER = "bqro_yassine";
		var $DEFAULT_PASS = "v6-kFGyEdL8";

		// var $DEFAULT_HOST = 'localhost';
		// var $DEFAULT_DB   = 'test';

		/**
		 * @var mysqli|false|null
		 */
		public static $DB = null;
		var $RS = null;
		var $sql = "";
		var $error_message = "";
        public $redis;

		// default constructor
		function __construct($dbserver = "", $dbname = "", $dbuser="", $dbpass=""){

			if( strlen($dbserver) == 0 ) { $dbserver = $this->DEFAULT_HOST; }
			if( strlen($dbname) == 0 ) { $dbname = $this->DEFAULT_DB; }
			if( strlen($dbuser) == 0 ) { $dbuser = $this->DEFAULT_USER; }
			if( strlen($dbpass) == 0 ) { $dbpass = $this->DEFAULT_PASS; }
            /*$this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);*/
			$list = explode('/',$_SERVER['DOCUMENT_ROOT']);
			$application = array_pop($list);

			//eko($application);

            // $dbuser = 'root';
            // $dbpass = '';

			$this->connect($dbserver, $dbname, $dbuser, $dbpass);
			$this->searchCriteria = null;
		}
        
         
		// Connection related
		function connect($dbserver, $dbname, $dbuser, $dbpass){

			if( self::$DB == null ) {

				$this->RS = null;
				self::$DB = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname);

				if( self::$DB ){
					if( self::$DB->connect_error ) {
						die('Erreur de connexion (' . self::$DB->connect_errno . ') '. self::$DB->connect_error);
					}
					if( !self::$DB->set_charset("utf8") ){
						printf("Erreur lors du chargement du jeu de caractères utf8 : %s\n", self::$DB->error);
						exit();
					} else {
						//printf("Jeu de caractères courant : %s\n", self::$DB->character_set_name());
					}
				}else{
					echo "Failed to connect to MySQL: " . mysqli_connect_error();
					exit();
				}
				 

			}
		}

		// Executer une requete SQL
		function exec($sql){

			if(self::$DB != null) {
				$this->RS = self::$DB->query($sql) or $this->error_message = self::$DB->errno." : ".self::$DB->connect_error;
			}
			return $this->RS;
		}

		function exec_cache($sql,$ttl){

		    $rows = null;

            $key = md5($sql);

            if( $result = $this->redis->get($key) ){
                $rows = json_decode($result,true);
            }
            else{
                $this->RS = self::$DB->query($sql) or $this->error_message = self::$DB->errno . " : " . self::$DB->connect_error;
                if($this->RS != null) {
                    $rows = $this->RS->fetch_all(MYSQLI_ASSOC);
                    $this->redis->setex($key, $ttl, json_encode($rows));
                }
            }

            return $rows;

        }

		// Executer une requete SQL
		function query($sql){
			return $this->exec($sql);
		}

		// Recuperer la requete SQL
		function getSQL(){
			return $this->sql;
		}

		// Recuperer l'erreur de l'execution d'une requete SQL
		function getError(){
			return self::$DB->error;
		}

		// Fermer le RecordSet et la connexion
		function close(){
			if(self::$DB != null) {
				self::$DB->close();
				self::$DB = null;
				if($this->RS != null) {
                    $this->RS->free();
					$this->RS = null;
				}
			}
		}

		// Resultset related
		function getRow(){
			$obj = null;
			if($this->RS != null) {
				$obj = mysql_fetch_row ($this->RS);
			}
			return $obj;
		}

		// Retourner un objet
		function getObject(){
			$obj = null;
			if($this->RS != null) {
				$obj = mysql_fetch_object ($this->RS);
			}
			return $obj;
		}

		// Retourner un tableau
		function getArray(){
			$obj = null;
			if($this->RS != null) {
				$obj = mysqli_fetch_array ($this->RS);
			}
			return $obj;
		}

		// Retourner un tableau associatif
		function getAssoc(){
			$obj = null;
			if($this->RS != null) {
				$obj = $this->RS->fetch_assoc();
			}
			return $obj;
		}

		function fetchAll() {
			$obj = null;
			if ($this->RS != null) {
				$obj = $this->RS->fetch_all(MYSQLI_ASSOC);
			}
			return $obj;
		}

		// Retourner le nombre des colones
		function numCols(){
			$r = 0;
			if($this->RS != null) {
				$r = mysql_num_fields($this->RS);
			}
			return $r;
		}

		// Retourner le nombre des lignes
		function numRows(){
			$r = 0;
			if($this->RS != null) {
				$r = $this->RS->num_rows;
			}
			return $r;
		}

		// Retourner le nombre des lignes affectees
		function affectedRows(){
			$r = 0;
			if($this->RS != null) {
				$r = self::$DB->affected_rows;
			}
			return $r;
		}

		// Retourner le dernier identifiant cree
		function lastOID(){
			$id = null;
			if ($this->RS != null) {
				$id = self::$DB->insert_id;
			}
			return $id;
		}

		// replaces unix wildcard chars with sql wildcard chars
		function sqlSearchStr($srch_str){
			$srch_str = str_replace("*", "%", $srch_str);
			$srch_str = str_replace("?", "_", $srch_str);
			return $srch_str;
		}

		// Demarrer une transaction
		function beginTrans(){
			return self::$DB->autocommit(FALSE) or die('erreur');
			//return self::$DB->autocommit(false);
		}

		function endTrans(){
            return self::$DB->autocommit(true) or die('erreur');
        }

		// Valider la transaction
		function commitTrans(){
			self::$DB->commit();
		}

		// Annuler la transaction
		function rollbackTrans(){
			return self::$DB->rollback();
		}

		// real espace
		function realEscape($string){
			return self::$DB->real_escape_string($string);
		}

	}
?>