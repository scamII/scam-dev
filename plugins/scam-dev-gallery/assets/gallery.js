( function () {
	'use strict';

	function initializeGallery( root ) {
		const items = Array.from(
			root.querySelectorAll( '.scamdev-gallery-item' )
		);
		const dialog = root.querySelector( '.scamdev-lightbox' );
		const content = dialog?.querySelector( '.scamdev-lightbox-content' );
		const image = dialog?.querySelector( '.scamdev-lightbox-image' );
		const previous = dialog?.querySelector( '[data-gallery-prev]' );
		const next = dialog?.querySelector( '[data-gallery-next]' );
		const closeButtons = dialog?.querySelectorAll( '[data-gallery-close]' );

		if (
			items.length === 0 ||
			! dialog ||
			! content ||
			! image ||
			! previous ||
			! next
		) {
			return;
		}

		let currentIndex = 0;
		let returnFocus = null;

		const show = ( index ) => {
			currentIndex = ( index + items.length ) % items.length;
			const item = items[ currentIndex ];
			image.src = item.dataset.full || '';
			image.alt = item.dataset.alt || '';
		};

		const close = () => {
			dialog.hidden = true;
			document.body.classList.remove( 'scamdev-lightbox-open' );
			image.src = '';
			returnFocus?.focus();
		};

		const open = ( index, trigger ) => {
			returnFocus = trigger;
			show( index );
			dialog.hidden = false;
			document.body.classList.add( 'scamdev-lightbox-open' );
			content.focus();
		};

		items.forEach( ( item, index ) => {
			item.addEventListener( 'click', () => open( index, item ) );
		} );

		closeButtons?.forEach( ( button ) =>
			button.addEventListener( 'click', close )
		);
		previous.addEventListener( 'click', () => show( currentIndex - 1 ) );
		next.addEventListener( 'click', () => show( currentIndex + 1 ) );

		dialog.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' ) {
				close();
			} else if ( event.key === 'ArrowLeft' ) {
				show( currentIndex - 1 );
			} else if ( event.key === 'ArrowRight' ) {
				show( currentIndex + 1 );
			} else if ( event.key === 'Tab' ) {
				const focusable = Array.from(
					content.querySelectorAll( 'button:not([disabled])' )
				);
				const first = focusable[ 0 ];
				const last = focusable[ focusable.length - 1 ];

				if ( event.shiftKey && document.activeElement === first ) {
					event.preventDefault();
					last.focus();
				} else if (
					! event.shiftKey &&
					document.activeElement === last
				) {
					event.preventDefault();
					first.focus();
				}
			}
		} );
	}

	function initialize() {
		document
			.querySelectorAll( '.scamdev-gallery-component' )
			.forEach( initializeGallery );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize, {
			once: true,
		} );
	} else {
		initialize();
	}
} )();
