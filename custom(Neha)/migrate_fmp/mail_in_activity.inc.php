<?php

/***
 * This class is create data import script of CRM Activity "pw_mail_in"
 * This class handles the details of performing the migration for you - iterating over source records, creating destination objects,
 * and keeping track of the relationships between them.
 * @author Netwininfo
 *
 */
class FmpMailInMigration extends XMLMigration {
	
	public function __construct($arguments) {
		
		parent::__construct($arguments);
		$this->description = t('Migrate EMail In Activity Data from XML');

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
     
		
	$items_url = 'import/lcourier/ListCourier.xml';
		
    // Our test data is in an XML file
    $item_xpath = '/ListCourierTable/ListCourierRow';
    $item_ID_xpath = 'Id';
    
    /** Define where the source data is coming from **/
    $this->source = new MigrateSourceXML($items_url, $item_xpath, $item_ID_xpath,$fields);
   
    /** Define the destination object type  **/
	$this->destination = new MigrateDestinationCRMCoreActivity('pw_mail_in');
		
	/** Define mappings from source fields to destination fields **/
	
	$this->addFieldMapping('field_nr_membre', 'NrMembre')
	->xpath('NrMembre'); 
	
	$this->addFieldMapping('field_nr', 'Nr')
		       ->xpath('Nr');
	
	 $this->addFieldMapping('field_nom', 'Nom')
		       ->xpath('Nom'); 
		       
	$this->addFieldMapping('field_demand_', 'Demande')
		       ->xpath('Demande');
	
	 $this->addFieldMapping('field_pay_', 'Paye')
		       ->xpath('Paye');	      
		       		        
	/* $this->addFieldMapping('field_moyenpayement', 'MoyenPayement')
		       ->xpath('MoyenPayement')->callbacks('utf8_encode'); */
	 
	$this->addFieldMapping('field_nr_juriste', 'Nr.Juriste')
		       ->xpath('Nr.Juriste'); 
	
	$this->addFieldMapping('field_datepayement', 'DatePayement')
		       ->xpath('DatePayement')->callbacks(array($this, 'get_formatted_date')); 
		       	
	$this->addFieldMapping('field_dateenvoi', 'DateEnvoi')
		       ->xpath('DateEnvoi')->callbacks(array($this, 'get_formatted_date')); 
		       
	$this->addFieldMapping('field_activity_date', 'Date')
		       ->xpath('Date')->callbacks(array($this, 'get_formatted_date'));
	
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
  public function prepare($entity, stdClass $row)
  {
		/**
		 * Set destination object for taxonomy term refrence field *
		 */
		if (! empty ( $row->xml->MoyenPayement )) {

			$termname = ucfirst($row->xml->MoyenPayement);
			$vocabulary = 'MoyenPayement';
				
			/** Get vocabulary detail using name **/
			$vocab = taxonomy_vocabulary_machine_name_load($vocabulary);
			$vid = $vocab->vid;
				
			/** Create Term & return TermId **/
			$termId = $this->custom_create_taxonomy_term($termname,$vid);
			$entity->field_moyenpayement [LANGUAGE_NONE] [0] ['value'] = $termId;
			
		}
	}
    		


  /**
   * This Funciton is used to create formatted DateTime object
   * @param unknown $date
   * @return unknown $newDateString
   */
  function get_formatted_date($date=null){
  		$newDateString = null;
    	if($date != null){
			$dateTime = \DateTime::createFromFormat ( 'Y-m-d', $date );
			$newDateString = $dateTime->format ( 'Y-m-d H:i:s' );
		}
		return $newDateString;
		
  }
  
    
}


