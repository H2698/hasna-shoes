/**
 * Site-wide interactions: header scroll shadow, scroll reveals, testimonial
 * rotation, product-rail navigation, wishlist toggle, newsletter submit.
 * Hero-specific GSAP behavior lives in hero.js (front page only).
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.addEventListener( 'DOMContentLoaded', function () {
		initHeaderShadow();
		initReveal();
		initRailNav();
		initWishlist();
		initTestimonials();
		initNewsletter();
		initMobileNav();
		initContactForm();
		initSearchPanel();
	} );

	function initSearchPanel() {
		var toggle = document.querySelector( '[data-search-toggle]' );
		var panel = document.querySelector( '[data-search-panel]' );
		var closeBtn = document.querySelector( '[data-search-close]' );
		var input = document.querySelector( '[data-search-input]' );
		if ( ! toggle || ! panel ) return;

		function close() {
			toggle.setAttribute( 'aria-expanded', 'false' );
			panel.hidden = true;
		}
		function open() {
			toggle.setAttribute( 'aria-expanded', 'true' );
			panel.hidden = false;
			if ( input ) setTimeout( function () { input.focus(); }, 50 );
		}

		toggle.addEventListener( 'click', function () {
			var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
			expanded ? close() : open();
		} );
		if ( closeBtn ) closeBtn.addEventListener( 'click', close );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) close();
		} );
	}

	function initContactForm() {
		var form = document.querySelector( '[data-contact-form]' );
		var msg = document.querySelector( '[data-contact-msg]' );
		if ( ! form ) return;

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var nonceField = form.querySelector( '#hasna_contact_nonce' );
			var submitBtn = form.querySelector( 'button[type="submit"]' );

			var body = new URLSearchParams();
			body.set( 'action', 'hasna_contact_submit' );
			body.set( 'name', form.querySelector( '#hs-c-name' ).value );
			body.set( 'email', form.querySelector( '#hs-c-email' ).value );
			body.set( 'phone', form.querySelector( '#hs-c-phone' ).value );
			body.set( 'message', form.querySelector( '#hs-c-message' ).value );
			body.set( 'nonce', nonceField ? nonceField.value : '' );

			submitBtn.disabled = true;

			fetch( hasnaSettings.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString(),
			} )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					submitBtn.disabled = false;
					if ( ! msg ) return;
					msg.textContent = data && data.data ? data.data.message : '';
					msg.className = 'hs-contact__msg ' + ( data && data.success ? 'is-success' : 'is-error' );
					if ( data && data.success ) form.reset();
				} )
				.catch( function () {
					submitBtn.disabled = false;
					if ( msg ) {
						msg.textContent = 'Une erreur est survenue, veuillez réessayer.';
						msg.className = 'hs-contact__msg is-error';
					}
				} );
		} );
	}

	function initMobileNav() {
		var toggle = document.querySelector( '[data-mobile-nav-toggle]' );
		var panel = document.querySelector( '[data-mobile-nav]' );
		if ( ! toggle || ! panel ) return;

		function close() {
			toggle.setAttribute( 'aria-expanded', 'false' );
			panel.hidden = true;
		}
		function open() {
			toggle.setAttribute( 'aria-expanded', 'true' );
			panel.hidden = false;
		}

		toggle.addEventListener( 'click', function () {
			var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
			expanded ? close() : open();
		} );
		panel.addEventListener( 'click', function ( e ) {
			if ( e.target.tagName === 'A' ) close();
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) close();
		} );
		window.addEventListener( 'resize', function () {
			if ( window.innerWidth > 768 ) close();
		} );
	}

	function initHeaderShadow() {
		var header = document.getElementById( 'hs-header' );
		if ( ! header ) return;
		var raf = null;
		window.addEventListener( 'scroll', function () {
			if ( raf ) return;
			raf = requestAnimationFrame( function () {
				raf = null;
				header.classList.toggle( 'is-scrolled', window.scrollY > 30 );
			} );
		}, { passive: true } );
	}

	function initReveal() {
		var nodes = document.querySelectorAll( '[data-reveal]' );
		if ( ! nodes.length ) return;

		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			nodes.forEach( function ( n ) { n.classList.add( 'is-visible' ); } );
			return;
		}

		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) return;
				entry.target.classList.add( 'is-visible' );
				io.unobserve( entry.target );
			} );
		}, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' } );

		nodes.forEach( function ( n ) { io.observe( n ); } );
	}

	function initRailNav() {
		var rail = document.querySelector( '[data-rail]' );
		if ( ! rail ) return;

		document.querySelectorAll( '[data-rail-nav]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var dir = parseInt( btn.getAttribute( 'data-rail-nav' ), 10 );
				rail.scrollBy( { left: dir * rail.clientWidth * 0.5, behavior: reduceMotion ? 'auto' : 'smooth' } );
			} );
		} );

		rail.addEventListener( 'wheel', function ( e ) {
			if ( Math.abs( e.deltaX ) >= Math.abs( e.deltaY ) ) return;
			e.preventDefault();
			rail.scrollLeft += ( e.deltaY + e.deltaX ) * 1.15;
		}, { passive: false } );
	}

	function initWishlist() {
		document.querySelectorAll( '[data-wish]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var on = btn.getAttribute( 'aria-pressed' ) === 'true';
				btn.setAttribute( 'aria-pressed', on ? 'false' : 'true' );
			} );
		} );
	}

	function initTestimonials() {
		var quotes = document.querySelectorAll( '[data-quote]' );
		var dots = document.querySelectorAll( '[data-dot]' );
		if ( ! quotes.length ) return;

		var current = 0;
		var timer = null;

		function show( i ) {
			current = i;
			quotes.forEach( function ( q, n ) { q.classList.toggle( 'is-active', n === i ); } );
			dots.forEach( function ( d, n ) { d.classList.toggle( 'is-active', n === i ); } );
		}

		dots.forEach( function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				show( i );
				restart();
			} );
		} );

		function restart() {
			if ( timer ) clearInterval( timer );
			if ( reduceMotion ) return;
			timer = setInterval( function () { show( ( current + 1 ) % quotes.length ); }, 6000 );
		}
		restart();
	}

	function initNewsletter() {
		var form = document.querySelector( '[data-newsletter-form]' );
		var msg = document.querySelector( '[data-newsletter-msg]' );
		if ( ! form ) return;

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var input = form.querySelector( 'input[type="email"]' );
			var email = input ? input.value : '';
			var nonceField = form.querySelector( '#hasna_newsletter_nonce' );

			var body = new URLSearchParams();
			body.set( 'action', 'hasna_newsletter_signup' );
			body.set( 'email', email );
			body.set( 'nonce', nonceField ? nonceField.value : '' );

			fetch( hasnaSettings.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString(),
			} )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					if ( data && data.success && msg ) {
						msg.classList.add( 'is-visible' );
						if ( input ) input.value = '';
					}
				} )
				.catch( function () { /* fails silently — form stays as-is for retry */ } );
		} );
	}
})();
