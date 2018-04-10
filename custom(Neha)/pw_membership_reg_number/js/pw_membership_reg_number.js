/**
  * Show a registration number input if prolongation.
  */
console.log(window.location.pathname);

if(window.location.pathname == '/content/cotisation-pour-particuliers' || window.location.pathname == '/fr/content/cotisation-pour-particuliers'){
	
	console.log('initiate pw membership reg number module');
	
	(function(jQuery) {
		//loopInResponses();
		jQuery('.commerce-cart-add-to-cart-form-113-157-158 input[type=radio]').first().prop('checked', true);
	})(jQuery);
	
	
	Drupal.behaviors.PWMembershipRegNumber = {
			attach: function (context, settings) {
				
				jQuery('.form-item-quantity').hide();
				
				//console.log(settings);
	
				if(context[0] != 'undefined' &&
				  context[0].className.length > 0 &&
				  context[0].className == "commerce-add-to-cart commerce-cart-add-to-cart-form-113-157-158"
					  ){
				  
				  loopInResponses();
				  
				  jQuery(".form-submit").attr("disabled", "disabled");
				  
				  jQuery("#registration-number-field").blur(function(){
					  console.log("blur");
					  if( jQuery(this).val().length <  5) {
						  jQuery(this).next('p').html('Veuillez entrer un numéro de membre valide');
						  jQuery("#registration-number-field").addClass('pw-membership-input-warning');
					  }else{
						  jQuery(".form-submit").removeAttr("disabled");
						  jQuery("#registration-number-field").removeClass('pw-membership-input-warning');
						  jQuery(this).next('p').html('');
					  }
				  })
				  
				}
			}
	};
	
	function loopInResponses(){
		
		 jQuery('.commerce-cart-add-to-cart-form-113-157-158 input[type=radio]').each(function(){
			  if(jQuery(this).is(':checked')){
				  
				  var regex = new RegExp("[a-z]-([0-9]+)");
				  var capture = 1;
				  var m = regex.exec(jQuery(this).attr('id'));
				  
				  if(m[capture] == '640'){
				  
					  jQuery(this).once('set_reg_number',function(){
						  jQuery(this)
						  .next('label')
						  .after(
								  '<div class="form-item pw-membership-form-item" style="margin-left: 20px;">'+
								  '<label class="pw-membership-label" for="registration_number">Numéro de membre</label>' +
								  '<input class="form-control form-text required" id="registration-number-field" type="text" name="registration_number" />' +
								  '<p class="pw-membership-error"></p>' +
								  '</div>'
						  		);
					  });
					  
				
				
				  }
				  if(m[capture] == '641'){
					  
					  jQuery(this).once('set_reg_number',function(){
						  jQuery(this)
						  .next('label')
						  .after(
								  '<div class="form-item pw-membership-form-item" style="margin-left: 20px;">'+
								  '<label class="pw-membership-label" for="registration_number">Numéro de membre</label>' +
								  '<input class="form-control form-text required" id="registration-number-field" type="text" name="registration_number" />' +
								  '<p class="pw-membership-error"></p>' +
								  '<div>Accéder au mandat de domiciliation SEPA : <a href="https://snpc-web.s3-eu-west-1.amazonaws.com/domiciliation%20document.pdf">cliquez ici</a></div>' +
								  '</div>'
						  		);
					  });
					  
				  }
			  }
		  });
		
	}
}




