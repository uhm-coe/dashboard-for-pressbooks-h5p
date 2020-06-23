/**
 * UI wiring for Pressbooks Organize page.
 */

/* global Data, tippy, jQuery */
( function( $ ) {

	const $tables = $( 'table.front-matter, table.chapters, table.back-matter');
	const heading = '<th role="columnheader">' + Data.column_heading_h5p + '</th>';

	// Add H5P column headings.
	$tables.find( 'thead tr th:nth-child(1)' ).after( heading );

	// Add H5P badge to each chapter's column.
	$tables.find( 'tbody tr td:nth-child(1)' ).each( function () {
		let badge = '—';

		const chapter_id = $( this ).parent( 'tr' ).attr( 'id' ).replace( 'chapter_', '' );
		const count  = Data.h5p_ids.hasOwnProperty( chapter_id ) ? Object.keys( Data.h5p_ids[ chapter_id ] ).length : 0;
		if ( count > 0 ) {
			let tooltip = '<ol>';
			for ( const h5p_id_key in Data.h5p_ids[ chapter_id ] ) {
				const h5p_id = parseInt( h5p_id_key.replace( 'h5p-id-', '' ) );
				const h5p    = Data.h5p_ids[ chapter_id ][ h5p_id_key ];
				tooltip  += "<li><a target='_blank' href='" + Data.h5p_url + "&id=" + h5p_id + "'>" + h5p.title + ' (' + h5p.library + ")</a></li>";
			}
			tooltip += '</ol>';
			badge = '<button class="button-primary" data-tippy-content="' + tooltip + '">' + count + '</button>';
		}

		const cell = '<td class="column-h5p">' + badge + '</td>';
		$( this ).after( cell );
	} );

	// Add H5P footer cell.
	const footer_heading_h5p = '<th role="columnheader">&nbsp;</th>';
	$tables.find( 'tfoot tr th:nth-child(1)' ).after( footer_heading_h5p );

	// Load tooltips.
	tippy('[data-tippy-content]', {
		trigger: 'click',
		allowHTML: true,
		placement: 'right',
		theme: 'light-border',
		interactive: true,
	} );

} )( jQuery );
