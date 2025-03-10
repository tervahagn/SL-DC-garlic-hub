/**
 * The Autocomplete class provides auto-suggestion functionality by sending
 * the user’s input to an API endpoint and displaying the suggestions in a datalist.
 * It supports debouncing to avoid frequent API requests while the user is typing.
 */
export class Autocomplete
{
	/**
	 * Debounce delay in milliseconds to prevent frequent API calls.
	 * @type {number}
	 * @static
	 * @default 300
	 */
	static DEBOUNCE_DELAY = 300;

	input_element      = null;
	selected_hidden    = null;
	data_list_element  = null;
	api_endpoint       = null;
	#debounce_timeout  = null;

	/**
	 * Creates an instance of the Autocomplete class.
	 * @param {HTMLInputElement} input_element - The input element where the user types their query.
	 * @param {HTMLDataListElement} data_list_element - The datalist element where suggestions are displayed.
	 * @param {HTMLInputElement} selected_hidden - The hidden input element to store the selected value's data.
	 * @param {string} api_endpoint - The API endpoint URL to fetch suggestions from.
	 */
	constructor(input_element, data_list_element, selected_hidden, api_endpoint)
	{
		this.input_element      = input_element;
		this.selected_hidden    = selected_hidden;
		this.data_list_element  = data_list_element;
		this.api_endpoint       = api_endpoint;

		// Attach input event listener with debouncing
		this.input_element.addEventListener('input', () =>
			this.#debounce(() => this.fetchSuggestions(), Autocomplete.DEBOUNCE_DELAY)
		);

		// Add a 'change' listener to detect when a user selects from the datalist
		this.input_element.addEventListener('change', () => this.#handleSelection());
	}

	/**
	 * Fetches suggestions from the API based on the user's input value.
	 * This method is triggered after a debounce delay to reduce the number of API requests.
	 *
	 * @async
	 * @returns {Promise<void>}
	 */
	async fetchSuggestions()
	{
		const query = this.input_element.value;
		if (query.length < 1) // Only fetch if input is not empty
		{
			this.#clearSelection();  // Clear the hidden field when input is cleared
			return;
		}

		try
		{
			const response    = await fetch(this.api_endpoint + query);
			const suggestions = await response.json();
			this.#updateDataList(suggestions);
		}
		catch (error)
		{
			console.error('Error fetching suggestions:', error);
		}
	}

	getHiddenIdElement()
	{
		return this.selected_hidden;
	}

	getEditFieldElement()
	{
		return this.input_element;
	}

	/**
	 * set the hidden id and the text field with values.
	 */
	setInputFields(id, text)
	{
		this.selected_hidden.value = id;
		this.input_element.value = text;
	}

	/**
	 * Clears the values of all input fields.
	 */
	clearAll()
	{
		this.data_list_element.innerHTML = '';
		this.input_element.value = 0;
		this.#clearSelection();
	}

	/**
	 * Updates the datalist element with the provided suggestions.
	 * Each suggestion is added as an option element to the datalist.
	 *
	 * @param {Array<Object>} suggestions - An array of suggestion objects containing both display text and a value.
	 */
	#updateDataList(suggestions)
	{
		this.data_list_element.innerHTML = ''; // Clear existing options

		suggestions.forEach(suggestion => {
			const option = document.createElement('option');
			option.value = suggestion.name;

			option.setAttribute('data-value', suggestion.id);
			this.data_list_element.appendChild(option);
		});
	}

	/**
	 * Handles the selection of an option from the datalist.
	 * This method checks if the user selected a value that matches an option in the datalist
	 * and then stores the associated `data-value` in the hidden input field.
	 */
	#handleSelection()
	{
		const value = this.input_element.value;
		const options = this.data_list_element.querySelectorAll('option');

		// Loop through the options to find a match
		options.forEach(option => {
			if (option.value === value)
			{
				this.selected_hidden.value = option.getAttribute('data-value');
				this.selected_hidden.dispatchEvent(new Event('change'));  // Manuelles Auslösen des change-Events
			}
		});
	}

	/**
	 * Clears the value of the selected hidden field.
	 */
	#clearSelection()
	{
		this.selected_hidden.value = '';
	}


	/**
	 * Debounces the provided function, delaying its execution by the specified delay time.
	 * Ensures that the function is only called after the user stops typing for the delay period.
	 *
	 * @private
	 * @param {Function} func - The function to debounce.
	 * @param {number} delay - The delay in milliseconds.
	 */
	#debounce(func, delay)
	{
		clearTimeout(this.#debounce_timeout);  // Clear any existing timeout
		this.#debounce_timeout = setTimeout(func, delay);  // Set a new timeout
	}
}
