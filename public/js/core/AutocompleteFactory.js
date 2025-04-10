/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

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
		const selected_hiddden  = document.getElementById(field_name); // hidden is the field we get data from

		if (!input_element || !data_list_element || !api_endpoint)
		{
			console.error('Invalid input or datalist element IDs.');
			return null;
		}

		return new Autocomplete(input_element, data_list_element, selected_hiddden, api_endpoint);
	}
}