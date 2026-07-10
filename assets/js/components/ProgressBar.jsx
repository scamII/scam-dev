import { useState, useEffect } from 'react';

export default function ProgressBar() {
	const [ progress, setProgress ] = useState( 0 );

	useEffect( () => {
		let frame = 0;

		const update = () => {
			frame = 0;
			const scrollTop = window.scrollY;
			const available =
				document.documentElement.scrollHeight - window.innerHeight;
			setProgress(
				available > 0
					? Math.min( ( scrollTop / available ) * 100, 100 )
					: 0
			);
		};

		const handler = () => {
			if ( frame === 0 ) {
				frame = window.requestAnimationFrame( update );
			}
		};

		window.addEventListener( 'scroll', handler, { passive: true } );
		window.addEventListener( 'resize', handler, { passive: true } );
		update();

		return () => {
			window.removeEventListener( 'scroll', handler );
			window.removeEventListener( 'resize', handler );
			if ( frame ) {
				window.cancelAnimationFrame( frame );
			}
		};
	}, [] );

	return (
		<div
			className="fixed top-0 left-0 w-full h-[3px] z-50 pointer-events-none"
			aria-hidden="true"
		>
			<div
				className="h-full transition-all duration-150"
				style={ {
					width: `${ progress }%`,
					background: 'var(--color-accent)',
				} }
			/>
		</div>
	);
}
