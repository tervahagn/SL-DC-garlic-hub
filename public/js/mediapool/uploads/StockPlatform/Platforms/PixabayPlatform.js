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

import { ApiConfig } from '../../ApiConfig.js';
import { AbstractStockPlatform } from './AbstractStockPlatform.js';

export class PixabayPlatform extends AbstractStockPlatform
{
	#searchUri = "https://pixabay.com/api"

	constructor(fetchClient)
	{
		super(fetchClient);
	}

	async search(query)
	{
		this.currentPage = 1;
		this.currentSearchQuery = query;

		return await this.#executeSearchQuery();
	}

	async loadNextPage()
	{
		if (this.currentPage >= this.totalPages)
			return;

		this.currentPage++;
		return await this.#executeSearchQuery();
	}

	async determineMediaDownloadUrl(downloadUri)
	{
		// no need to do any further processing for Pixabay
		return downloadUri;
	}

	async #executeSearchQuery()
	{
		try
		{
			const apiUrl = this.#searchUri + "/?q=" + encodeURIComponent(this.currentSearchQuery) +
				"&page=" + this.currentPage + "&per_page=" + this.resultsPerPage +
				"&key=" + this.apiToken;

			const dataToSend  = {"api_url": apiUrl};
			const options = {method: "POST", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dataToSend)}
			const results = await this.fetchClient.fetchData(ApiConfig.SEARCH_STOCK_IMAGES, options);

			if (!results || !results.success)
			{
				console.error('Error:', results.error_message || 'Unknown error');
				return;
			}

			const data = results.data;

			//totalHits => Pixabay API is limited to return a maximum of 500 images per query.
			this.totalPages   = data.totalHits / this.resultsPerPage;
			this.totalResults = data.total;

			return this.#prepareResults(data);
		}
		catch(error)
		{
			console.log('Search error:', error.message);
		}
	}

	#prepareResults(json)
	{
		if (!json.hits || !Array.isArray(json.hits))
			throw new Error("Wrong JSON format");


		// traverse results and create a new array with the required fields
		return json.hits.map(item => ({
			id: item.id,
			type: "image",
			preview: item.webformatURL || null,
			thumb: item.previewURL || null,
			downloadUrl: item.largeImageURL || null,
			metadata: {
				pool: "Pixabay",
				created: item.created_at || null,
				description: item.tags || null,
				user: {
					username: item.username || null,
					name: item.username || null,
					url: null,
				},
			}
		}));

	}

	hasApiToken()
	{
		if (localStorage.getItem('PixabayApiToken') === null)
			return false

		this.apiToken = localStorage.getItem('PixabayApiToken');
		return true;
	}

	saveToken(token)
	{
		localStorage.setItem('PixabayApiToken', token);
		this.apiToken = token;
	}
}
