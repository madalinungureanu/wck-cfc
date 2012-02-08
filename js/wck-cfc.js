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
});