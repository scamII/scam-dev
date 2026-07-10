import { useEffect } from 'react';

export default function CopyCode() {
	useEffect( () => {
		const cleanups = [];

		document.querySelectorAll( '.prose pre code' ).forEach( ( block ) => {
			const pre = block.parentElement;
			if ( ! pre || pre.querySelector( '.copy-btn' ) ) {
				return;
			}

			pre.classList.add( 'has-copy-button' );

			const button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'copy-btn';
			button.textContent = 'Копировать';
			button.setAttribute( 'aria-label', 'Копировать код' );

			const handler = async () => {
				try {
					await navigator.clipboard.writeText( block.textContent || '' );
					button.textContent = 'Скопировано';
					window.setTimeout( () => {
						button.textContent = 'Копировать';
					}, 2000 );
				} catch {
					button.textContent = 'Ошибка';
					window.setTimeout( () => {
						button.textContent = 'Копировать';
					}, 2000 );
				}
			};

			button.addEventListener( 'click', handler );
			pre.appendChild( button );

			cleanups.push( () => {
				button.removeEventListener( 'click', handler );
				button.remove();
			} );
		} );

		return () => cleanups.forEach( ( cleanup ) => cleanup() );
	}, [] );

	return null;
}
