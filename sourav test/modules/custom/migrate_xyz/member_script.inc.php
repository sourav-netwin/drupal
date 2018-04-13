<?php

use Drupal\paragraphs\Entity\Paragraph;

class ExamplePageMigration extends Migration {

  public function __construct($arguments) {
  
    parent::__construct($arguments);
    
    
    //$this->dependencies = array('MigrateTextFieldHandler');
    
    $this->map = new MigrateSQLMap($this->machineName, array(
    		'sid' => array(
    				'type' => 'int',
    				'unsigned' => TRUE,
    				'not null' => TRUE,
    				'description' => t('Source ID'),
    		)
    ),
    		MigrateDestinationNode::getKeySchema()
    );
    
    /*$query = Database::getConnection('default', 'for_migration')
             ->select('example_pages')
             ->fields('example_pages', array('pgid', 'page_title', 'page_body'));
     */
    
    $file = 'import/test.csv';
    $columns = array();
    $options = array('header_rows' => 1);
     
    //$this->source = new MigrateSourceSQL($query);
    
    
   $this->source = new MigrateSourceCSV($file, $columns, $options);
	
   
   $this->destination = new MigrateDestinationNode('ere');
      
   /***
    * This line is required if we have to update record , it meand data with csv soucre id exist in db
    * 1. will check corresponding destid1 from "migrate_map_examplepage" for sourceid1 (sid of csv file)
    * 2. then will check data of node table for that destid1 (i.e nid),data should be update in table
    */ 
   
  // $this->addFieldMapping('nid', 'sid');
   
   
   $this->addFieldMapping('title', 'name')->callbacks(array($this, 'get_telephone'));
   //$this->addFieldMapping('body', 'desc');
   $this->addFieldMapping('uid', 'userid');
   
   $this->addFieldMapping('field_ere_phone', 'country');
   $this->addFieldMapping('field_ere_phone:thoroughfare', 'address');
   $this->addFieldMapping('field_ere_phone:locality', 'city');
   $this->addFieldMapping('field_ere_phone:postal_code', 'postalcode');

   // Category: taxonomy term reference. If you use tid, you need the second line, if you use name not
   $this->addFieldMapping('field_office', 'office');
   $this->addFieldMapping('field_office:source_type')
   ->defaultValue('tid');
   
   
   // $this->addFieldMapping('field_ere_mail', 'mail');
   
   
   
  }
  
  
  // Based on the entity
  public function complete($entity, stdClass $row) {
   	
  $mail = explode(';', $row->mail);
  foreach ($mail as $key=>$val){
				
			$node = node_load ( $entity->nid );
			
			$paragraph = new ParagraphsItemEntity ( array (
					'field_name' => 'field_ere_mail',
					'bundle' => 'field_contact_email_bundle' 
			) );
			$paragraph->is_new = TRUE;
			$paragraph->setHostEntity ( 'node', $node );
			$paragraph->field_contact_email [LANGUAGE_NONE] [0] ['value'] = $val;
			$paragraph->save ();
		}
   
  }
 
  protected function get_telephone($value)
  {
  	$value1 = explode('-', $value);
  
  	return preg_replace('/[^0-9]/', '', $value1[0]);
  }
  

  
}
