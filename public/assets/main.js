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

	const loadTime = template.querySelector('.load-time'),
		displayedColors = template.querySelector('.displayed-colors'),
		totalColors = template.querySelector('.total-colors'),
		uniquesColors = template.querySelector('.unique-colors'),
		{ load_time, displayed_colors_count, total_colors_count, unique_colors_count } = result;

	loadTime.innerHTML = loadTime.innerHTML.replace(
		'{loadTime}',
		load_time.toFixed( DEFAULT_PERCENTAGE_FRAC_DIGITS )
	);

	displayedColors.innerHTML = displayedColors.innerHTML.replace( '{displayedColors}', displayed_colors_count );
	totalColors.innerHTML = totalColors.innerHTML.replace( '{totalColors}', total_colors_count );
	uniquesColors.innerHTML = uniquesColors.innerHTML.replace( '{uniqueColors}', unique_colors_count );

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

	// Using fetch API to send the image to the server.
	const result = await sendAnalyzeRequest( formData );

	if ( result ) {
		createColorsResult( result );
	}
}

setDefaultState();
