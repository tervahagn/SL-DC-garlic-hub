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

import { UploadApiConfig } from '../../UploadApiConfig.js';
import { AbstractStockPlatform } from './AbstractStockPlatform.js';
export class PexelsPlatform extends AbstractStockPlatform
{
	#searchUri = "https://api.pexels.com/v1/search"

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
		return downloadUri + "?auto=compress&cs=tinysrgb&w=" + this.maxWith + "&h=" + this.maxHeight;
	}

	async #executeSearchQuery()
	{
		try
		{
			const apiUrl = this.#searchUri + "/?query=" + encodeURIComponent(this.currentSearchQuery) +
				"&page=" + this.currentPage + "&per_page=" + this.resultsPerPage;

			const dataToSend  = {"api_url": apiUrl, "headers": {"Authorization": this.apiToken}};
			const options     = {method: "POST", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dataToSend)};
			const results     = await this.fetchClient.fetchData(UploadApiConfig.SEARCH_STOCK_IMAGES, options);

			if (!results || !results.success)
			{
				console.error('Error:', results.error_message || 'Unknown error');
				return;
			}

			const data = results.data;

			//totalHits => Pixabay API is limited to return a maximum of 500 images per query.
			this.totalPages   = data.total_results / this.resultsPerPage;
			this.totalResults = data.total_results;

			this.#prepareResults(data);

			return this.resultList;
		}
		catch(error)
		{
			console.log('Search error:', error.message);
		}
	}

	#prepareResults(json)
	{
		if (!json.photos || !Array.isArray(json.photos))
			throw new Error("Wrong JSON format");

		this.resultList = json.photos.reduce((acc, item) => {
			acc[item.id] = {
				type: "image",
				preview: item.src?.medium || null,
				thumb: item.src?.small || null,
				downloadUrl: item.src?.original || null,
				metadata: {
					origin: "Pixabay",
					description: item.alt || null,
					page_url: item.url || null,
					user: {
						username: item.photographer_id || null,
						name: item.photographer || null,
						url: item.photographer_url,
					},
				}
			};
			return acc;
		}, this.resultList || {});
	}

	hasApiToken()
	{
		if (localStorage.getItem('PexelsApiToken') === null)
			return false

		this.apiToken = localStorage.getItem('PexelsApiToken');
		return true;
	}

	saveToken(token)
	{
		localStorage.setItem('PexelsApiToken', token);
		this.apiToken = token;
	}
}
