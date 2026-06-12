import { useState, useEffect, useCallback } from 'react';

export default function TableOfContents() {
	const [ headings, setHeadings ] = useState( [] );
	const [ open, setOpen ] = useState( false );
	const [ activeId, setActiveId ] = useState( '' );

	useEffect( () => {
		const hs = Array.from(
			document.querySelectorAll( '.prose h2, .prose h3' )
		).map( ( h, i ) => {
			const id = h.id || `toc-${ i }`;
			if ( ! h.id ) {
				h.id = id;
			}
			return { id, text: h.textContent, level: h.tagName.toLowerCase() };
		} );
		setHeadings( hs );
	}, [] );

	useEffect( () => {
		const observer = new IntersectionObserver(
			( entries ) => {
				for ( const entry of entries ) {
					if ( entry.isIntersecting ) {
						setActiveId( entry.target.id );
					}
				}
			},
			{ rootMargin: '-80px 0px -80% 0px' }
		);
		headings.forEach( ( h ) => {
			const el = document.getElementById( h.id );
			if ( el ) {
				observer.observe( el );
			}
		} );
		return () => observer.disconnect();
	}, [ headings ] );

	const scrollTo = useCallback( ( id ) => {
		const el = document.getElementById( id );
		if ( el ) {
			el.scrollIntoView( { behavior: 'smooth' } );
		}
	}, [] );

	if ( headings.length < 2 ) {
		return null;
	}

	return (
		<div
			className="mb-8 rounded-xl border overflow-hidden"
			style={ {
				background: 'var(--color-surface)',
				borderColor: 'var(--color-border)',
			} }
		>
			<button
				onClick={ () => setOpen( ! open ) }
				className="w-full flex items-center justify-between px-5 py-3 text-sm font-semibold hover:opacity-80 transition-opacity focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--color-accent)]/40"
				style={ { color: 'var(--color-text)' } }
				aria-expanded={ open }
			>
				Содержание
				<svg
					className={ `w-4 h-4 transition-transform ${
						open ? 'rotate-180' : ''
					}` }
					fill="none"
					stroke="currentColor"
					viewBox="0 0 24 24"
				>
					<path
						strokeLinecap="round"
						strokeLinejoin="round"
						strokeWidth="2"
						d="M19 9l-7 7-7-7"
					/>
				</svg>
			</button>
			{ open && (
				<nav className="px-5 pb-4 pt-1" aria-label="Оглавление">
					<ul className="space-y-1 text-sm">
						{ headings.map( ( h ) => (
							<li
								key={ h.id }
								className={ h.level === 'h3' ? 'ml-3' : '' }
							>
								<button
									onClick={ () => scrollTo( h.id ) }
									className={ `block w-full text-left py-1 transition-colors hover:opacity-80
                                        ${
											activeId === h.id
												? 'font-medium'
												: 'opacity-70'
										}` }
									style={ {
										color:
											activeId === h.id
												? 'var(--color-accent)'
												: 'var(--color-text-secondary)',
									} }
								>
									{ h.text }
								</button>
							</li>
						) ) }
					</ul>
				</nav>
			) }
		</div>
	);
}
