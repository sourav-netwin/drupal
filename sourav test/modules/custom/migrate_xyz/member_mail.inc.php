<?php

use Drupal\paragraphs\Entity\Paragraph;

class ExampleMailMigration extends Migration {/* 

  public function __construct($arguments) {
  	
  	ECHO "FDGF";EXIT;
  
    parent::__construct($arguments);
    
    $this->map = new MigrateSQLMap($this->machineName, array(
    		'sid' => array(
    				'type' => 'int',
    				'unsigned' => TRUE,
    				'not null' => TRUE,
    				'description' => t('Source ID'),
    		)
    ),
    		  MigrateDestinationParagraphsItem::getKeySchema()
    );
    
    
    $file = 'import/test.csv';
    $columns = array();
    $options = array('header_rows' => 1);
     
    
    
    $this->source = new MigrateSourceCSV($file, $columns, $options);
	
   
    $paragraph_options = MigrateDestinationParagraphsItem::options('en', 'filtered_html');
    $paragraph_options['field_name'] = 'field_ere_email';
    
    $this->destination = new MigrateDestinationParagraphsItem('field_contact_email_bundle', $paragraph_options);
    $this->addFieldMapping('field_ere_mail', 'mail');
    

 

  }
  

  
 */}
