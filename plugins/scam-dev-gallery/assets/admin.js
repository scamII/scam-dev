( function () {
	'use strict';

	const config = window.scamdevGalleryAdmin;
	const addButton = document.getElementById( 'scamdev-add-images' );
	const idsInput = document.getElementById( 'scamdev-gallery-ids' );
	const preview = document.getElementById( 'scamdev-gallery-preview' );

	if ( ! config || ! addButton || ! idsInput || ! preview || ! window.wp?.media ) {
		return;
	}

	let mediaFrame;

	const getIds = () =>
		idsInput.value
			.split( ',' )
			.map( ( value ) => Number.parseInt( value, 10 ) )
			.filter( Number.isInteger );

	const setIds = ( ids ) => {
		idsInput.value = Array.from( new Set( ids ) ).join( ',' );
	};

	const createPreviewItem = ( id, url ) => {
		const wrapper = document.createElement( 'div' );
		wrapper.className = 'scamdev-gallery-admin-item';
		wrapper.dataset.id = String( id );

		const image = document.createElement( 'img' );
		image.src = url;
		image.alt = '';
		image.width = 100;
		image.height = 100;

		const remove = document.createElement( 'button' );
		remove.type = 'button';
		remove.className = 'scamdev-remove-img';
		remove.setAttribute( 'aria-label', config.labels.remove );
		remove.textContent = '×';

		wrapper.append( image, remove );
		return wrapper;
	};

	const fetchPreview = async ( id ) => {
		const body = new URLSearchParams( {
			action: 'scamdev_preview_img',
			id: String( id ),
			nonce: config.nonce,
		} );

		const response = await window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body,
		} );

		if ( ! response.ok ) {
			throw new Error( `Preview request failed: ${ response.status }` );
		}

		const payload = await response.json();
		if ( ! payload.success || ! payload.data?.url ) {
			throw new Error( 'Invalid preview response' );
		}

		return payload.data.url;
	};

	const appendPreview = async ( id ) => {
		if ( preview.querySelector( `[data-id="${ id }"]` ) ) {
			return;
		}

		try {
			const url = await fetchPreview( id );
			preview.appendChild( createPreviewItem( id, url ) );
		} catch {
			// Keep the ID in the field; WordPress will validate it on save.
		}
	};

	addButton.addEventListener( 'click', ( event ) => {
		event.preventDefault();

		if ( mediaFrame ) {
			mediaFrame.open();
			return;
		}

		mediaFrame = window.wp.media( {
			title: config.labels.title,
			button: { text: config.labels.select },
			multiple: true,
			library: { type: 'image' },
		} );

		mediaFrame.on( 'select', () => {
			const ids = getIds();

			mediaFrame
				.state()
				.get( 'selection' )
				.each( ( attachment ) => {
					const id = Number.parseInt( attachment.id, 10 );
					if ( Number.isInteger( id ) && ! ids.includes( id ) ) {
						ids.push( id );
						appendPreview( id );
					}
				} );

			setIds( ids );
		} );

		mediaFrame.open();
	} );

	preview.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '.scamdev-remove-img' );
		if ( ! button ) {
			return;
		}

		const item = button.closest( '.scamdev-gallery-admin-item' );
		const id = Number.parseInt( item?.dataset.id || '', 10 );

		if ( Number.isInteger( id ) ) {
			setIds( getIds().filter( ( currentId ) => currentId !== id ) );
		}

		item?.remove();
	} );
} )();
