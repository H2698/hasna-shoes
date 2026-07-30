/**
 * Homepage hero — GSAP ScrollTrigger scroll-scrubbed motion.
 *
 * Behavior: a sticky hero card (CSS `position: sticky`, not GSAP pin — avoids a
 * double-pin conflict) sits inside a 300vh track. Scroll progress through the
 * track drives: (1) an entrance reveal, (2) a "focus" scale/tilt on the active
 * product, (3) a crossfade to the next product at each third of the track,
 * (4) a settle/exit as the track ends. Idle float + mouse parallax run only
 * when the hero is on screen and the user isn't actively scrubbing.
 *
 * The choreography follows the project's animation brief (phased scroll
 * motion, idle float, parallax, reduced-motion handling) applied to the
 * *approved* hero design (three real products, sticky card, orange gradient
 * background) — the background/text color-inversion described in an earlier,
 * superseded hero spec was not carried over since it would alter the approved
 * visual design.
 */
(function () {
	'use strict';

	var track = document.querySelector( '[data-hero-track]' );
	var hero = document.querySelector( '[data-hero]' );
	var shoeWrap = document.querySelector( '[data-hero-shoe]' );
	if ( ! track || ! hero || ! shoeWrap || typeof gsap === 'undefined' ) {
		return;
	}

	gsap.registerPlugin( ScrollTrigger );

	var slides = Array.prototype.slice.call( shoeWrap.querySelectorAll( '[data-shoe]' ) );
	var infos = Array.prototype.slice.call( document.querySelectorAll( '[data-info]' ) );
	var thumbs = Array.prototype.slice.call( document.querySelectorAll( '[data-hero-goto]' ) );
	var counter = document.querySelector( '[data-hero-counter]' );
	var stage = document.querySelector( '.hs-hero__stage' );
	var glow = document.querySelector( '.hs-hero__glow' );
	var copy = document.querySelector( '.hs-hero__copy' );
	var info = document.querySelector( '.hs-hero__info' );
	var count = slides.length;

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var isDesktop = window.matchMedia( '(min-width: 1025px)' ).matches;

	var current = 0;
	var isAnimating = false;
	var idleTween = null;
	var scrubbing = false;

	/* ---------------------------------------------------------------------
	 * Entrance reveal (Phase 1) — plays once, independent of scroll position,
	 * so the hero doesn't sit half-hidden if the page loads mid-scroll.
	 * ------------------------------------------------------------------- */
	function playEntrance( onDone ) {
		if ( reduceMotion ) {
			gsap.set( [ hero, copy, info, activeImg(), '.hs-hero__thumb', '.hs-hero__shadow' ], { clearProps: 'all' } );
			if ( onDone ) onDone();
			return;
		}

		var tl = gsap.timeline( { defaults: { ease: 'power3.out' }, onComplete: onDone } );
		tl.from( hero, { scale: 0.96, opacity: 0.9, duration: 0.9 }, 0 )
			.from( activeImg(), { y: 100, scale: 0.82, rotateZ: -4, opacity: 0, duration: 1.1 }, 0.05 )
			.from( copy.children, { x: -70, opacity: 0, duration: 0.8, stagger: 0.08 }, 0.1 )
			.from( info.querySelectorAll( '.hs-hero__info-stage, .hs-hero__controls, .hs-hero__counter' ), { x: 70, opacity: 0, duration: 0.8, stagger: 0.08 }, 0.15 )
			.from( '.hs-hero__thumb', { y: 14, opacity: 0, duration: 0.6, stagger: 0.06 }, 0.35 )
			.from( '.hs-hero__shadow', { opacity: 0, scaleX: 0.6, duration: 0.9 }, 0.1 );
	}

	function activeImg() {
		var slide = slides[ current ];
		return slide ? slide.querySelector( 'img' ) : null;
	}

	/* ---------------------------------------------------------------------
	 * Scroll-scrubbed "focus" motion on the active product (Phases 2-3):
	 * a gentle scale/tilt build across each third of the track, independent
	 * of which product is currently showing.
	 * ------------------------------------------------------------------- */
	function buildFocusTimeline() {
		if ( reduceMotion ) return null;

		ScrollTrigger.create( {
			trigger: track,
			start: 'top top',
			end: 'bottom bottom',
			scrub: 1,
			invalidateOnRefresh: true,
			onUpdate: function ( self ) {
				scrubbing = true;
				handleSlideProgress( self.progress );
				applyFocusMotion( self.progress );
				if ( idleTween ) idleTween.pause();
			},
			onLeave: releaseScrub,
			onLeaveBack: releaseScrub,
		} );
	}

	/**
	 * Continuous scale/tilt "focus" build-and-settle within whichever third of
	 * the track the active product occupies — a bell curve peaking mid-segment,
	 * matching the "product becomes visually dominant, then settles" motion.
	 */
	function applyFocusMotion( progress ) {
		if ( isAnimating ) return; // let the in-flight slide crossfade finish undisturbed
		var img = activeImg();
		if ( ! img ) return;

		var segProgress = ( progress * count ) % 1;
		var bell = Math.sin( segProgress * Math.PI ); // 0 -> 1 -> 0 across the segment

		var scale = 1 + bell * ( isDesktop ? 0.08 : 0.03 );
		var rotateZ = bell * 1.2;
		var rotateY = isDesktop ? bell * 4 : 0;
		var y = -bell * ( isDesktop ? 20 : 8 );

		gsap.set( img, { scale: scale, rotateZ: rotateZ, rotateY: rotateY, y: y, transformPerspective: 900 } );

		if ( glow ) {
			gsap.set( glow, { xPercent: bell * 6, yPercent: -bell * 4 } );
		}

		if ( progress > 0.92 ) {
			var exitP = ( progress - 0.92 ) / 0.08;
			gsap.set( hero, { scale: 1 - exitP * 0.015 } );
			if ( copy ) gsap.set( copy, { opacity: 1 - exitP * 0.15 } );
			if ( info ) gsap.set( info, { opacity: 1 - exitP * 0.15 } );
		} else {
			gsap.set( hero, { scale: 1 } );
			if ( copy ) gsap.set( copy, { opacity: 1 } );
			if ( info ) gsap.set( info, { opacity: 1 } );
		}
	}

	var releaseTimer = null;
	function releaseScrub() {
		scrubbing = false;
		clearTimeout( releaseTimer );
		releaseTimer = setTimeout( function () {
			if ( ! scrubbing && idleTween ) idleTween.play();
		}, 150 );
	}

	/* ---------------------------------------------------------------------
	 * Slide switching at each third of the track, mirroring the approved
	 * design's scroll-paged product showcase.
	 * ------------------------------------------------------------------- */
	function handleSlideProgress( progress ) {
		var seg = Math.min( count - 1, Math.floor( progress * count ) );
		if ( seg !== current ) {
			goToSlide( seg, seg < current );
		}
	}

	function goToSlide( index, isBack ) {
		if ( index === current || isAnimating ) return;
		isAnimating = true;
		var prevImg = activeImg();
		current = index;
		var nextImg = activeImg();

		slides.forEach( function ( s, n ) { s.classList.toggle( 'is-active', n === index ); } );
		infos.forEach( function ( s, n ) { s.classList.toggle( 'is-active', n === index ); } );
		thumbs.forEach( function ( t, n ) { t.classList.toggle( 'is-active', n === index ); } );
		if ( counter ) counter.textContent = ( '0' + ( index + 1 ) ) + ' / 0' + count;

		if ( reduceMotion ) {
			isAnimating = false;
			return;
		}

		var dir = isBack ? -1 : 1;
		if ( nextImg ) {
			gsap.fromTo( nextImg, { opacity: 0, scale: 0.9, y: 50 * dir, rotateZ: 3 * dir }, { opacity: 1, scale: 1, y: 0, rotateZ: 0, duration: 0.7, ease: 'power3.out', onComplete: function () { isAnimating = false; } } );
		} else {
			isAnimating = false;
		}
	}

	/* ---------------------------------------------------------------------
	 * Manual prev/next + thumbnail navigation.
	 * ------------------------------------------------------------------- */
	document.querySelectorAll( '[data-hero-nav]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			if ( isAnimating ) return;
			var dir = parseInt( btn.getAttribute( 'data-hero-nav' ), 10 );
			goToSlide( ( current + dir + count ) % count, dir < 0 );
		} );
	} );
	thumbs.forEach( function ( thumb ) {
		thumb.addEventListener( 'click', function () {
			if ( isAnimating ) return;
			var i = parseInt( thumb.getAttribute( 'data-hero-goto' ), 10 );
			goToSlide( i, i < current );
		} );
	} );

	/* ---------------------------------------------------------------------
	 * Size selectors.
	 * ------------------------------------------------------------------- */
	document.querySelectorAll( '.hs-hero__sizes' ).forEach( function ( group ) {
		group.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '.hs-hero__size' );
			if ( ! btn ) return;
			group.querySelectorAll( '.hs-hero__size' ).forEach( function ( b ) {
				b.classList.remove( 'is-active' );
				b.setAttribute( 'aria-pressed', 'false' );
			} );
			btn.classList.add( 'is-active' );
			btn.setAttribute( 'aria-pressed', 'true' );
			if ( ! reduceMotion ) {
				gsap.fromTo( btn, { scale: 0.85 }, { scale: 1, duration: 0.35, ease: 'back.out(3)' } );
			}
		} );
	} );

	/* ---------------------------------------------------------------------
	 * Idle float on the active product when the hero is idle (not mid-scrub).
	 * ------------------------------------------------------------------- */
	function startIdleFloat() {
		if ( reduceMotion ) return;
		idleTween = gsap.to( shoeWrap, {
			y: -6,
			duration: gsap.utils.random( 2.8, 3.5 ),
			yoyo: true,
			repeat: -1,
			ease: 'sine.inOut',
		} );
	}

	/* ---------------------------------------------------------------------
	 * Mouse parallax (desktop only), applied to the stage wrapper + glow so
	 * it never fights the scrub timeline's transforms on the image itself.
	 * ------------------------------------------------------------------- */
	function initParallax() {
		if ( ! isDesktop || reduceMotion || ! stage ) return;

		var xTo = gsap.quickTo( stage, 'x', { duration: 0.5, ease: 'power3.out' } );
		var yTo = gsap.quickTo( stage, 'y', { duration: 0.5, ease: 'power3.out' } );
		var glowXTo = glow ? gsap.quickTo( glow, 'x', { duration: 0.6, ease: 'power3.out' } ) : null;
		var glowYTo = glow ? gsap.quickTo( glow, 'y', { duration: 0.6, ease: 'power3.out' } ) : null;

		hero.addEventListener( 'mousemove', function ( e ) {
			if ( scrubbing ) return;
			var r = hero.getBoundingClientRect();
			var cx = ( ( e.clientX - r.left ) / ( r.width || 1 ) - 0.5 ) * 2;
			var cy = ( ( e.clientY - r.top ) / ( r.height || 1 ) - 0.5 ) * 2;
			xTo( cx * 8 );
			yTo( cy * 6 );
			if ( glowXTo ) glowXTo( cx * 12 );
			if ( glowYTo ) glowYTo( cy * 10 );
		} );
		hero.addEventListener( 'mouseleave', function () {
			xTo( 0 ); yTo( 0 );
			if ( glowXTo ) glowXTo( 0 );
			if ( glowYTo ) glowYTo( 0 );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Boot.
	 * ------------------------------------------------------------------- */
	playEntrance( buildFocusTimeline );
	startIdleFloat();
	initParallax();

	window.addEventListener( 'load', function () {
		ScrollTrigger.refresh();
	} );
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( function () { ScrollTrigger.refresh(); } );
	}
})();
