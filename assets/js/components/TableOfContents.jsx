import { useState, useEffect, useCallback } from 'react';

const TOGGLE_CLASS_NAME = [
	'w-full flex items-center justify-between px-5 py-3',
	'text-sm font-semibold hover:opacity-80 transition-opacity',
	'focus:outline-none focus:ring-2 focus:ring-inset',
	'focus:ring-[var(--color-accent)]/40',
].join( ' ' );

function uniqueHeadingId( heading, index, usedIds ) {
	const existing = heading.id?.trim();
	const textSlug = heading.textContent
		?.toLowerCase()
		.trim()
		.replace( /[^\p{L}\p{N}\s-]/gu, '' )
		.replace( /\s+/g, '-' );
	const base = existing || textSlug || `section-${ index + 1 }`;
	let candidate = base;
	let suffix = 2;

	while ( usedIds.has( candidate ) ) {
		candidate = `${ base }-${ suffix }`;
		suffix += 1;
	}

	usedIds.add( candidate );
	return candidate;
}

export default function TableOfContents() {
	const [ headings, setHeadings ] = useState( [] );
	const [ open, setOpen ] = useState( false );
	const [ activeId, setActiveId ] = useState( '' );

	useEffect( () => {
		const targetHeadings = Array.from(
			document.querySelectorAll( '.prose h2, .prose h3' )
		);
		const targetSet = new Set( targetHeadings );
		const usedIds = new Set(
			Array.from( document.querySelectorAll( '[id]' ) )
				.filter( ( element ) => ! targetSet.has( element ) )
				.map( ( element ) => element.id )
				.filter( Boolean )
		);

		const collected = targetHeadings.map( ( heading, index ) => {
			const id = uniqueHeadingId( heading, index, usedIds );
			heading.id = id;

			return {
				id,
				text:
					heading.textContent?.trim() || `Раздел ${ index + 1 }`,
				level: heading.tagName.toLowerCase(),
			};
		} );

		setHeadings( collected );
	}, [] );

	useEffect( () => {
		if ( ! window.IntersectionObserver ) {
			return undefined;
		}

		const observer = new IntersectionObserver(
			( entries ) => {
				const visible = entries.find(
					( entry ) => entry.isIntersecting
				);
				if ( visible ) {
					setActiveId( visible.target.id );
				}
			},
			{ rootMargin: '-80px 0px -70% 0px' }
		);

		headings.forEach( ( heading ) => {
			const element = document.getElementById( heading.id );
			if ( element ) {
				observer.observe( element );
			}
		} );

		return () => observer.disconnect();
	}, [ headings ] );

	const scrollTo = useCallback( ( id ) => {
		const element = document.getElementById( id );
		if ( ! element ) {
			return;
		}

		const reduceMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches;
		element.scrollIntoView( {
			behavior: reduceMotion ? 'auto' : 'smooth',
			block: 'start',
		} );
		window.history.replaceState(
			null,
			'',
			`#${ encodeURIComponent( id ) }`
		);
	}, [] );

	if ( headings.length < 2 ) {
		return null;
	}

	return (
		<div className="mb-8 rounded-xl border overflow-hidden">
			<button
				type="button"
				onClick={ () => setOpen( ( current ) => ! current ) }
				className={ TOGGLE_CLASS_NAME }
				aria-expanded={ open }
				aria-controls="scam-dev-table-of-contents"
			>
				Содержание
				<span aria-hidden="true">{ open ? '▴' : '▾' }</span>
			</button>

			{ open && (
				<nav
					id="scam-dev-table-of-contents"
					className="px-5 pb-4 pt-1"
					aria-label="Оглавление"
				>
					<ul className="space-y-1 text-sm">
						{ headings.map( ( heading ) => (
							<li
								key={ heading.id }
								className={
									heading.level === 'h3' ? 'ml-3' : ''
								}
							>
								<button
									type="button"
									onClick={ () => scrollTo( heading.id ) }
									className={ `block w-full text-left py-1 transition-colors hover:opacity-80 ${
										activeId === heading.id
											? 'font-medium'
											: 'opacity-70'
									}` }
									aria-current={
										activeId === heading.id
											? 'location'
											: undefined
									}
								>
									{ heading.text }
								</button>
							</li>
						) ) }
					</ul>
				</nav>
			) }
		</div>
	);
}
