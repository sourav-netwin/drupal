<?php

/***
 * This class is used to create data import script of CRM contact "homeowner association"
 * This class handles the details of performing the migration for you - iterating over source records, creating destination objects,
 * and keeping track of the relationships between them.
 * @author Netwininfo
 *
 */
class FmpAssociationMigration extends Migration
{
	public function __construct($arguments)
	{
		parent::__construct($arguments);
		$this->description = t('Import homeowner association data from FMP');
	    
		/** Define the mapping between a single row of source data and the resulting Drupal data **/
		$this->map = new MigrateSQLMap($this->machineName, array(
				'NumeroMembre' => array(
						'type' => 'varchar',
						'not null' => TRUE,
						'description' => t('Homeowner Association ID'),
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
		$this->destination = new MigrateDestinationCRMCoreContact('homeowner_association ');
		
		
		/** Define mappings from source fields to destination fields **/
		$this->addFieldMapping('contact_name', 'Nom')->callbacks('utf8_encode');
	
		// Map Address Field
		$this->addFieldMapping('field_contact_address','Pays')->callbacks(array($this, 'get_country_code_val'));
		$this->addFieldMapping('field_contact_address:thoroughfare', 'Adresse')->callbacks('utf8_encode');
		$this->addFieldMapping('field_contact_address:postal_code', 'CodePostal')->callbacks('utf8_encode');
		$this->addFieldMapping('field_contact_address:locality', 'Locality')->callbacks('utf8_encode'); 
		
		$this->addFieldMapping('field_main_contact', 'NumeroMembre')->callbacks('utf8_encode');
					
	}
	
	
	/**
	 * This method is called by the source class next() method, after loading the data row.
	 * The argument $row is object containing the raw data provided by the source.There are two primary reasons to implement prepareRow()
	 *  1. To modify the data row before it passes through any further methods and handlers.
	 *  2. To conditionally skip a row (by returning FALSE).
	 * {@inheritDoc}
	 * @see Migration::prepareRow()
	 */
	public function prepareRow($row)
	{
		$numeroMembre = 0;
		$numeroMembre = (int)$row->NumeroMembre;
		$row->NumeroMembre = $numeroMembre;
		
		$vme = 0;
		$vme = (int)$row->VME;
		$row->VME = $vme;
		
		/** 1. Skip those record whose VME != 1 & VME = "" 
		 *  2. Skip those record whose NumeroMembre value is blank
		 **/
		if( (strlen($numeroMembre) == 1) ||  ($vme != 1) || (strlen($vme) == 0)){
			return false;
		}
		
		/* if( ($vme != 1) || (strlen($vme) == 1)){
			return false;
		} */
		
	}
	
	
	/**
	 * Fetch Country code using country name
	 * @param unknown $val
	 */
	function get_country_code_val($val){
		$val = utf8_encode($val);
		$countries = country_get_list();
		$country_code = 'PF';
		if(!empty($val)){
			$country_code =  array_search(strtolower($val), array_map('strtolower', $countries));
		}
		 
		return $country_code;
	}
	
}
 