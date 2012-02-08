jQuery(function(){
	jQuery( '#wck_cfc_fields #field-type' ).change(function () {
		value = jQuery(this).val();
		
		if( value == 'select' || value == 'checkbox' || value == 'radio' ){
			jQuery( '#wck_cfc_fields .row-options' ).show();
		}
		else{
			jQuery( '#wck_cfc_fields .row-options' ).hide();
		}
	});
	
	jQuery( '#container_wck_cfc_fields #field-type' ).live( 'change', function () {
		value = jQuery(this).val();
		if( value == 'select' || value == 'checkbox' || value == 'radio' ){
			jQuery( '.hide-options', jQuery(this).parent().parent().parent() ).show();
		}
		else{
			jQuery( '.hide-options', jQuery(this).parent().parent().parent() ).hide();
		}
	});
});