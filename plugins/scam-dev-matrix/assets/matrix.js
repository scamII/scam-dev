( function () {
	'use strict';

	function initialize() {
		document.querySelectorAll( '[data-password-toggle]' ).forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const targetId = button.getAttribute( 'aria-controls' );
				const input = targetId
					? document.getElementById( targetId )
					: null;

				if ( ! input ) {
					return;
				}

				const reveal = input.type === 'password';
				input.type = reveal ? 'text' : 'password';
				button.setAttribute( 'aria-pressed', String( reveal ) );
				button.setAttribute(
					'aria-label',
					reveal ? 'Скрыть пароль' : 'Показать пароль'
				);
				button.textContent = reveal ? 'Скрыть' : 'Показать';
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize, {
			once: true,
		} );
	} else {
		initialize();
	}
} )();
