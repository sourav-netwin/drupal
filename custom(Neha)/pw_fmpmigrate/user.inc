<?php

define('STATUS_DISABLED', 0);
define('STATUS_ACTIVE', 1);
define('SNPC_MEMBER_RID', 4); // DRUPAL_AUTHENTICATED_RID

class MigrateFMPUserMigration extends Migration {

	public function __construct($arguments) {
    
		parent::__construct($arguments);
    
	    $columns = array(
	    		0 => array('memberid', 'Member ID'),
	    		1 => array('email', 'Email'),
	    		2 => array('salutation', 'Email'),
	    		3 => array('lname', 'Last Name'),
	    		4 => array('fname', 'First Name'),
	    		5 => array('address', 'Address'),
	    		6 => array('city', 'City'),
	    		7 => array('postcode', 'Postal Code'),
	    		8 => array('country', 'Country'),
	    		9 => array('lang', 'Language'),
	    		10 => array('status', 'Status'),
	    		11 => array('subscriptionendmonth', 'Subscription End Month'),
	    		12 => array('subscriptionendyear', 'Subscription End Year'),    		
	    );
    
    $this->source = new MigrateSourceCSV('private://exportdrupal.csv', $columns, array('delimiter' => ',', enclosure => '"'), $this->addedFields());
    
    $this->destination = new MigrateDestinationUser(array());

    $this->map = new MigrateSQLMap($this->machineName,
        array(
          'memberid' => array('type' => 'int',
                          'unsigned' => TRUE,
                          'not null' => TRUE,
                          'description' => t('Source Member ID'),
                         )
        ),
        MigrateDestinationUser::getKeySchema()
      );
    
    // Core fields
    $this->addFieldMapping('name', 'username'); // Field to be created
    $this->addFieldMapping('mail', 'cleanemail'); // Field to created
    $this->addFieldMapping('pass', 'password'); // Field to be created
    $this->addFieldMapping('status', 'cleanstatus'); // Field to be created    
    $this->addFieldMapping('roles')
    	->defaultValue(SNPC_MEMBER_RID);   
    $this->addFieldMapping('language', 'fr');
    
    // Custom fields
    $this->addFieldMapping('field_memberid', 'memberid');
    $this->addFieldMapping('field_subscription_expiry', 'subscription_expiry'); // Field to be created
    $this->addFieldMapping('field_subscription_status', 'status'); // Field to be created
  }
  
  public function prepare(stdClass $entity, stdClass $row)
  {
  	// If user already exists, do not override his password
  	if (isset($entity->is_new) && $entity->is_new === FALSE) {
  		unset($entity->pass);
  	}
  }
  
  public function prepareRow($row) {
  	if (in_array($row->status, array('M', 'VME')))
  		$row->cleanstatus = STATUS_ACTIVE;
  	else
  		$row->cleanstatus = STATUS_DISABLED;
  	
  	//$row->fullname = ucfirst($row->fname) . ' ' . strtoupper($row->lname) .' (' . $row->memberid . ')';
  	
  	$row->password = $row->memberid;
  	
  	$emails = explode(';', $row->email);
  	$email = filter_var($emails[0], FILTER_SANITIZE_EMAIL);
  	if (filter_var($email, FILTER_VALIDATE_EMAIL))	  	
  		$row->cleanemail = $email;
  	else 
  		$row->cleanemail = $row->memberid.'@snpc-nems.be';
  	
  	$row->username = $row->cleanemail;
  	
  	$row->password = $row->memberid;
  	
  	if ($row->subscriptionendmonth == 12)
  	{
  		$endmonth = 1;
  		$endyear = $row->subscriptionendyear + 1;  		
  	}
  	else 
	{
  		$endmonth = $row->subscriptionendmonth + 1;
  		$endyear = $row->subscriptionendyear;
  	}
  	$row->subscription_expiry = $endyear.'-'.$endmonth.'-1';
  	
  }
  
  public function addedFields() {
  	return array(
  			'username' => 'A composite field made during prepareRow().',
  			'cleanemail' => 'A composite field made during prepareRow().',
  			'password' => 'An array of batters, populated during prepareRow().',
  			'subscription_expiry' => 'An array of batters, populated during prepareRow().',
  			'cleanstatus' => 'An array of batters, populated during prepareRow().',
  	);
  }  
}