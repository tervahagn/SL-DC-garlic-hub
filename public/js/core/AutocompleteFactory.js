import { Autocomplete } from './Autocomplete.js';

export class AutocompleteFactory
{
	constructor()
	{
	}x

	create(field_name, api_endpoint)
	{
		const input_element     = document.getElementById(field_name+"_search");
		const data_list_element = document.getElementById(field_name+"_suggestions");
		const selected_hiddden  = document.getElementById(field_name+"_selected");

		if (!input_element || !data_list_element || !api_endpoint)
		{
			console.error('Invalid input or datalist element IDs.');
			return null;
		}

		return new Autocomplete(input_element, data_list_element, selected_hiddden, api_endpoint);
	}
}