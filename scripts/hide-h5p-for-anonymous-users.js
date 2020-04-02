/**
 * Blur H5P Content and add login button for anonymous users.
 */

/* global Data, jQuery */
( function( $ ) {

	$( document ).ready( function() {
		// Blur (and prevent interaction with) H5P activities for anonymous users.
		// Also show a message instructing students to log in to access them.
		$( 'body.anonymous iframe.h5p-iframe' ).before( function() {
			const loginUrl = Data.loginUrl + '%23' + $( this ).attr( 'id' );
			return '<p class="h5p-restricted-message"><a href="' + loginUrl + '">' + Data.msgLogInToComplete + '</a></p>';
		} );
	} );

} )( jQuery );
