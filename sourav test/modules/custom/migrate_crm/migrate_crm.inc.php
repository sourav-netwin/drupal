<?php
	/**
	*This class is used to create data import script of CRM contact "Member"
	*This class handles the details of performing the migration for you - iterating over source records, creating destination objects,
	*and keeping track of the relationships between them.
	*@author Netwininfo
	*/
	class CrmMemberMigration extends Migration
	{
		public function __construct($arguments)
		{
			parent::__construct($arguments);
			$this->description = t('Import members data from csv to crm');

			//Define the mapping between a single row of source data and the resulting Drupal data
			$this->map = new MigrateSQLMap(
				$this->machineName,
				array(
					'sid' => array(
						'type' => 'int',
						'not null' => TRUE,
						'description' => t('Member ID')
					)
				),
				MigrateDestinationCRMCoreContact::getKeySchema()
			);

			//The full system filepath to the source CSV file
			$file = 'import/member_contact.csv';

			//An array describing the CSV file's columns, This may be left empty if the CSV file has a header row
			$columns = array();

			//The number of rows to count as headers
			$options = array('header_rows' => 1);

			//Define where the source data is coming from
			$this->source = new MigrateSourceCSV($file , $columns , $options);

			//Define the destination object type
			$this->destination = new MigrateDestinationCRMCoreContact('menber');

			/** Define mappings from source fields to destination fields **/
			$this->addFieldMapping('contact_name', 'Name');
			$this->addFieldMapping('field_memberid', 'Memberid')->callbacks('utf8_encode');

			 // Map Address Field
			 $this->addFieldMapping('field_member_address','Country');
			 $this->addFieldMapping('field_member_address:thoroughfare', 'Address one')->callbacks('utf8_encode');
			 $this->addFieldMapping('field_member_address:postal_code', 'Pin code')->callbacks('utf8_encode');
			 $this->addFieldMapping('field_member_address:administrative_area', 'State')->callbacks('utf8_encode');
			 $this->addFieldMapping('field_member_address:locality', 'City')->callbacks('utf8_encode');

			// Map Normal filed
			$this->addFieldMapping('field_status', 'status');

		}


	  /**
	   * This method is called by the source class next() method, after loading the data row.
	   * The argument $row is object containing the raw data provided by the source.There are two primary reasons to implement prepareRow()
	   *  1. To modify the data row before it passes through any further methods and handlers.
	   *  2. To conditionally skip a row (by returning FALSE).
	   * {@inheritDoc}
	   * @see Migration::prepareRow()
	   */
	   // public function prepareRow($row){
//
			// $numeroMembre = 0;
			// $numeroMembre = (int)$row->NumeroMembre;
			// $row->NumeroMembre = $numeroMembre;
//
//
			// /** 1. Skip those record whose VME == 1
			 // *  2. Skip those record whose NumeroMembre value is blank
			 // **/
			// if ( (strlen($numeroMembre) == 1) ||  ($row->VME == 1)) {
				// return FALSE;
			// }
//
		// }


	   /**
		* This method is called to manipulate the destination object before it is saved to the Drupal database
	    * The $entity argument is destination object as populated by the initial field mappings and manipulated by the field-level methods;
	    * The $row argument is an object containing the data after prepareRow() and any callbacks have been applied.
	    * @param unknown $entity
	    * @param stdClass $row
	    */
		public function prepare($entity, stdClass $row)
		{


			/**  Set destination object for taxonomy term refrence field **/
//
			// if(!empty($row->RaisonDepart)){
				// $term_id = '';
				// $term_id = $this->getRaisonDesinscriptionTermId($row->RaisonDepart);
				// $entity->field_raison_desinscription[LANGUAGE_NONE][0]['tid'] = $term_id;
			// }
//
			// if(!empty($row->Langue)){
				// $term_id = '';
				// $term_id = $this->getLanguageTermId($row->Langue);
				// $entity->field_langue[LANGUAGE_NONE][0]['tid'] = $term_id;
			// }
//
			// if(!empty($row->Statut)){
				// $term_id = '';
				// $term_id = $this->getMemberStatusTermId($row->Statut);
				// if (!empty($term_id)){
					// $entity->field_statut[LANGUAGE_NONE][0]['tid'] = $term_id;
				// }
			// }
//
			// if(!empty($row->SiagePaiement)){
				// $term_id = '';
				// $term_id = $this->getOfficeTermId($row->SiagePaiement);
				// if (!empty($term_id)){
					// $entity->field_siege_paiement[LANGUAGE_NONE][0]['tid'] = $term_id;
				// }
			// }
//
//
			// if(!empty($row->Politesse)){
				// $term_id = '';
				// $term_id = $this->get_politesse($row->Politesse);
				// if (!empty($term_id)){
					// $entity->field_politesse[LANGUAGE_NONE][0]['tid'] = $term_id;
				// }
			// }
//
//
			// // These fields are not working with addFieldMapping(),
			// if(!empty($row->AnneInscrDomiciliation)){
				// $time = strtotime($row->AnneInscrDomiciliation);
				// $AnneInscrDomiciliation = date('Y-m-d',$time);
				// $entity->field_date_domiciliation[LANGUAGE_NONE][0]['value'] = $AnneInscrDomiciliation;
			// }
//
			// if(!empty($row->DateNaissance)){
				// $time = strtotime($row->DateNaissance);
				// $DateNaissance = date('Y-m-d',$time);
				// $entity->field_date_naissance[LANGUAGE_NONE][0]['value'] = $DateNaissance;
			// }
//
			// if(!empty($row->DatePremireCot)){
				// $time = strtotime($row->DatePremireCot);
				// $DatePremireCot = date('Y-m-d',$time);
				// $entity->field_date_prem_cotisation[LANGUAGE_NONE][0]['value'] = $DatePremireCot;
			// }
//
			// if (!empty($row->DateSEncodage)){
				// $time = strtotime($row->DateSEncodage);
				// $DateSEncodage = date('Y-m-d',$time);
				// $entity->field_date_prem_cotisation[LANGUAGE_NONE][0]['value'] = $DateSEncodage;
			// }

		}


		/**
		 * This function is called mmediately after the complete destination object is saved to save pargraph type contact data
		 * @param unknown $entity
		 * @param stdClass $row
		 */
		public function complete($entity, stdClass $row) {


			/** Save Mobile value as paragraph **/

			if(!empty($row->mobile_number))
			$phone_array = explode(',', $row->mobile_number);

			if(count($phone_array) > 0 ){

				// If $phone_array count > 1 then it have multiple mobile no
				if(count($phone_array) > 1){

					foreach ( $phone_array as $key => $val ) {

						$phone =  preg_replace('/[^0-9]/', '', $val);

						//Constructs the $paragraph entity object for telephone
						$paragraph = new ParagraphsItemEntity ( array (
								'field_name' => 'field_mobile_number',
								'bundle' => 'mobile_bundle'
						) );

						$paragraph->is_new = TRUE;
						$paragraph->setHostEntity ( 'crm_core_contact', $entity );
						$paragraph->field_contact_number [LANGUAGE_NONE] [0] ['value'] = $phone;
						$paragraph->save ();
					}

				 }else{

				 	$phone =  preg_replace('/[^0-9]/', '', $phone_array[0]);



					$paragraph = new ParagraphsItemEntity ( array (
							'field_name' => 'field_mobile_number',
							'bundle' => 'mobile_bundle'
					) );

					$paragraph->is_new = TRUE;
					$paragraph->setHostEntity ( 'crm_core_contact', $entity );
					$paragraph->field_contact_number [LANGUAGE_NONE] [0] ['value'] = $phone;

					echo '<pre>';print_r($entity);die('popop = '.$phone);

					$paragraph->save ();
				}

			}


			/** Save Email value as paragraph **/
			// $email = explode(';', $row->Email);
			// if(count($email) > 0 ){
//
				// // If $email count > 1 then it have multiple email
				// if(count($email) > 1){
//
					// foreach ($email as $key=>$val){
//
						// //Constructs the $paragraph entity object for email
						// $paragraph = new ParagraphsItemEntity ( array (
								// 'field_name' => 'field_mail',
								// 'bundle' => 'field_contact_email_bundle'
						// ) );
//
						// $paragraph->is_new = TRUE;
						// $paragraph->setHostEntity ( 'crm_core_contact', $entity );
						// $paragraph->field_contact_email [LANGUAGE_NONE] [0] ['value'] = utf8_encode($val);
						// $paragraph->save ();
					// }
//
				// }else{
//
					// $paragraph = new ParagraphsItemEntity ( array (
							// 'field_name' => 'field_mail',
							// 'bundle' => 'field_contact_email_bundle'
					// ) );
//
					// $paragraph->is_new = TRUE;
					// $paragraph->setHostEntity ( 'crm_core_contact', $entity );
					// $paragraph->field_contact_email [LANGUAGE_NONE] [0] ['value'] = utf8_encode($email[0]);
					// $paragraph->save ();
				// }

			//}

		 }


		/**
		 * Fetch Country code using country name
		 * @param unknown $val
		 */
		// function get_country_code($val){
			// $val = utf8_encode($val);
			// $countries = country_get_list();
		    // $country_code = 'PF';
		    // if(!empty($val)){
				// $country_code =  array_search(strtolower($val), array_map('strtolower', $countries));
		    // }
//
			// return $country_code;
		// }





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







	}
