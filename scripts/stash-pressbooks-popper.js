/**
 * Pressbooks (v5.19.1) currently uses Popper.js v1 for its Glossary Term
 * tooltips, which is not compatible with Popper v2 used by this plugin. If
 * Popper v1 is detected here, stash it, and later modify the namespacing to run
 * both concurrently (in extend-pressbooks-toc.js).
 */
( function() {
	if ( window.hasOwnProperty( 'Popper' ) && 'function' === typeof window.Popper ) {
		window.pressbooks_Popper = window.Popper;
	}
} )();
