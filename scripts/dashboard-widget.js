/**
 * Wire up the dashboard widget.
 */

/* global Data, tippy, jQuery */
( function( $ ) {

	var $widget = $( '#p22d_dashboard_widget' );

	// Add spinner to far right of widget title.
	$( 'h2.hndle span', $widget ).after( '<span class="postbox-title-action"><span class="spinner"></span></span>' );

	// Update user list when changing users per page dropdown.
	$widget.on( 'change', 'select.option', function ( event ) {
		var toUpdate = {};
		toUpdate[ $( this ).attr( 'name' ) ] = $( this ).val();
		refresh( $( this ), toUpdate );
	} );

	// Wire up pager elements (go to page text input, search text input).
	$widget.on( 'keydown', '#p22d-current-page-selector, #p22d-user-search-input', function( event ) {
		if ( event.which === 13 ) { // Enter key
			var searchTerm  = $( '#p22d-user-search-input' ).val();
			var currentPage = parseInt( $( this ).val(), 10 ) || 1;
			var totalPages  = parseInt( $( '.total-pages' ).first().text().replace( /[^0-9]/g, '' ), 10 ) || 1;

			// Make sure current page is between 1 and total pages.
			if ( currentPage < 1 ) {
				currentPage = 1;
			} else if ( currentPage > totalPages ) {
				currentPage = totalPages;
			}

			// Update user list with users on next page.
			var filters = {
				page:   currentPage,
				search: searchTerm,
			};
			refresh( $( this ), filters );

			// Prevent default behavior.
			event.preventDefault();
			return false;
		}
	});

	// Wire up pager buttons (first/last/next/previous and search buttons).
	$widget.on( 'click', '.tablenav .pagination-links a, .tablenav #p22d-search-submit', function( event ) {
		var searchTerm  = $( '#p22d-user-search-input' ).val();
		var currentPage = parseInt( getParameterByName( 'paged', $( this ).attr( 'href' ) ), 10 ) || 1;
		var totalPages  = parseInt( $( '.total-pages' ).first().text().replace( /[^0-9]/g, '' ), 10 ) || 1;
		if ( currentPage > totalPages ) {
			currentPage = totalPages;
		}

		// Update user list with users on next page.
		var filters = {
			page:   currentPage,
			search: searchTerm,
		};
		refresh( $( this ), filters );

		// Remove focus from clicked element.
		$( this ).blur();

		// Prevent default behavior.
		event.preventDefault();
		return false;
	});

	initTooltips();

	/**
	 * Load tooltips.
	 */
	function initTooltips() {
		tippy( '[data-tippy-content]', {
			trigger: 'click',
			allowHTML: true,
			placement: 'left',
			theme: 'light-border',
			interactive: true,
			onShown( instance ) {
				// Load any nested tooltips.
				tippy( '.tippy-content [data-tippy-content]', {
					trigger: 'click',
					allowHTML: true,
					placement: 'left',
					// theme: 'light-border',
					interactive: true,
				} );
			}
		} );
	}

	/**
	 * Render the user list.
	 */
	function refresh( $caller, filters = {} ) {
		$( '.spinner', $widget ).addClass( 'is-active' );
		$caller.attr( 'disabled', 'disabled' );
		$.post( Data.ajax_url, {
			nonce:    Data.nonce,
			dataType: 'jsonp',
			action:   'p22d_dashboard_widget_update',
			filters:  filters,
		} ).done( function ( response ) {
			if ( response.success ) {
				// Render.
				$( '.users', $widget ).replaceWith( response.html );
				initTooltips();
			}
		} ).fail( function () {
		} ).always( function () {
			$( '.spinner', $widget ).removeClass( 'is-active' );
			$caller.removeAttr( 'disabled' );
		} );
	}

	// Helper function to grab a querystring param value by name
	function getParameterByName( needle, haystack ) {
		needle = needle.replace( /[\[]/, '\\\[').replace(/[\]]/, '\\\]' ); // eslint-disable-line no-useless-escape
		var regex = new RegExp( '[\\?&]' + needle + '=([^&#]*)' );
		var results = regex.exec( haystack );
		if ( results === null ) {
			return '';
		} else {
			return decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
		}
	}

} )( jQuery );
