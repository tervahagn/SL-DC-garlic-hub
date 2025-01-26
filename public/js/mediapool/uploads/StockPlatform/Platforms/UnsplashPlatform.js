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

import { AbstractStockPlatform } from './AbstractStockPlatform.js';
export class UnsplashPlatform extends AbstractStockPlatform
{
	#searchUri = "https://api.unsplash.com/search/photos"

	constructor(fetchClient)
	{
		super(fetchClient);
	}


	async search(query)
	{
		try
		{
			const apiUrl = `${this.#searchUri}?query=${encodeURIComponent(query)}&per_page=20&client_id=${this.apiToken}`;
			const results = await this.fetchClient.fetchData(apiUrl);

			if (!results)
			{
				console.error('Error:', result || 'Unknown error');
				return;
			}

			return this.#prepareResults(results);
		}
		catch(error)
		{
			console.log('Search error:', error.message);
		}
	}

	#prepareResults(json)
	{
		if (!json.results || !Array.isArray(json.results)) {
			throw new Error("Ungültiges JSON-Format");
		}

		// Ergebnisse durchlaufen und gewünschte Werte extrahieren
		return json.results.map(item => ({
			id: item.id,
			type: "image",
			preview: item.urls?.small || null,
			thumb: item.urls?.thumb || null,
			download: item.urls?.regular || null
		}));

	}


	hasApiToken()
	{
		if (localStorage.getItem('UnsplashApiToken') === null)
			return false

		this.apiToken = localStorage.getItem('UnsplashApiToken');
		return true;
	}

	saveToken(token)
	{
		localStorage.setItem('UnsplashApiToken', token);
		this.apiToken = token;
	}
}