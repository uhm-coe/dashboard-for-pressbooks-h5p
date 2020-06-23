/**
 * Blur H5P Content and add login button for anonymous users.
 */

/* global Data, jQuery */
( function( $ ) {

	$( document ).ready( function() {
		// Blur (and prevent interaction with) H5P activities for anonymous users.
		// Also show a message instructing students to log in to access them.
		$( '.h5p-iframe-wrapper, .h5p-content', 'body.anonymous' ).prepend( function() {
			let loginUrl = Data.loginUrl;

			// If the H5P element has a child with an id, append that as a named
			// ahcnor in the redirect URL in the login URL so we can come back it.
			$( this ).children( '[id]:first' ).each( function () {
				loginUrl += '%23' + $( this ).attr( 'id' );
			} );

			return '<p class="h5p-restricted-message"><a href="' + loginUrl + '">' + Data.msgLogInToComplete + '</a></p>';
		} );
	} );

} )( jQuery );
