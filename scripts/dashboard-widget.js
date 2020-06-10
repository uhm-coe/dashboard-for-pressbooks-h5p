/**
 * Wire up the dashboard widget.
 */

/* global Data, tippy, jQuery */
( function( $ ) {

	const $widget = $( '#d4ph_dashboard_widget' );

	// Add spinner to far right of widget title.
	$( 'h2.hndle span', $widget ).after( '<span class="postbox-title-action"><span class="spinner"></span></span>' );

	// Update user list when changing users per page dropdown.
	$widget.on( 'change', 'select.option', function () {
		const toUpdate = {};
		toUpdate[ $( this ).attr( 'name' ) ] = $( this ).val();
		refresh( $( this ), toUpdate );
	} );

	// Wire up pager elements (go to page text input, search text input).
	$widget.on( 'keydown', '#d4ph-current-page-selector, #d4ph-user-search-input', function( event ) {
		if ( event.which === 13 ) { // Enter key
			const searchTerm  = $( '#d4ph-user-search-input' ).val();
			const totalPages  = parseInt( $( '.total-pages' ).first().text().replace( /[^0-9]/g, '' ), 10 ) || 1;
			let currentPage   = parseInt( $( this ).val(), 10 ) || 1;

			// Make sure current page is between 1 and total pages.
			if ( currentPage < 1 ) {
				currentPage = 1;
			} else if ( currentPage > totalPages ) {
				currentPage = totalPages;
			}

			// Update user list with users on next page.
			const filters = {
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
	$widget.on( 'click', '.tablenav .pagination-links a, .tablenav #d4ph-search-submit', function( event ) {
		const searchTerm  = $( '#d4ph-user-search-input' ).val();
		const totalPages  = parseInt( $( '.total-pages' ).first().text().replace( /[^0-9]/g, '' ), 10 ) || 1;
		let currentPage   = parseInt( getParameterByName( 'paged', $( this ).attr( 'href' ) ), 10 ) || 1;
		if ( currentPage > totalPages ) {
			currentPage = totalPages;
		}

		// Update user list with users on next page.
		const filters = {
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
			onShown() {
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
	 *
	 * @param  {Object} $caller jQuery object that triggered this refresh.
	 * @param  {Object} filters Optional filters for query (page, role, etc.)
	 */
	function refresh( $caller, filters = {} ) {
		$( '.spinner', $widget ).addClass( 'is-active' );
		$caller.attr( 'disabled', 'disabled' );
		$.post( Data.ajax_url, {
			nonce:    Data.nonce,
			dataType: 'jsonp',
			action:   'd4ph_dashboard_widget_update',
			filters,
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

	/**
	 * Helper function to grab a querystring param value by name.
	 *
	 * @param  {string} needle   Param name to find.
	 * @param  {string} haystack Query string to search.
	 *
	 * @return {string}          Param value if found; empty string otherwise.
	 */
	function getParameterByName( needle, haystack = '' ) {
		needle = needle.replace( /[\[]/, '\\\[').replace(/[\]]/, '\\\]' ); // eslint-disable-line no-useless-escape
		const regex = new RegExp( '[\\?&]' + needle + '=([^&#]*)' );
		const results = regex.exec( haystack );

		return results === null ? '' : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
	}

} )( jQuery );
