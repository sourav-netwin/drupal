<?php
/*
function pw_docgen_rule_action($order)
{
	error_log('In rule 1');
	$uid = get_uid_from_order($order->order_id);
	sync_user($uid);
	$prods = get_products_from_order($order);
	//var_dump($prods);exit();
	foreach ($prods as $prod)
	{
		$jdoc_id = create_doc_inj($prod->field_joomla_form_id['und'][0]['value'], $uid);
		add_matching_data($order->order_id, $uid, $jdoc_id, $prod->skuid, $prod->title);
	}
	error_log('In rule 2');
}
*/
