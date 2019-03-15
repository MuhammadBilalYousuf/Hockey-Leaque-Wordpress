var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
	SelectControl = wp.components.SelectControl,
	InspectorControls = wp.editor.InspectorControls;

registerBlockType( 'sponsors-carousel/block', {
	title: 'Sponsors carousel',
	icon: el('svg', { width: 20, height: 20, viewBox: "0 0 320 320" },
		el('path', { d: "m160 0a160 160 0 0 0 -160 160 160 160 0 0 0 160 160 160 160 0 0 0 160 -160 160 160 0 0 0 -160 -160zm-50.7 110.6c9.3 0 18.7 2.2 26.8 6.9l-6.3 16.5c-5.9-3.6-12.7-5.5-19.5-5.7-6.1-.9-14.85 2.6-12.62 10.2 3.62 8.5 14.42 9.2 21.82 13 10.3 3.6 19.6 12.4 19.8 23.9 1.7 11.4-5 23.3-16.1 26.9-15.7 5.1-33.95 3.5-48.45-4.6 1.99-5.6 3.98-11.1 5.98-16.7 8.69 5.1 18.85 7.5 28.77 6.2 6.4.2 12-6.4 8.7-12.6-6.6-9.4-19.73-9.4-28.86-15.4-9.7-5.1-14.33-16.8-11.85-27.3 2.27-13.9 16.95-21.5 29.91-21.2.7 0 1.3-.1 1.9-.1zm106.9.1c10.1-.1 20.6 1.9 29.3 7.4-2 5.6-4 11.2-6 16.7-6-3.8-13.1-5.8-20.2-6.1-11.1-1.1-22.2 6.4-24.5 17.5-3 12.6-2.4 29.2 9.3 37.4 11.3 6.4 25.8 3.8 36.8-2.2 1.8 5.6 3.6 11.1 5.4 16.6-5.3 3.5-11.7 5.3-17.9 6.2-16.9 3-37.1-.3-47.2-15.6-9.7-14.7-10.6-34.2-5-50.7 5.9-16.3 22.7-27.4 40-27.2zm-173.7 30.6c8.03 0 14.59 6.6 14.59 14.6s-6.56 14.6-14.59 14.6c-8.04 0-14.6-6.6-14.6-14.6s6.56-14.6 14.6-14.6zm230.4 2.7c8 0 14.5 6.6 14.5 14.6 0 8.1-6.5 14.6-14.5 14.6-8.1 0-14.6-6.5-14.6-14.6 0-8 6.5-14.6 14.6-14.6zm-230.4 1.3c-5.88 0-10.6 4.7-10.6 10.6s4.72 10.6 10.6 10.6c5.87 0 10.59-4.7 10.59-10.6s-4.72-10.6-10.59-10.6zm3.42 1.5a2 2 0 0 1 1.43 3.4l-5.63 5.8 5.48 5.6a2 2 0 0 1 -2.54 3l-.23-.1a2 2 0 0 1 -.64 -.7l-6.3-6.4a2 2 0 0 1 0 -2.8l7-7.2a2 2 0 0 1 1.43 -.6zm227 1.2c-5.9 0-10.6 4.7-10.6 10.6s4.7 10.6 10.6 10.6c5.8 0 10.5-4.7 10.5-10.6s-4.7-10.6-10.5-10.6zm-4.4 1.5a2 2 0 0 1 1.4 .7l7 7.1a2 2 0 0 1 0 2.8l-6.3 6.4a2 2 0 0 1 -.6 .7l-.3.2a2 2 0 0 1 -2.5 -3.1l5.5-5.6-5.7-5.7a2 2 0 0 1 1.5 -3.5z" } )
	),
	category: 'widgets',
	edit: function( props ) {
		return [
			el( ServerSideRender, {
				block: 'sponsors-carousel/block',
				attributes: props.attributes,
			} ),
			el( InspectorControls, {},
				el( SelectControl, {
					label: props.attributes.label,
					value: props.attributes.id,
					options: props.attributes.list,
					onChange: ( value ) => { props.setAttributes( { id: value } ); },
				} )
			),
		];
	},
	save: function() {
		return null;
	},
} );
