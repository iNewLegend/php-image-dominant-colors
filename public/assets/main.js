/**
 * author Leonid Vinikov <leonidvinikov@gmail.com>
 */
const CLICK_HERE_IMAGE_URL = '/assets/click-here.jpg',
	BACKEND_URL = '/backend.php',
	DEFAULT_PERCENTAGE_FRAC_DIGITS = 2;

const elements = {
	spinner: document.querySelector( '.spinner' ),

	error: document.querySelector( '#error' ),

	main: {
		self: document.querySelector( '#main' ),


		controlPanel: {
			self: document.getElementById( "control-panel" ),

			settings: {
				maxColorsInput: document.getElementById( "max-colors-input" ),
				colorsSensitivity: document.getElementById( "colors-sensitivity" ),
				maxTimeoutInput: document.getElementById( "max-timeout-input" ),
			}
		},

		imageInput: {
			preview: document.getElementById( "preview" ),

			fileInput: document.getElementById( "file-input" ),
		}
	},

	content: document.getElementById( "content" ),

	templates: {
		colorsResult: document.getElementById( 'colors-result' ),
		colorsResultItem: document.getElementById( 'colors-result-item' ),
	}
};

let gColorsMergeSensitivity = '0.00';

function numberWithCommas( x ) {
	return x.toString().replace( /\B(?=(\d{3})+(?!\d))/g, "," );
}

/**
 * Preview the image, before sending analyze request.
 */
function onPreviewChange() {
	const { controlPanel } = elements.main,
		{ preview, fileInput } = elements.main.imageInput,
		file = fileInput.files[ 0 ],
		reader = new FileReader();

	if ( ! file ) {
		return setDefaultState();
	}

	reader.onload = function( e ) {
		preview.src = e.target.result;
		fileInput.src = preview.src;

		controlPanel.self.classList.remove( "hidden" );

		elements.content.innerHTML = '';
	};

	if ( file ) {
		reader.readAsDataURL( file );
	} else {
		preview.src = CLICK_HERE_IMAGE_URL;
	}
}

/**
 * Set the default state of the page (clear the image preview, and hide the control panel, etc...).
 */
function setDefaultState() {
	const { controlPanel } = elements.main,
		{ preview, fileInput } = elements.main.imageInput;

	preview.src = CLICK_HERE_IMAGE_URL;

	fileInput.value = '';

	preview.classList.remove( "hidden" );

	controlPanel.self.classList.add( "hidden" );

	elements.content.innerHTML = '';
}

/**
 * Set the state of the control panel (disable/enable the buttons/preview).
 *
 * @param {boolean} state
 */
function setControlPanelState( state ) {
	const { controlPanel } = elements.main;

	controlPanel.self.querySelectorAll( 'button' ).forEach( ( button ) => {
		button.disabled = state;
	} );

	// Disable the file input.
	elements.main.imageInput.fileInput.disabled = state;
}

/**
 * Set the colors sensitivity merge value.
 */
function setColorSensitivity() {
	let text = 'Enter the color sensitivity percent ( Default 0, Minimum :0.01, Maximum: 100 )';

	if ( '0.00' === gColorsMergeSensitivity ) {
		text += '\nHigher means more colors, lower means less colors.';
	}

	gColorsMergeSensitivity = prompt( text, gColorsMergeSensitivity );
}

/**
 * Show error message, with fade out effect.
 *
 * @param {string} message
 * @param {number} [timeout=3000]
 */
function showError( message, timeout = 3000 ) {
	const { error, controlPanel } = elements;

	error.innerHTML = 'Error: ' + message;

	error.classList.remove( 'hidden' );

	setControlPanelState( true );

	error.classList.add( 'fade-out' );

	setTimeout( () => {
		error.classList.add( 'hidden' );
		setControlPanelState( false );
	}, 3000 );
}

/**
 * Send the analyze request to the server.
 *
 * @param {FormData} formData
 *
 * @return {Promise<{success}|any|boolean>}
 */
async function sendAnalyzeRequest( formData ) {
	setControlPanelState( true );

	elements.spinner.classList.toggle( 'hidden' );

	const abortController = new AbortController();

	setTimeout( () => abortController.abort(), elements.main.controlPanel.settings.maxTimeoutInput.value );

	let result = false;

	try {
		result = await (await fetch( BACKEND_URL, {
			method: 'POST',
			body: formData,
			signal: abortController.signal,
		} )).json();
	} catch ( e ) {
		if ( e.name === 'AbortError' ) {
			 setTimeout( () => showError( 'Request timeout' ) );
		} else {
			setTimeout( () => showError( e.message ) );
		}
	}

	setControlPanelState( false );

	elements.spinner.classList.toggle( 'hidden' );

	if ( ! result?.success ) {
		showError( result.message );

		return false;
	}

	return result || false;
}

/**
 * Create the colors result table.
 *
 * @param {{}} result
 *
 * @return Element
 */
function createColorsResult( result ) {
	const { content } = elements,
		{ colorsResult, colorsResultItem } = elements.templates;

	const template = colorsResult.content.cloneNode( true ),
		tableBodyEl = template.querySelector( 'tbody' );

	// Remove all previous results.
	content.innerHTML = '';

	result.statistics.forEach( ( item ) => {
		const itemEl = colorsResultItem.content.cloneNode( true ),
			colorEl = itemEl.querySelector( '.color' ),
			colorValueEl = itemEl.querySelector( '.color-value' ),
			percentageEl = itemEl.querySelector( '.percentage' )

		colorValueEl.innerHTML = colorValueEl.innerHTML.replace( '{color}', item.color );

		percentageEl.innerHTML = percentageEl.innerHTML.replace(
			'{percentage}',
			item.percentage.toFixed( DEFAULT_PERCENTAGE_FRAC_DIGITS )
		);

		colorEl.style.backgroundColor = '#' + item.color;

		tableBodyEl.appendChild( itemEl );
	} );

	const generalEl = template.querySelector( '.statistics.general' );
		loadTime = generalEl.querySelector('.load-time'),
		displayedColors = generalEl.querySelector('.displayed-colors'),
		totalColors = generalEl.querySelector('.total-colors'),
		uniquesColors = generalEl.querySelector('.unique-colors'),
		{ load_time, displayed_colors_count, total_colors_count, unique_colors_count } = result;

	loadTime.innerHTML = loadTime.innerHTML.replace(
		'{loadTime}',
		load_time.toFixed( DEFAULT_PERCENTAGE_FRAC_DIGITS )
	);

	displayedColors.innerHTML = displayedColors.innerHTML.replace( '{displayedColors}', displayed_colors_count );
	totalColors.innerHTML = totalColors.innerHTML.replace( '{totalColors}', numberWithCommas( total_colors_count ) );
	uniquesColors.innerHTML = uniquesColors.innerHTML.replace( '{uniqueColors}', unique_colors_count );

	if ( result.merge ) {
		const mergeEl = template.querySelector( '.statistics.merge' ),
			totalColors = mergeEl.querySelector( '.merged-colors' ),
			uniquesColors = mergeEl.querySelector( '.unique-colors' ),
			sensitivity = mergeEl.querySelector( '.sensitivity' ),
			{ total_colors_count, unique_colors_count } = result.merge;

		totalColors.innerHTML = totalColors.innerHTML.replace( '{mergedColors}', numberWithCommas( total_colors_count ) );
		uniquesColors.innerHTML = uniquesColors.innerHTML.replace( '{uniqueColors}', unique_colors_count );
		sensitivity.innerHTML = sensitivity.innerHTML.replace( '{sensitivity}', gColorsMergeSensitivity );

		mergeEl.classList.remove( 'hidden' );
	}

	content.appendChild( template );
}

/**
 * Takes selected image and pass it to `sendAnalyzeRequest` function, on result - create the result table.
 *
 * @return {Promise<void>}
 */
async function analyzeImage() {
	const formData = new FormData();

	formData.append( 'file', elements.main.imageInput.fileInput.files[ 0 ] );
	formData.append( 'max_colors', parseInt( elements.main.controlPanel.settings.maxColorsInput.value ) );
	formData.append( 'colors_merge_sensitivity', gColorsMergeSensitivity );

	// Using fetch API to send the image to the server.
	const result = await sendAnalyzeRequest( formData );

	if ( result ) {
		createColorsResult( result );
	}
}

setDefaultState();
