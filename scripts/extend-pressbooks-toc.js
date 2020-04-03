/**
 * UI wiring for Pressbooks table of contents.
 */

/* global Data, tippy, jQuery */
( function( $ ) {

	const $lis = $( 'li.toc__front-matter, li.toc__chapter, li.toc__back-matter' );

	// Add H5P badge to each chapter's column.
	$lis.each( function () {
		const chapter_id = $( this ).attr( 'id' ).replace( 'toc-front-matter-', '' ).replace( 'toc-chapter-', '' );
		const count = Data.h5p_ids.hasOwnProperty( chapter_id ) ? Object.keys( Data.h5p_ids[ chapter_id ] ).length : 0;
		if ( count > 0 ) {
			let completed = 0;
			let tooltip   = '<ol>';
			for ( const h5p_id in Data.h5p_ids[ chapter_id ] ) {
				// Calculate score; mark completed if result exists and
				// score > max_score * passing_percentage.
				let score = '—';
				if ( Data.h5p_results.hasOwnProperty( h5p_id ) ) {
					score = Math.round( Data.h5p_results[ h5p_id ]['score'] / Data.h5p_results[ h5p_id ]['max_score'] * 100 ) + '%';
					if ( Data.h5p_results[ h5p_id ]['score'] >= Data.h5p_results[ h5p_id ]['max_score'] * Data.h5p_ids[ chapter_id ][ h5p_id ]['passing'] / 100 ) {
						completed++;
					}
				}

				tooltip += "<li>" + Data.h5p_ids[ chapter_id ][ h5p_id ]['title'] + ': ' + score + "</li>";
			}
			tooltip += '</ol>';

			const finished = count - completed < 1;
			const label    = finished ? '✓' : count - completed;
			const classes  = finished ? 'h5p-results done' : 'h5p-results not-done';
			const badge    = '<button class="' + classes + '" data-tippy-content="' + tooltip + '">' + label + '</button>';
			$( this ).find( '.toc__title' ).append( badge );
		}
	} );

	// Load tooltips.
	tippy('[data-tippy-content]', {
		trigger: 'mouseenter focus',
		allowHTML: true,
		placement: 'right',
		theme: 'light-border',
		interactive: false,
		appendTo: 'parent',
	} );

} )( jQuery );
