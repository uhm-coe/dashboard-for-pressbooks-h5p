/**
 * UI wiring for Pressbooks table of contents.
 */

/* global Data, tippy, jQuery, H5P */
( function( $ ) {
	// Render badges on page load.
	render_badges();

	// Refresh badges whenever some H5P content is completed.
	$( document ).ready( function () {
		if ( Data.isLoggedIn && 'object' === typeof H5P && H5P.hasOwnProperty( 'externalDispatcher' ) ) {
			H5P.externalDispatcher.on( 'xAPI', function ( event ) {
				/**
				 * Here we filter to H5P content that has been completed, while ignoring
				 * subcontent completion events and other irrelevant events. A summary
				 * of the H5P content types that are considered:
				 *
				 * Questions (these fire "answered" events):
				 *   Drag and Drop
				 *   Drag The Words
				 *   Fill in the blanks
				 *   Find the hotspot
				 *   Mark the words
				 *   Multiple Choice
				 *   Summary
				 *
				 * Containers (these fire "completed" events, and can have children
				 * that fire either "completed" or "answered" events):
				 *   Quiz (Question Set)
				 *   Single Choice Set
				 *   Interactive Video (note: can also contain a Single Choice Set)
				 *   Course Presentation (note: can also contain Interactive Video and Single Choice Set)
				 *
				 * See: https://h5p.org/documentation/x-api
				 */
				let verb, contextActivities, h5p_id, title, score, max_score, finished, duration;

				// Ignore any statements not matching requirements.
				try {
					const statement = event.data.statement;

					// Ignore any statements other than "answered" and "completed."
					verb = statement.verb.display['en-US'];
					if ( ! ['answered', 'completed'].includes( verb ) ) {
						throw 'Not a completion verb.';
					}

					// Ignore any activity that has a parent, since we only want to log
					// one result per H5P ID.
					contextActivities = statement.context.contextActivities;
					if ( contextActivities.hasOwnProperty( 'parent' ) ) {
						throw 'Ignoring activity with a parent.';
					}

					// Get required properties from the xAPI statement (fail gracefully if
					// any are missing).
					h5p_id    = statement.object.definition.extensions['http://h5p.org/x-api/h5p-local-content-id'];
					title     = statement.object.definition.name['en-US'];
					score     = statement.result.score.raw;
					max_score = statement.result.score.max;
					finished  = Math.floor(Date.now() / 1000);
					duration  = parseDurationString( statement.result.duration );
				} catch ( error ) {
					return;
				}

				// Update the result cache with the new result.
				if ( Data.h5p_results.hasOwnProperty( h5p_id ) ) {
					Data.h5p_results[ h5p_id ].score = score;
				} else {
					Data.h5p_results[ h5p_id ] = {
						title,
						score,
						max_score,
						finished,
						duration,
					};
				}

				// Re-render the badges.
				render_badges();
			} );
		}
	} );

	/**
	 * Render badges with tooltips summarizing H5P content in individual chapters
	 * in the Pressbooks Table of Contents.
	 */
	function render_badges() {
		// Add H5P badge to each chapter with H5P content.
		const $lis = $( 'li.toc__front-matter, li.toc__chapter, li.toc__back-matter' );
		$lis.each( function () {
			const chapter_id = $( this ).attr( 'id' ).replace( 'toc-front-matter-', '' ).replace( 'toc-chapter-', '' );
			const count = Data.h5p_ids.hasOwnProperty( chapter_id ) ? Object.keys( Data.h5p_ids[ chapter_id ] ).length : 0;
			if ( count > 0 ) {
				let completed = 0;

				// Build tooltip showing H5P results.
				let tooltip = '';
				if ( ! Data.isLoggedIn ) {
					tooltip += "<p><a class='button' href='" + Data.loginUrl + "'>" + Data.msgLogInToSee + "</a></p>";
				}
				tooltip += '<p>' + Data.msgYourH5PResults + ':</p>';
				tooltip += '<ol>';
				for ( const h5p_id_key in Data.h5p_ids[ chapter_id ] ) {
					const h5p_id = parseInt( h5p_id_key.replace( 'h5p-id-', '' ) );
					const h5p    = Data.h5p_ids[ chapter_id ][ h5p_id_key ];
					// Calculate score; mark completed if result exists and
					// score > max_score * passing_percentage.
					let score          = '—';
					let checkmarkClass = 'checkmark hidden';
					if ( Data.h5p_results.hasOwnProperty( h5p_id ) ) {
						const result = Data.h5p_results[ h5p_id ];
						score = Math.round( result.score / result.max_score * 100 ) + '%';
						if ( result.score >= result.max_score * h5p.passing / 100 ) {
							checkmarkClass = 'checkmark';
							completed++;
						}
					}
					tooltip += "<li data-h5p-id='" + h5p_id + "'><span class='" + checkmarkClass + "'>&#10004;</span>&nbsp; " + h5p.title + ': ' + score + "</li>";
				}
				tooltip += '</ol>';

				// Build the badge: green checkmark if finished; red with remaining count
				// if not; blue with total count if user is anonymous (results unable to
				// be collected).
				const finished     = count - completed < 1;
				const label        = finished ? '✓' : count - completed;
				const badgeClasses = [ 'h5p-results' ];
				if ( ! Data.isLoggedIn ) {
					badgeClasses.push( 'anonymous' );
				} else if ( finished ) {
					badgeClasses.push( 'done' );
				} else {
					badgeClasses.push( 'not-done' );
				}
				const badge = '<button class="' + badgeClasses.join( ' ' ) + '" data-tippy-content="' + tooltip + '">' + label + '</button>';

				// Remove any existing badge before appending new badge.
				$( this ).find( 'button.h5p-results' ).remove();

				// Append badge.
				$( this ).find( '.toc__title' ).append( badge );
			}
		} );

		// Load tooltips.
		tippy('[data-tippy-content]', {
			trigger: 'mouseenter focus',
			allowHTML: true,
			placement: 'right',
			theme: 'light-border',
			interactive: true,
		} );
	}

	/**
	 * Parse ISO8601 duration string (e.g., PT12.33S) into seconds.
	 *
	 * @param  {string} durationString Duration as an ISO8601 string.
	 * @return {number}                Duration in seconds.
	 */
	function parseDurationString( durationString ){
		const stringPattern = /^PT(?:(\d+)D)?(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d{1,3})?)S)?$/;
		const stringParts   = stringPattern.exec( durationString );

		return (
			(
				(
					( stringParts[ 1 ] === undefined ? 0 : stringParts[ 1 ] * 1 )        // Days.
					* 24 + ( stringParts[ 2 ] === undefined ? 0 : stringParts[ 2 ] * 1 ) // Hours.
				)
				* 60 + ( stringParts[ 3 ] === undefined ? 0 : stringParts[ 3 ] * 1 )   // Minutes.
			)
			* 60 + ( stringParts[ 4 ] === undefined ? 0 : stringParts[ 4 ] * 1 )     // Seconds.
		);
	}

} )( jQuery );
