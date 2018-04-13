<?php



class ExampleTestMigration extends Migration {

  public function __construct($arguments) {

    parent::__construct($arguments);


	//define('DESTINATION' , 155);
 	//constant DESTINATION = 2;
 	//$this->systemOfRecord = Migration::DESTINATION;

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



     $file = 'import/test.csv';
     $columns = array();
     $options = array('header_rows' => 1);

   $this->source = new MigrateSourceCSV($file, $columns, $options);

  $this->destination = new MigrateDestinationNode('employee_info');


  //$this->addFieldMapping('nid', 'sid');


    $this->addFieldMapping('title', 'name');


  }

}
