( function ( wp ) {
	const { SelectControl, Spinner } = wp.components;
	const { __ } = wp.i18n;
	const { useState, useEffect } = wp.element;
	const { useBlockProps } = wp.blockEditor;
	const apiFetch = wp.apiFetch;

	wp.blocks.registerBlockType( 'plugiva/pulse', {
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const { pulseId } = attributes;

			const [ pulses, setPulses ] = useState( [] );
			const [ loading, setLoading ] = useState( true );

			useEffect( function () {
				apiFetch( { path: '/plugiva-pulse/v1/pulses' } )
					.then( function ( data ) {
						setPulses( data || [] );
						setLoading( false );
					} )
					.catch( function () {
						setLoading( false );
					} );
			}, [] );

			const blockProps = useBlockProps();

			if ( loading ) {
				return wp.element.createElement(
					'div',
					blockProps,
					wp.element.createElement( Spinner )
				);
			}

			return wp.element.createElement(
				'div',
				blockProps,
				wp.element.createElement( SelectControl, {
					label: __( 'Pulse', 'plugiva-pulse' ),
					value: pulseId,
					options: [
						{ label: __( 'Select a pulse', 'plugiva-pulse' ), value: '' },
						...pulses.map( p => ( { label: p.title, value: p.id } ) ),
					],
					onChange: value => setAttributes( { pulseId: value } ),
                    __next40pxDefaultSize: true,
	                __nextHasNoMarginBottom: true,
				} )
			);
		},
		save: function () {
			return null; // dynamic block
		},
	} );
} )( window.wp );
