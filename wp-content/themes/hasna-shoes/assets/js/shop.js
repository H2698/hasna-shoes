/**
 * Single product page: replaces the default WooCommerce variation <select>
 * with circular size buttons (matching the hero's size selector), while
 * keeping the native select as the source of truth so WooCommerce's own
 * variation-form logic (price/stock/gallery updates, add-to-cart gating)
 * keeps working unmodified.
 */
(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var select = document.querySelector( '.variations select[name="attribute_pa_taille"]' );
		if ( ! select || typeof jQuery === 'undefined' ) return;

		var options = Array.prototype.slice.call( select.options ).filter( function ( o ) { return o.value; } );
		if ( ! options.length ) return;

		var wrap = document.createElement( 'div' );
		wrap.className = 'hs-size-buttons';

		var label = document.createElement( 'span' );
		label.className = 'hs-size-buttons__label';
		label.textContent = select.closest( 'tr' ) && select.closest( 'tr' ).querySelector( 'label' )
			? select.closest( 'tr' ).querySelector( 'label' ).textContent
			: 'Taille';

		var group = document.createElement( 'div' );
		group.setAttribute( 'role', 'group' );
		group.setAttribute( 'aria-label', label.textContent );

		options.forEach( function ( opt ) {
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'hs-size-btn';
			btn.textContent = opt.textContent;
			btn.setAttribute( 'aria-pressed', 'false' );
			if ( opt.disabled ) btn.disabled = true;

			btn.addEventListener( 'click', function () {
				group.querySelectorAll( '.hs-size-btn' ).forEach( function ( b ) {
					b.classList.remove( 'is-active' );
					b.setAttribute( 'aria-pressed', 'false' );
				} );
				btn.classList.add( 'is-active' );
				btn.setAttribute( 'aria-pressed', 'true' );
				jQuery( select ).val( opt.value ).trigger( 'change' );
			} );

			group.appendChild( btn );
		} );

		wrap.appendChild( label );
		wrap.appendChild( group );
		select.closest( '.variations' ).appendChild( wrap );

		// Keep buttons in sync if WooCommerce resets the select (e.g. "Clear").
		jQuery( select ).on( 'change', function () {
			var current = select.value;
			group.querySelectorAll( '.hs-size-btn' ).forEach( function ( b, i ) {
				var match = options[ i ] && options[ i ].value === current;
				b.classList.toggle( 'is-active', !! match );
				b.setAttribute( 'aria-pressed', match ? 'true' : 'false' );
			} );
		} );

		// Reflect stock-based option disabling that WooCommerce toggles at runtime.
		var observer = new MutationObserver( function () {
			options.forEach( function ( opt, i ) {
				group.querySelectorAll( '.hs-size-btn' )[ i ].disabled = opt.disabled;
			} );
		} );
		observer.observe( select, { attributes: true, childList: true, subtree: true } );
	} );
})();
