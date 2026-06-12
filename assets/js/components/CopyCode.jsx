import { useState, useEffect } from 'react';

export default function CopyCode() {
	const [ copied, setCopied ] = useState( null );

	useEffect( () => {
		document.querySelectorAll( 'pre code' ).forEach( ( block, i ) => {
			const pre = block.parentElement;
			if ( ! pre || pre.querySelector( '.copy-btn' ) ) {
				return;
			}

			pre.style.position = 'relative';

			const btn = document.createElement( 'button' );
			btn.className = 'copy-btn';
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('class', 'w-4 h-4');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('viewBox', '0 0 24 24');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('stroke-width', '2');
            path.setAttribute('d', 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z');
            svg.appendChild(path);
            btn.appendChild(svg);
			btn.setAttribute( 'aria-label', 'Копировать код' );
			btn.onclick = () => {
				navigator.clipboard.writeText( block.textContent ).then( () => {
					setCopied( i );
					setTimeout( () => setCopied( null ), 2000 );
				} );
			};

			Object.assign( btn.style, {
				position: 'absolute',
				top: '8px',
				right: '8px',
				padding: '6px 8px',
				background: 'var(--color-surface)',
				border: '1px solid var(--color-border)',
				borderRadius: '6px',
				cursor: 'pointer',
				color: 'var(--color-text-secondary)',
				fontSize: '12px',
				display: 'flex',
				alignItems: 'center',
				gap: '4px',
				transition: 'all 0.2s',
			} );

			pre.appendChild( btn );
		} );
	}, [] );

	return null;
}
