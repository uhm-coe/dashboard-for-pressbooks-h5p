/**
 * UI wiring for Pressbooks Organize page.
 */

/* global Data, tippy, jQuery */
( function( $ ) {

	$( document ).ready( function() {
		// Blur (and prevent interaction with) H5P activities for anonymous users.
		// Also show a message instructing students to log in to access them.
		$( 'body.anonymous iframe.h5p-iframe' ).before( function() {
			var loginUrl = p22d.loginUrl + '%23' + $( this ).attr( 'id' );
			var html =
				'<p class="h5p-restricted-message">' +
				'  <a href="' + loginUrl + '">' + p22d.msgLogInToComplete + '</a>' +
				'</p>';
			return html;
		} );
	} );

} )( jQuery );
