import { useState, useEffect } from 'react';

export default function ProgressBar() {
	const [ progress, setProgress ] = useState( 0 );

	useEffect( () => {
		const handler = () => {
			const scrollTop = window.scrollY;
			const docHeight =
				document.documentElement.scrollHeight - window.innerHeight;
			setProgress(
				docHeight > 0
					? Math.min( ( scrollTop / docHeight ) * 100, 100 )
					: 0
			);
		};
		window.addEventListener( 'scroll', handler, { passive: true } );
		handler();
		return () => window.removeEventListener( 'scroll', handler );
	}, [] );

	return (
		<div className="fixed top-0 left-0 w-full h-[3px] z-50 pointer-events-none">
			<div
				className="h-full transition-all duration-150"
				style={ {
					width: progress + '%',
					background: 'var(--color-accent)',
					boxShadow: `0 0 8px var(--color-accent)`,
				} }
			/>
		</div>
	);
}
