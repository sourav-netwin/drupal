<?php

/***
 * This class is create data import script of CRM Activity "pw_mail_out"
 * This class handles the details of performing the migration for you - iterating over source records, creating destination objects,
 * and keeping track of the relationships between them.
 * @author Netwininfo
 *
 */
class FmpMailOutMigration extends XMLMigration {
	
	public function __construct($arguments) {
		
		parent::__construct($arguments);
		$this->description = t('Migrate EMail Out Activity Data from XML');

		$fields = array();
		
		/** Define the mapping between a single row of source data and the resulting Drupal data **/
		$this->map = new MigrateSQLMap($this->machineName,
				array(
						'Id' => array(
						'type' => 'varchar',
						'not null' => TRUE,
						'description' => t('Activity ID'),
						'length'=>256,
						)
				),
				MigrateDestinationCRMCoreActivity::getKeySchema()
			);
		
		
	$items_url = 'import/courier/courier.xml';
	//$items_url = 'import/courier/courier-test.xml';
		
    // Our test data is in an XML file
    $item_xpath = '/CourierTable/CourierRow';
    $item_ID_xpath = 'Id';
    
    /** Define where the source data is coming from **/
    $this->source = new MigrateSourceXML($items_url, $item_xpath, $item_ID_xpath,$fields);
   
    /** Define the destination object type  **/
	$this->destination = new MigrateDestinationCRMCoreActivity('pw_email_out');
	
	
	/** Define mappings from source fields to destination fields **/
	
	$this->addFieldMapping('field_membre_id', 'NrMembre')
	->xpath('NrMembre'); 

	$this->addFieldMapping('field_eout_creationdate', 'CreationDate')
		       ->xpath('CreationDate')->callbacks(array($this, 'get_formatted_date'));
	       		       
	$this->addFieldMapping('field_eout_modificationdate', 'Modificationdate')
		       ->xpath('Modificationdate')->callbacks(array($this, 'get_formatted_date'));
	
	$this->addFieldMapping('field_activity_date', 'Date')
		       ->xpath('Date')->callbacks(array($this, 'get_formatted_date'));
		
	$this->addFieldMapping('field_email_out_nom', 'Nom')
		       ->xpath('Nom');
		       		       
	$this->addFieldMapping('field_eout_prenom', 'Prenom')
		       ->xpath('Prenom'); 
	
	$this->addFieldMapping('field_eout_adresse', 'Adresse')
		       ->xpath('Adresse');
	
	$this->addFieldMapping('field_eout_pays', 'Pays')
		       ->xpath('Pays');
		       
	$this->addFieldMapping('field_cp', 'CP')
		       ->xpath('CP');
	
	$this->addFieldMapping('field_eout_localite', 'Localite')
		       ->xpath('Localite');
	
	$this->addFieldMapping('field_eout_politesse', 'Politesse')
		       ->xpath('Politesse');
		       
	$this->addFieldMapping('field_brusselledate', 'Brusselledate')
		       ->xpath('Brusselledate');
	
	 $this->addFieldMapping('field_politesse2', 'Politesse2')
		       ->xpath('Politesse2');	      
		       		        
	 $this->addFieldMapping('field_lettre', 'Lettre')
		       ->xpath('Lettre');
	 
	 $this->addFieldMapping('field_nr_ref_', 'NrRef.')
		       ->xpath('NrRef.'); 
	
	 $this->addFieldMapping('field_nrjuriste', 'Nr.Juriste')
		       ->xpath('Nr.Juriste');
		       
	 $this->addFieldMapping('field_formuledepolitesse', 'FormuledePolitesse')
		       ->xpath('FormuledePolitesse');
	
	 $this->addFieldMapping('field_aes', 'AES')
		       ->xpath('AES');
		        		       
	 $this->addFieldMapping('field_fax', 'Fax')
		       ->xpath('Fax');
	
	 $this->addFieldMapping('field_nrpage', 'NrPage')
		       ->xpath('NrPage');
		       
	 $this->addFieldMapping('field_montant', 'Montant')
		       ->xpath('Montant');
	
	 $this->addFieldMapping('field_politesse3', 'Politesse3')
		       ->xpath('Politesse3');
	
	 $this->addFieldMapping('field_reference', 'Reference')
		       ->xpath('Reference');

	 $this->addFieldMapping('field_nomlettretype', 'NomLettreType')
		       ->xpath('NomLettreType');
			       
	 $this->addFieldMapping('field_lignenomsuppl', 'Lignenomsuppl')
		       ->xpath('Lignenomsuppl'); 
		        		        		        		       
	 $this->addFieldMapping('uid')->defaultValue(1);
	
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
  	$numeroMembre = (int)$row->xml->NrMembre;
	   
  	/** 
  	 *  1. Skip those record whose NumeroMembre value is blank
  	 **/
  	if ( strlen($numeroMembre) == 1) {
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
  public function prepare($entity, stdClass $row){
  
  	/** Set destination object for Creator & Modifier field **/
		if(!empty($row->xml->Creator)){
			$termname = ucfirst($row->xml->Creator);
			$vocabulary = 'Creator';
			
			/** Get vocabulary detail using name **/
			$vocab = taxonomy_vocabulary_machine_name_load($vocabulary);
			$vid = $vocab->vid;
			
			/** Create Term & return TermId **/
			$termId = $this->custom_create_taxonomy_term($termname,$vid);
			$entity->field_creator [LANGUAGE_NONE] [0] ['value'] = $termId;
		}
		
		
		if(!empty($row->xml->Modifier)){
			$termname = ucfirst($row->xml->Modifier);
			$vocabulary = 'Modifier';
			
			/** Get vocabulary detail using name **/
			$vocab = taxonomy_vocabulary_machine_name_load($vocabulary);
			$vid = $vocab->vid;
			
			/** Create Term & return TermId **/
			$termId = $this->custom_create_taxonomy_term($termname,$vid);
			$entity->field_modifier [LANGUAGE_NONE] [0] ['value'] = $termId;
		}
			
  	
  }
   
    
  /**
   * This Funciton is used to create formatted DateTime object 
   * @param unknown $date
   * @return unknown $newDateString
   */
  function get_formatted_date($date){
  	$dateTime = \DateTime::createFromFormat('Y-m-d',$date);
  	$newDateString = $dateTime->format('Y-m-d H:i:s');
  	return $newDateString;
  }
  
  /**
   * This Function is used to create term of vocabulary
   * @param unknown $name
   * @param unknown $vid
   * @return unknown $term->tid
   */
  function custom_create_taxonomy_term($name, $vid) {
  	$term_name = taxonomy_get_term_by_name($name);
  	if (is_array($term_name)) {
  		$term_name = array_values($term_name)[0];
  		if (isset($term_name->name)) {
  			//if there is a term, return the id of the term
  			return $term_name->tid;
  		}else{
  			//if there is no term it creates the term and returns the id
  			$term = new stdClass();
  			$term->name = $name;
  			$term->vid = $vid;
  			taxonomy_term_save($term);
  			return $term->tid;
  		}
  	}else{
  		//if there is no term it creates the term and returns the id
  		$term = new stdClass();
  		$term->name = $name;
  		$term->vid = $vid;
  		taxonomy_term_save($term);
  		return $term->tid;
  	}
  }
  		
}


