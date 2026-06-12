import { useState, useEffect } from 'react';

export default function RelatedPosts( { postId } ) {
	const [ posts, setPosts ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		if ( ! postId ) {
			return;
		}
		const base = ( window.scamDev?.restUrl || '/wp-json/' ).replace(
			/\/$/,
			''
		);
		const url = `${ base }/wp/v2/posts?per_page=3&exclude=${ postId }&_embed`;
		fetch( url )
			.then( ( r ) => r.json() )
			.then( ( data ) => {
				setPosts( data.slice( 0, 3 ) );
				setLoading( false );
			} )
			.catch( () => setLoading( false ) );
	}, [ postId ] );

	if ( loading || posts.length === 0 ) {
		return null;
	}

	return (
		<div className="mt-12">
			<h3
				className="text-lg font-bold mb-6"
				style={ { color: 'var(--color-text)' } }
			>
				<span style={ { color: 'var(--color-accent)' } }>#</span>{ ' ' }
				Похожие записи
			</h3>
			<div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
				{ posts.map( ( post ) => (
					<a
						key={ post.id }
						href={ post.link }
						className="block rounded-xl border overflow-hidden group no-underline transition-all duration-300 hover:-translate-y-1"
						style={ {
							background: 'var(--color-card-bg)',
							borderColor: 'var(--color-card-border)',
							boxShadow: 'var(--shadow-sm)',
						} }
					>
						{ post._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]
							?.source_url && (
							<div className="overflow-hidden">
								<img
									src={
										post._embedded[
											'wp:featuredmedia'
										][ 0 ].source_url
									}
									alt=""
									className="w-full h-36 object-cover transition-transform duration-500 group-hover:scale-105"
									loading="lazy"
								/>
							</div>
						) }
						<div className="p-4">
							<h4
								className="text-sm font-semibold line-clamp-2 transition-colors group-hover:text-[var(--color-accent)]"
								style={ { color: 'var(--color-text)' } }
							>
								{ post.title.rendered.replace( /<[^>]*>/g, '' ) }
							</h4>
							<time
								className="text-xs mt-2 block"
								style={ { color: 'var(--color-text-muted)' } }
							>
								{ new Date( post.date ).toLocaleDateString(
									'ru-RU'
								) }
							</time>
						</div>
					</a>
				) ) }
			</div>
		</div>
	);
}
