jQuery(function(){
	jQuery( '#wck_cfc_fields #field-type' ).change(function () {
		value = jQuery(this).val();
		
		if( value == 'select' || value == 'checkbox' || value == 'radio' ){
			jQuery( '#wck_cfc_fields .row-options' ).show();
		}
		else{
			jQuery( '#wck_cfc_fields .row-options' ).hide();
		}
		
		if( value == 'upload' ){
			jQuery( '#wck_cfc_fields .row-attach-upload-to-post' ).show();
		}
		else{
			jQuery( '#wck_cfc_fields .row-attach-upload-to-post' ).hide();
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
		
		if( value == 'upload' ){
			jQuery( '.hide-attach', jQuery(this).parent().parent().parent() ).show();
		}
		else{
			jQuery( '.hide-attach', jQuery(this).parent().parent().parent() ).hide();
		}
	});
});