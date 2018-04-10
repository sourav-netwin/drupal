<?php

//error_log('OKKKK --- ' . $_POST['jid']);
// CHANGE : update status to downloaded when "Download" is clicked

$jid = $_POST['jid'];

$upd = db_update('matching_data')
	->fields(array(
		'status' => 'downloaded',
		)
	)
	->condition('jid', $jid)
	->execute();

?>
