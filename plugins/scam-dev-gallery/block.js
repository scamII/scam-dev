( function ( wp ) {
	'use strict';

	const element = wp.element.createElement;
	const InspectorControls = wp.blockEditor.InspectorControls;
	const PanelBody = wp.components.PanelBody;
	const RangeControl = wp.components.RangeControl;
	const TextControl = wp.components.TextControl;

	wp.blocks.registerBlockType( 'scamdev/gallery', {
		edit( props ) {
			const attributes = props.attributes;

			return element(
				wp.element.Fragment,
				{},
				element(
					'div',
					{
						className: props.className,
						style: {
							padding: '24px',
							border: '1px dashed #8c8f94',
							borderRadius: '8px',
							textAlign: 'center',
						},
					},
					'Галерея Scam Dev будет отображена на сайте.'
				),
				element(
					InspectorControls,
					{},
					element(
						PanelBody,
						{ title: 'Настройки галереи' },
						element( RangeControl, {
							label: 'Колонок',
							value: attributes.cols,
							onChange( value ) {
								props.setAttributes( { cols: value } );
							},
							min: 2,
							max: 6,
						} ),
						element( TextControl, {
							label: 'ID галереи (0 — все опубликованные)',
							type: 'number',
							value: attributes.galleryId || 0,
							onChange( value ) {
								props.setAttributes( {
									galleryId: Number.parseInt( value, 10 ) || 0,
								} );
							},
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
