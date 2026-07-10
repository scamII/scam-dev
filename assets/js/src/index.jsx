import { createRoot } from 'react-dom/client';
import hljs from 'highlight.js/lib/common';

import ThemeToggle from '../components/ThemeToggle';
import ProgressBar from '../components/ProgressBar';
import BackToTop from '../components/BackToTop';
import TableOfContents from '../components/TableOfContents';
import CopyCode from '../components/CopyCode';

function mount( Component, selector, props = {} ) {
	const root = document.querySelector( selector );
	if ( root ) {
		createRoot( root ).render( <Component { ...props } /> );
	}
}

function initHeaderSearch() {
	const button = document.getElementById( 'header-search-toggle' );
	const panel = document.getElementById( 'header-search-form' );

	if ( ! button || ! panel ) {
		return;
	}

	const close = () => {
		panel.hidden = true;
		button.setAttribute( 'aria-expanded', 'false' );
		button.setAttribute( 'aria-label', 'Открыть поиск' );
	};

	button.addEventListener( 'click', () => {
		const opening = panel.hidden;
		panel.hidden = ! opening;
		button.setAttribute( 'aria-expanded', String( opening ) );
		button.setAttribute(
			'aria-label',
			opening ? 'Закрыть поиск' : 'Открыть поиск'
		);

		if ( opening ) {
			window.setTimeout( () => {
				panel.querySelector( 'input[type="search"]' )?.focus();
			}, 0 );
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && ! panel.hidden ) {
			close();
			button.focus();
		}
	} );

	document.addEventListener( 'click', ( event ) => {
		if (
			! panel.hidden &&
			! panel.contains( event.target ) &&
			! button.contains( event.target )
		) {
			close();
		}
	} );
}

function initMobileMenu() {
	const trigger = document.getElementById( 'mobile-menu-toggle' );
	const panel = document.getElementById( 'mobile-menu-panel' );
	const content = panel?.querySelector( '.mobile-menu-content' );
	const openIcon = trigger?.querySelector( '.mobile-menu-open-icon' );
	const closeIcon = trigger?.querySelector( '.mobile-menu-close-icon' );

	if ( ! trigger || ! panel || ! content ) {
		return;
	}

	const focusableSelector = [
		'a[href]',
		'button:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'textarea:not([disabled])',
		'[tabindex]:not([tabindex="-1"])',
	].join( ',' );

	const setOpen = ( open ) => {
		panel.hidden = ! open;
		trigger.setAttribute( 'aria-expanded', String( open ) );
		trigger.setAttribute(
			'aria-label',
			open ? 'Закрыть меню' : 'Открыть меню'
		);
		document.body.classList.toggle( 'mobile-menu-open', open );

		if ( openIcon ) {
			openIcon.hidden = open;
		}
		if ( closeIcon ) {
			closeIcon.hidden = ! open;
		}

		if ( open ) {
			window.setTimeout( () => {
				const first = content.querySelector( focusableSelector );
				( first || content ).focus();
			}, 0 );
		}
	};

	const close = ( restoreFocus = true ) => {
		if ( panel.hidden ) {
			return;
		}
		setOpen( false );
		if ( restoreFocus ) {
			trigger.focus();
		}
	};

	trigger.addEventListener( 'click', () => setOpen( panel.hidden ) );
	panel
		.querySelectorAll( '[data-mobile-menu-close]' )
		.forEach( ( element ) =>
			element.addEventListener( 'click', () => close() )
		);

	panel.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			close();
			return;
		}

		if ( event.key !== 'Tab' ) {
			return;
		}

		const focusable = Array.from(
			content.querySelectorAll( focusableSelector )
		).filter( ( element ) => ! element.hidden );

		if ( focusable.length === 0 ) {
			event.preventDefault();
			content.focus();
			return;
		}

		const first = focusable[ 0 ];
		const last = focusable[ focusable.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	panel.querySelectorAll( 'a' ).forEach( ( link ) => {
		link.addEventListener( 'click', () => close( false ) );
	} );

	window
		.matchMedia( '(min-width: 1024px)' )
		.addEventListener( 'change', ( event ) => {
			if ( event.matches ) {
				close( false );
			}
		} );
}

function initParticles() {
	const container = document.getElementById( 'particles' );
	const reduceMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches;

	if ( ! container || reduceMotion ) {
		return;
	}

	const fragment = document.createDocumentFragment();

	for ( let index = 0; index < 24; index += 1 ) {
		const particle = document.createElement( 'span' );
		particle.className = 'particle';
		particle.style.left = `${ Math.random() * 100 }%`;
		particle.style.animationDelay = `${ Math.random() * 6 }s`;
		particle.style.animationDuration = `${ 4 + Math.random() * 4 }s`;
		fragment.appendChild( particle );
	}

	container.appendChild( fragment );
}

function syncHighlightStyles() {
	const isLight = document.documentElement.dataset.theme === 'light';
	const lightStyle = document.getElementById( 'highlightjs-light-css' );
	const darkStyle = document.getElementById( 'highlightjs-dark-css' );

	if ( lightStyle ) {
		lightStyle.media = isLight ? 'all' : 'not all';
	}
	if ( darkStyle ) {
		darkStyle.media = isLight ? 'not all' : 'all';
	}
}

function initSyntaxHighlighting() {
	if ( ! document.querySelector( '.prose pre code' ) ) {
		return;
	}

	syncHighlightStyles();
	hljs.configure( { ignoreUnescapedHTML: true } );
	hljs.highlightAll();

	const observer = new MutationObserver( syncHighlightStyles );
	observer.observe( document.documentElement, {
		attributes: true,
		attributeFilter: [ 'data-theme' ],
	} );
}

function initialize() {
	mount( ThemeToggle, '#theme-toggle-root' );
	mount( ThemeToggle, '#theme-toggle-root-mobile' );
	mount( ProgressBar, '#progress-bar-root' );
	mount( BackToTop, '#back-to-top-root' );
	mount( TableOfContents, '#toc-root' );

	if ( document.querySelector( '.prose pre code' ) ) {
		const copyRoot = document.createElement( 'div' );
		copyRoot.hidden = true;
		document.body.appendChild( copyRoot );
		createRoot( copyRoot ).render( <CopyCode /> );
	}

	initHeaderSearch();
	initMobileMenu();
	initParticles();
	initSyntaxHighlighting();
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', initialize, { once: true } );
} else {
	initialize();
}
