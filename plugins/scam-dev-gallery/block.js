( function ( wp ) {
	const el = wp.element.createElement;
	const InspectorControls = wp.blockEditor.InspectorControls;
	const PanelBody = wp.components.PanelBody;
	const RangeControl = wp.components.RangeControl;

	wp.blocks.registerBlockType( 'scamdev/gallery', {
		edit( props ) {
			const attrs = props.attributes;
			return el(
				'div',
				{ className: props.className },
				el(
					'div',
					{
						style: {
							padding: '20px',
							background: '#f0f0f0',
							borderRadius: '8px',
							textAlign: 'center',
						},
					},
					el(
						'div',
						{
							style: {
								display: 'flex',
								gap: '8px',
								justifyContent: 'center',
								marginBottom: '12px',
							},
						},
						el( 'div', {
							style: {
								width: '60px',
								height: '60px',
								background: '#d0d0d0',
								borderRadius: '4px',
							},
						} ),
						el( 'div', {
							style: {
								width: '60px',
								height: '60px',
								background: '#d0d0d0',
								borderRadius: '4px',
							},
						} ),
						el( 'div', {
							style: {
								width: '60px',
								height: '60px',
								background: '#d0d0d0',
								borderRadius: '4px',
							},
						} )
					),
					el(
						'p',
						{
							style: {
								margin: '8px 0 0',
								fontSize: '14px',
								color: '#666',
							},
						},
						'Галерея Scam Dev — все изображения из галерей будут показаны здесь'
					)
				),
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: 'Настройки сетки' },
						el( RangeControl, {
							label: 'Колонок',
							value: attrs.cols || 3,
							onChange( v ) {
								props.setAttributes( { cols: v } );
							},
							min: 2,
							max: 6,
						} )
					)
				)
			);
		},
		save() {
			return null;
		},
	} );
} )( window.wp );
