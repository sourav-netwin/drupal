<?php
function pw_shipping_rate_rules_action_info() {
  return array(
    'pw_commerce_actions' => array(
      'label' => t('PW shipping rate Rule Action'),
      'parameter' => array(

	     'commerce_line_item' => array(
	     		'type' => 'commerce_line_item',
	     		'label' => t('commerceLineItems'),
	     		'description' => t('this data will be used for this action')
	     ),
      ),
      'provides' => array(
		'shipping_cost_for_line_item' => array(
				'type' => 'decimal',
				'label' => t('Calculated shipping cost'),
		),
       ),
      'configurable' => FALSE,
      'group' => t('sample Custom module action'),
      'callbacks' => array(
        'execute' => 'calculate_shipping_cost_on_weight_action',
      ),
    ),
  );
}
  
  function calculate_shipping_cost_on_weight_action($commerce_line_item){
  	
  	global $user;
  	$user = user_load($user->uid);
  	$order = commerce_cart_order_load($user->uid);
  	
  	$commerce_items = [];
  	$commerce_items['bpack'] = false;
  	
  	foreach($order->commerce_line_items['und'] as $k => $v){

  		$line_item = commerce_line_item_load($v['line_item_id']);
  		if( $line_item->line_item_label != 'Frais de port fixes'){
	  		
	  		$sku = $line_item->line_item_label;
	  		$product = commerce_product_load_by_sku($sku);

	  		$commerce_items['products'][$k]['total_price'] = $line_item->commerce_total['und'][0]['amount']/100;
	  		$commerce_items['products'][$k]['product_id'] = $product->product_id;
	  		$commerce_items['total_weight'] += $product->field_weight['und'][0]['weight'] * $line_item->quantity;
	  		

	  		if(
  				$commerce_items['products'][$k]['product_id'] == 49 ||
  				$commerce_items['products'][$k]['product_id'] == 50 || 
  			    $commerce_items['products'][$k]['product_id'] == 51 ||
  				$commerce_items['products'][$k]['product_id'] == 52
  				){
  					$commerce_items['bpack'] = true;
	  		}
  		}
  		
  	}

	if($commerce_items['total_weight'] == 0){
		$shipping_cost = 0;
	}elseif(count($commerce_items['products']) == 1 && $commerce_items['products'][0]['product_id'] == 147){
  		$shipping_cost = 2.37;
  	}else{
  		
		if($commerce_items['total_weight'] >= 2000 && $commerce_items['bpack'] == false)
			$commerce_items['bpack'] = true;
			
	  	
	  	if($commerce_items['bpack'] == false){
	  	
		  	if($commerce_items['total_weight'] < 350){
		  		$shipping_cost = 2.37;
		  	}
		  	if($commerce_items['total_weight'] >= 350 && $commerce_items['total_weight'] < 1000){
		  		$shipping_cost = 3.95;
		  	}
		  	if($commerce_items['total_weight'] >= 1000 && $commerce_items['total_weight'] < 2000){
		  		$shipping_cost = 5.53;
		  	}
	  	}else{
	  		
	  		if($commerce_items['total_weight'] < 2000){
	  			$shipping_cost = 6.50;
	  		}
	  		if($commerce_items['total_weight'] >= 2000 && $commerce_items['total_weight'] < 10000){
	  			$shipping_cost = 8.80;
	  		}
	  		if($commerce_items['total_weight'] >= 10000){
	  			$shipping_cost = 14.90;
	  		}
	  		
	  	}
  	}
  	//die("shipping_cost=".$commerce_items['total_weight']);
  	return array('shipping_cost_for_line_item' => $shipping_cost*100);
  	
  }
