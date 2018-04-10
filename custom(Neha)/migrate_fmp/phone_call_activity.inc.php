<?php


/***
 * This class is create data import script of CRM Activity "pw_phonecall"
 * This class handles the details of performing the migration for you - iterating over source records, creating destination objects,
 * and keeping track of the relationships between them.
 * @author Netwininfo
 *
 */
class FmpPhonecallMigration extends XMLMigration {
	
	public function __construct($arguments) {
		
		parent::__construct($arguments);
		$this->description = t('Migrate Phonecall Data from XML');

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

		
	//$items_url = 'import/juridique/test.xml';

	$items_url = 'import/juridique/juridique.xml';
		
    // Our test data is in an XML file
    $item_xpath = '/juridique/juridiqueitem';
    $item_ID_xpath = 'Id';
    
    /** Define where the source data is coming from **/
    $this->source = new MigrateSourceXML($items_url, $item_xpath, $item_ID_xpath,$fields);
   
    /** Define the destination object type  **/
	$this->destination = new MigrateDestinationCRMCoreActivity('pw_phonecall');
	
	
	/** Define mappings from source fields to destination fields **/
	$this->addFieldMapping('field_nrmembre', 'NrMembre')
	->xpath('NrMembre'); 
	
	$this->addFieldMapping('field_mot_cl_premier_niveau', 'Keyword1')
		       ->xpath('Keyword1');
	
	$this->addFieldMapping('field_mot_cl_second_niveau', 'Keyword2')
		       ->xpath('Keyword2');	
	
	$this->addFieldMapping('field_validation', 'Validation')
		       ->xpath('Validation');
		       
	$this->addFieldMapping('field_probl_me', 'Problem')
		       ->xpath('Problem');
	
	$this->addFieldMapping('field_commentaires', 'Comments')
		       ->xpath('Comments');		      
		       		        
	$this->addFieldMapping('field_commentaire_active', 'ActiveComment')
		       ->xpath('ActiveComment');
		       
	$this->addFieldMapping('uid')->defaultValue(1);
		
  }
	
	
  /**
   * This method is called to manipulate the destination object before it is saved to the Drupal database
   * The $entity argument is destination object as populated by the initial field mappings and manipulated by the field-level methods;
   * The $row argument is an object containing the data after prepareRow() and any callbacks have been applied.
   * @param unknown $entity
   * @param stdClass $row
   */
	public function prepare($entity, stdClass $row){

		/*echo "<pre>";
		print_r($row);
        die();*/

		/**  Set destination object for all date type field **/
		if(!empty($row->xml->CreationDate)){
			//$creationDate = DateTime::createFromFormat ( "Y-m-d", $row->xml->CreationDate);
			//$creation_date= $creationDate->format ( 'Y-m-d' );
			$entity->field_creationdate [LANGUAGE_NONE] [0] ['value'] = $row->xml->CreationDate;
		}
		
		if( !empty($row->xml->ModificationDate) && ($row->xml->ModificationDate != 0 ) ){

			//echo $row->xml->ModificationDate." neha ";die;
			$modificationDate = DateTime::createFromFormat ( "Y-m-d", $row->xml->ModificationDate);
			$modification_date= $modificationDate->format ( 'Y-m-d' );

			$entity->field_modificationdate [LANGUAGE_NONE] [0] ['value'] = $modification_date;
		}
		
		if(!empty($row->xml->Date)){
			//$date= DateTime::createFromFormat ( "Y-m-d", $row->xml->Date);
			//$date_val= $date->format ( 'Y-m-d' );
			$entity->field_activity_date [LANGUAGE_NONE] [0] ['value'] = $row->xml->Date;
		}
		
		if(!empty($row->xml->Jurist)){
			//$jurist= DateTime::createFromFormat ( "Y-m-d", $row->xml->Jurist);
			//$jurist_val= $jurist->format ( 'Y-m-d' );
			$entity->field_jurist [LANGUAGE_NONE] [0] ['value'] = $row->xml->Jurist;
		} 
		
		// Set destination object for Commentaire active field of activity
		/* $entity->field_commentaire_active [LANGUAGE_NONE] [0] ['value'] = 0;
		if(!empty($row->xml->Comments)){
			$entity->field_commentaire_active [LANGUAGE_NONE] [0] ['value'] = 1;
		} */
		
		if(!empty($row->xml->Statut)){
			$termname = ucfirst($row->xml->Statut);
			$vocabulary = 'statut';
			$vocab = taxonomy_vocabulary_machine_name_load($vocabulary);
			$vid = $vocab->vid;
			$termId = $this->custom_create_taxonomy_term($termname,$vid);
			$entity->field_activity_statut [LANGUAGE_NONE] [0] ['value'] = $termId;
		}
		
		
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
	
	
		
	/**
	 * This Function is used to create custom vocabulary 
	 * @param unknown $vocabularyName
	 * @return $vid
	 */
	function create_custom_vocabulary($vocabularyName){
		
		$vocab_object = taxonomy_vocabulary_machine_name_load($vocabularyName);
		$vid = $vocab_object->vid;
		
		if(!empty($vid)){
			return  $vid;
		}else{
			$vocabulary = new stdClass ();
			$vocabulary->name = $vocabularyName;
			$vocabulary->machine_name = $vocabularyName;
			taxonomy_vocabulary_save ( $vocabulary );
			$vid = $vocabulary->vid;
			return $vid;
		}
	}
			
	
}


