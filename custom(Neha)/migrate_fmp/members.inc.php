<?php

require_once('includes\PHPExcel-1.8\Classes\PHPExcel.php');

/***
 * This class is used to create data import script of CRM contact "Member"
 * This class handles the details of performing the migration for you - iterating over source records, creating destination objects,
 * and keeping track of the relationships between them.
 * @author Netwininfo
 *
 */
class FmpMembersMigration extends Migration
{
	public function __construct($arguments)
	{
		parent::__construct($arguments);
		$this->description = t('Import members data from FMP');

		/** Define the mapping between a single row of source data and the resulting Drupal data **/
		$this->map = new MigrateSQLMap($this->machineName, array(
			'NumeroMembre' => array(
				'type' => 'varchar',
				//'unsigned' => TRUE,
				'not null' => TRUE,
				'description' => t('Member ID'),
				'length'=>256,
				)
			),
			MigrateDestinationCRMCoreContact::getKeySchema()
		);

		/** The full system filepath to the source CSV file **/
		$file = 'import/member_contact.csv';
		
		/** An array describing the CSV file's columns, This may be left empty if the CSV file has a header row **/
		$columns = array();
		
		/** The number of rows to count as headers **/
		$options = array('header_rows' => 1);

		/** Define where the source data is coming from **/
		$this->source = new MigrateSourceCSV($file, $columns, $options);
		
		/** Define the destination object type  **/
		$this->destination = new MigrateDestinationCRMCoreContact('membre');
	
		
		/** Define mappings from source fields to destination fields **/
		$this->addFieldMapping('field_memberid', 'NumeroMembre');
		$this->addFieldMapping('contact_name', 'Nom')->callbacks('utf8_encode');
		
		 // Map Address Field
		 $this->addFieldMapping('field_member_address','Pays')->callbacks(array($this, 'get_country_code'));
		 $this->addFieldMapping('field_member_address:thoroughfare', 'Adresse')->callbacks('utf8_encode');
		 $this->addFieldMapping('field_member_address:postal_code', 'CodePostal')->callbacks('utf8_encode');
		 $this->addFieldMapping('field_member_address:locality', 'Locality')->callbacks('utf8_encode');   
			
		// Map Normal filed		 
		$this->addFieldMapping('field_envoi_magazine', 'EnvoiMagazine');
		$this->addFieldMapping('field_carte_membre_electronique', 'CarteMembreElec')->callbacks(array($this, 'get_cartmemb'));  
		
		// Below field didn't chnage as it was ok
		$this->addFieldMapping('field_ligne_nom_supplementaire', 'LigneNomSuppl')->callbacks('utf8_encode');
		$this->addFieldMapping('field_notes', 'DateRemboursement')->callbacks(array($this,'get_dateremb'));
		$this->addFieldMapping('field_prenom', 'Prenom')->callbacks('utf8_encode');
		$this->addFieldMapping('field_moyen_de_paiement', 'MoyenDePaiement')->callbacks('utf8_encode');
		$this->addFieldMapping('field_vous_etes', 'VousEtes')->callbacks(array($this, 'get_vousetes'));
		$this->addFieldMapping('field_compteur_le_cri', 'CompteurCri')->callbacks('utf8_encode');
		$this->addFieldMapping('field_communication_structuree', 'EnvoiNumCalc')->callbacks('utf8_encode');
		$this->addFieldMapping('field_compteur_juridique', 'CompteurJuri')->callbacks('utf8_encode');
		//$this->addFieldMapping('field_raison_desinscription', 'RaisonDepart')->callbacks('utf8_encode');
		$this->addFieldMapping('field_numero_tva', 'NumTVA')->callbacks('utf8_encode');
		$this->addFieldMapping('field_gsm', 'Telephone')->callbacks(array($this, 'get_gsm'));  
		
		/* Below Membership field will be mapped later
		 	$this->addFieldMapping('field_rappel', 'Permanent')->callbacks(array($this, 'get_rappel'));
		 	$this->addFieldMapping('field_mois_d_echeance', 'MoisEchÃ©ance');
		 	$this->addFieldMapping('field_annee_d_echeance', 'AnnÃ©eEchÃ©ance');
		 	$this->addFieldMapping('field_professionnel', 'Profession2')->callbacks(array($this, 'get_prof')); 
		 */
	}

	
  /**
   * This method is called by the source class next() method, after loading the data row.
   * The argument $row is object containing the raw data provided by the source.There are two primary reasons to implement prepareRow()
   *  1. To modify the data row before it passes through any further methods and handlers.
   *  2. To conditionally skip a row (by returning FALSE).
   * {@inheritDoc}
   * @see Migration::prepareRow()
   */	
   public function prepareRow($row){
   	
		$numeroMembre = 0;
		$numeroMembre = (int)$row->NumeroMembre;
		$row->NumeroMembre = $numeroMembre;
		

		/** 1. Skip those record whose VME == 1
		 *  2. Skip those record whose NumeroMembre value is blank
		 **/
		if ( (strlen($numeroMembre) == 1) ||  ($row->VME == 1)) {
			return FALSE;
		} 
		
	}   

	
   /**
	* This method is called to manipulate the destination object before it is saved to the Drupal database 
    * The $entity argument is destination object as populated by the initial field mappings and manipulated by the field-level methods;
    * The $row argument is an object containing the data after prepareRow() and any callbacks have been applied.
    * @param unknown $entity
    * @param stdClass $row
    */
	public function prepare($entity, stdClass $row)
	{		
					
		/** Membership type fields **/
		$member_id = $row->NumeroMembre;
		
		if(!empty($row->Email)){
			$emails = explode ( ';', $row->Email );
			$email = filter_var ( $emails [0], FILTER_SANITIZE_EMAIL );
		}
		
		
		// set membership start_date value
		if(!empty($row->DateDerCot)){
			$sdate = DateTime::createFromFormat("d/m/Y" ,  $row->DateDerCot );
			$start_date =  $sdate->format('Y-m-d'); 
		}
			
		// set membership end_month & end_year value
		if(!empty($row->MoisEch�ance) && !empty($row->Ann�eEch�ance)){
			$end_month = $row->MoisEch�ance;
			$end_year = $row->Ann�eEch�ance;
			
			$edate = '01/'. $end_month.'/'.$end_year;
			
			$edate_val = DateTime::createFromFormat("d/m/Y" ,  $edate);
			
			if(!empty($edate_val)){
				$end_date =  $edate_val->format('Y-m-d');
			}
		}
		
		
		//$row->Permanent = 1;
	
		/**
		 * Check Membership type "Permanent" & email field value
		 * If both value set, create membership by using email and memberid
		 */
		if(!empty($row->Permanent) && !empty($email) && !empty($start_date)){
			
			// Fetch User record using email
			$query = new \EntityFieldQuery();
			$query->entityCondition('entity_type', 'user')
			->propertyCondition('name',$email)
			->addMetaData('memberid', $member_id);
			$results = $query->execute();
			
			if(!empty($results['user'])){			
				$uid = key($results['user']);
			}
			
			// Check user exist or not by uid, if uid is blank or less than 1 then create user of role type snpc-member and return $uid
			if($uid < 1 || empty($uid)){			
				$uid = $this->createUser($email,$member_id);
			}
				
			$membership_type = 'lifetime';
			$duration =  '99999 days';
			
			// If uid > 0 , create membership and set corresponding destination object
			if($uid > 0){
				$membership_id = $this->createMembership ( $uid, $membership_type, $start_date, $end_date, $duration );
				$entity->field_rappel [LANGUAGE_NONE] [0] ['nid'] = $membership_id;
			}
		}
	

	   //$row->Profession2 = 1;
	   
	   /**
		* Check Membership type "Profession2" & email field value
	    * If both value set, create membership by using email and memberid
		*/		
	   if(!empty($row->Profession2) && !empty($email) && !empty($start_date)){
	   	
	   	    // Fetch User record using email
			$query = new \EntityFieldQuery();
			$query->entityCondition('entity_type', 'user')
			->propertyCondition('name',$email)
			->addMetaData('memberid', $member_id);
			$results = $query->execute();
				
			$membership_type = 'professional';
			$duration =  '365 days';
			
	   		if(!empty($results['user'])){
				$uid = key($results['user']);
			}
			
			// Check user exist or not by uid, if uid is blank or less than 1 then create user of role type snpc-member and return $uid
			if($uid < 1){
				$uid = $this->createUser($email,$member_id);
			}
			
			// If uid > 0 , create membership and set corresponding destination object
			if($uid > 0){
				$membership_id = $this->createMembership ( $uid, $membership_type, $start_date, $end_date, $duration );
				$entity->field_professionnel [LANGUAGE_NONE] [0] ['nid'] = $membership_id;
			}
			
		}   
		
		
		/** 
		 * Link CRM contacts to Drupal users
		 * Check user exist or not by using email and memberid
		 * If not exist , create user for that member
		 **/
	   if(!empty($email) && !empty($member_id)){
			$results = $this->CheckExistingUser($email,$member_id);
			
			if(!empty($results['user'])){
				if (key ( $results ['user']) < 1 ) {
					$this->createUser ( $email, $member_id );
				}
			}
		}
		
		
		/**  Set destination object for taxonomy term refrence field **/
		
		if(!empty($row->RaisonDepart)){
			$term_id = '';
			$term_id = $this->getRaisonDesinscriptionTermId($row->RaisonDepart);
			$entity->field_raison_desinscription[LANGUAGE_NONE][0]['tid'] = $term_id;
		}
		
		if(!empty($row->Langue)){
			$term_id = '';
			$term_id = $this->getLanguageTermId($row->Langue);
			$entity->field_langue[LANGUAGE_NONE][0]['tid'] = $term_id;
		}
		
		if(!empty($row->Statut)){
			$term_id = '';
			$term_id = $this->getMemberStatusTermId($row->Statut);
			if (!empty($term_id)){
				$entity->field_statut[LANGUAGE_NONE][0]['tid'] = $term_id;
			}
		}
		
		if(!empty($row->SiagePaiement)){
			$term_id = '';
			$term_id = $this->getOfficeTermId($row->SiagePaiement);
			if (!empty($term_id)){
				$entity->field_siege_paiement[LANGUAGE_NONE][0]['tid'] = $term_id;
			}
		}
		
		
		if(!empty($row->Politesse)){
			$term_id = '';
			$term_id = $this->get_politesse($row->Politesse);
			if (!empty($term_id)){
				$entity->field_politesse[LANGUAGE_NONE][0]['tid'] = $term_id; 
			}
		}
		
		
		// These fields are not working with addFieldMapping(),
		if(!empty($row->AnneInscrDomiciliation)){
			$time = strtotime($row->AnneInscrDomiciliation);
			$AnneInscrDomiciliation = date('Y-m-d',$time);
			$entity->field_date_domiciliation[LANGUAGE_NONE][0]['value'] = $AnneInscrDomiciliation;
		}
		
		if(!empty($row->DateNaissance)){
			$time = strtotime($row->DateNaissance);
			$DateNaissance = date('Y-m-d',$time);
			$entity->field_date_naissance[LANGUAGE_NONE][0]['value'] = $DateNaissance;
		}
		
		if(!empty($row->DatePremireCot)){
			$time = strtotime($row->DatePremireCot);
			$DatePremireCot = date('Y-m-d',$time);
			$entity->field_date_prem_cotisation[LANGUAGE_NONE][0]['value'] = $DatePremireCot;
		}
		
		if (!empty($row->DateSEncodage)){
			$time = strtotime($row->DateSEncodage);
			$DateSEncodage = date('Y-m-d',$time);
			$entity->field_date_prem_cotisation[LANGUAGE_NONE][0]['value'] = $DateSEncodage;
		} 
		
	}

	
	/**
	 * This function is called mmediately after the complete destination object is saved to save pargraph type contact data 
	 * @param unknown $entity
	 * @param stdClass $row
	 */
	public function complete($entity, stdClass $row) {
				
		
		/** Save Mobile value as paragraph **/
		
		if(!empty($row->Telephone))
		$phone_array = explode('-', $row->Telephone);
		
		if(count($phone_array) > 0 ){
			
			// If $phone_array count > 1 then it have multiple mobile no
			if(count($phone_array) > 1){
				
				foreach ( $phone_array as $key => $val ) {
					
					$phone =  preg_replace('/[^0-9]/', '', $val);
					
					//Constructs the $paragraph entity object for telephone
					$paragraph = new ParagraphsItemEntity ( array (
							'field_name' => 'field_telephone',
							'bundle' => 'field_contact_phone_bundle' 
					) );
					
					$paragraph->is_new = TRUE;
					$paragraph->setHostEntity ( 'crm_core_contact', $entity );
					$paragraph->field_contact_phone [LANGUAGE_NONE] [0] ['value'] = $phone;
					$paragraph->save ();
				}
				
			 }else{
			 	
			 	$phone =  preg_replace('/[^0-9]/', '', $phone_array);
				$paragraph = new ParagraphsItemEntity ( array (
						'field_name' => 'field_telephone',
						'bundle' => 'field_contact_phone_bundle'
				) );
				
				$paragraph->is_new = TRUE;
				$paragraph->setHostEntity ( 'crm_core_contact', $entity );
				$paragraph->field_contact_phone [LANGUAGE_NONE] [0] ['value'] = $phone;
				$paragraph->save ();
			}
			
		} 
				
	
		/** Save Email value as paragraph **/
		$email = explode(';', $row->Email);
		if(count($email) > 0 ){
			
			// If $email count > 1 then it have multiple email
			if(count($email) > 1){
				
				foreach ($email as $key=>$val){
					
					//Constructs the $paragraph entity object for email
					$paragraph = new ParagraphsItemEntity ( array (
							'field_name' => 'field_mail',
							'bundle' => 'field_contact_email_bundle'
					) );
							
					$paragraph->is_new = TRUE;
					$paragraph->setHostEntity ( 'crm_core_contact', $entity );
					$paragraph->field_contact_email [LANGUAGE_NONE] [0] ['value'] = utf8_encode($val);
					$paragraph->save ();
				}
				
			}else{
														
				$paragraph = new ParagraphsItemEntity ( array (
						'field_name' => 'field_mail',
						'bundle' => 'field_contact_email_bundle'
				) );
				
				$paragraph->is_new = TRUE;
				$paragraph->setHostEntity ( 'crm_core_contact', $entity );
				$paragraph->field_contact_email [LANGUAGE_NONE] [0] ['value'] = utf8_encode($email[0]);
				$paragraph->save ();
			}
		
		}
	
	 }  
	 
	
	/**
	 * Fetch Country code using country name
	 * @param unknown $val
	 */
	function get_country_code($val){
		$val = utf8_encode($val);
		$countries = country_get_list();
	    $country_code = 'PF';
	    if(!empty($val)){
			$country_code =  array_search(strtolower($val), array_map('strtolower', $countries));
	    }
	    
		return $country_code;
	} 
	
	
	
	/**
	 * Fetch Membership Id using membership type
	 * @param string $value
	 * @return string
	 */
	function getMembershipId($value) {
		$id = '';
		$id = db_query('SELECT id FROM membership_entity_type WHERE type = :type', array(':type' => $value))->fetchField();
		return $id; 
	}
	
	
	/**
	 * Fetch Term Id using term name of vocabulary "languages"
	 * @param string $value
	 * @return string
	 */
	function getLanguageTermId($value='F') {
		
		 $lan = array('F'=>'Francais','N'=>'Nerderlands');
		 $term_name = $lan[$value];
		 $term_res = taxonomy_get_term_by_name($term_name,'languages');
		 $term_id = '';
		 foreach ($term_res as $result){
		 	$term_id = $result->tid;
		 }
		 
		 return $term_id;
		
	} 
	
	/**
	 * Fetch Term Id using term name for  vocabulary "member_status"
	 * @param string $value
	 * @return string
	 */
	function getMemberStatusTermId($value='M') {
	
		$term_name = $value;
		$term_res = taxonomy_get_term_by_name($term_name,'member_status');
		$term_id = '';
		foreach ($term_res as $result){
			$term_id = $result->tid;
		}
			
		return $term_id;
	
	}
	
	
	/**
	 * Fetch Term Id using term name for  vocabulary "offices"
	 * @param string $value
	 * @return string
	 */
	function getOfficeTermId($value='N00') {
	
		$term_name = utf8_encode($value);
		$term_res = taxonomy_get_term_by_name($term_name,'offices');
		$term_id = '';
		foreach ($term_res as $result){
			$term_id = $result->tid;
		}
			
		return $term_id;
	
	}
	
	/**
	 * Fetch Term Id using term name for  vocabulary "raison_desinscription"
	 * @param string $value
	 * @return string
	 */
	function getRaisonDesinscriptionTermId($value=NULL) {
	   
		$term_id = null;
		if($value != null){
			$term_name = utf8_encode ( $value );
			$term_res = taxonomy_get_term_by_name ( $term_name, 'raison_desinscription' );
			$term_id = '';
			foreach ( $term_res as $result ) {
				$term_id = $result->tid;
			}
		}
			
		return $term_id;
	
	}
	
	
	/**
	 * 1. This function sanitize polatise column data of csv 
	 * 2. Fetch Term Id using term name i.e $key
	 * @param unknown $key
	 */
	function get_politesse($key)
	{
		
		$filename =  'import/politesse.xlsx';
		
		$type = PHPExcel_IOFactory::identify($filename);
		
		// Create a reader by explicitly setting the file type.
		$objReader = PHPExcel_IOFactory::createReader($type);
		
		// Load the excel data into PHP
		$objPHPExcel = $objReader->load($filename);
		
		//Itrating through all the sheets in the excel and storing the array data
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$worksheets[$worksheet->getTitle()] = $worksheet->toArray();
		
			$result = $worksheets ['Sheet1'];
			unset($result[0]);
		
			/**
			 * Iterate the array and store their value into $polatise array in following way
			 * Set old value i.e $res[0] as key of $polatise array
			 * Set new value i.e $res[1] as value of $polatise array
			 */
			foreach($result as $res){
				$polatise[$res[0]] = $res[1];
			}
		}
		
		
		$val_new = '';
		$term_id = '';
		
		// If key exist into array return corresponding value from $polatise array
		if (array_key_exists ( $key, $polatise )) {
			$val_new = $polatise[$key];
		}
		
		// Fetch term Id by using term name
		if(!empty($val_new)){
			$term_name = $val_new;
			$term_res = taxonomy_get_term_by_name ( $term_name, 'salutation' );
			foreach ( $term_res as $result ) {
				$term_id = $result->tid;
			}
		}
			
		return $term_id;
		
		//return $val_new;
		
	} 
	
	
	
	protected function get_telephone($value)
	{
		$value1 = explode('-', $value);
		return preg_replace('/[^0-9]/', '', $value1[0]);
	}

	protected function get_gsm($value)
	{
		$value2 = explode('-', $value);
		return preg_replace('/[^0-9]/', '', $value2[1]);
	}

	protected function get_rappel($value)
	{
		if ($value == 1)
		{
			return 'Non';
		}
		else
		{
			return 'Oui';
		}
	}

	protected function get_prof($value)
	{
		if ($value == 1)
		{
			return 'Oui';
		}
		else
		{
			return 'Non';
		}
	}

	protected function get_envoimag($value)
	{
		if ($value == 1)
		{
			return 'Non';
		}
		else
		{
			return 'Oui';
		}
	}

	protected function get_vousetes($value)
	{
		switch ($value)
		{
			case 'PROPRIETAIRE + COPROPRIETAIRE' :
				return 'PropriÃ©taire + copropriÃ©taire';
			case 'COPROPRIETAIRE+LOCATAIRE' :
				return 'CopropriÃ©taire + locataire';
			case 'PROPRIETAIRE + LOCATAIRE' :
				return 'PropriÃ©taire + locataire';
			case 'LOCATAIRE' :
				return 'Locataire';
			case 'COPROPRIETAIRE' :
				return 'CopropriÃ©taire';
			case 'PROPRIETAIRE' :
				return 'PropriÃ©taire';
			case 'PROFESSIONNEL IMMOBILIER' :
				return 'Agent immobilier';
			case 'Usufruitier' :
				return 'Usufruitier';
			default:
				return '';
		}
	}

	protected function get_dateremb($value)
	{
		if (!empty($value))
		{
			return 'Date de remboursement : ' . $value;
		}
	}

	protected function get_cartmemb($value)
	{
		if ($value == 'Neen')
		{
			return '0';
		}
		else
		{
			return '1';
		}
	}
	
	
	/**
	 * This function creates membership for member data of csv
	 * @param unknown $mail
	 * @param unknown $uname
	 * @param unknown $start_date
	 * @return The
	 */
	function createMembership($uid,$membership_type,$start_date,$end_date,$term){
				
			$member_id = $this->random_string(5);
			$membership_id = db_insert ( 'membership_entity' )->fields ( array (
					'member_id' => filter_xss ( $member_id ),
					'type' => $membership_type,
					'uid' => $uid,
					'status' => 1,
					'created' => time (),
					'changed' => time ()
			) )->execute ();
			
			
			
			if(!empty($membership_id)){
				$termid = db_insert ( 'membership_entity_term' )->fields ( array (
						'mid' => $membership_id,
						'status' => 1,
						'term' => $term,
						'start' => $start_date,
						'end' => $end_date,
						'created'=>time(),
						'changed' => time()
				) )->execute ();
				
			}
		
		return $membership_id;
	}
	
	/**
	 * This function creates membership for member data of csv
	 * @param unknown $mail
	 * @param unknown $uname
	 * @param unknown $start_date
	 * @return The
	 */
	function createUser($mail,$member_id){

		$account = new stdClass();
		$account->name = $mail;
		$account->mail = $mail;
		$account->init = $mail;
		$account->pass = $member_id;
		$account->status = 1;
		$account->roles = array (
				DRUPAL_AUTHENTICATED_RID => 'authenticated user',
				4 => 'snpc-member'
		);
		
		$edit = array(
				'field_memberid' => array(
						'und' => array(
								0 => array(
										'value' => $member_id,
								),
						),
				),
		);
		
		user_save($account,$edit);
				
		$uid = $account->uid;
	
		return $uid;
	}
	
	/**
	 * This function is used to generate Random string
	 * @param unknown $length
	 * @return string|mixed
	 */
	function random_string($length) {
		$key = '';
		$keys = array_merge(range(0, 9));
	
		for ($i = 0; $i < $length; $i++){
			$key .= $keys[array_rand($keys)];
		}
	
		return $key;
	}
	
	/**
	 * This function is used to check whether user exist or not
	 * @param unknown $email
	 * @param unknown $member_id
	 */
	function CheckExistingUser($email,$member_id){
		$query = new \EntityFieldQuery();
		$query->entityCondition('entity_type', 'user')
		->propertyCondition('name',$email)
		->addMetaData('memberid', $member_id);
		$results = $query->execute();
		return $results;
	}
	
}
