import {ItemsApiConfig} from "./ItemsApiConfig.js";

export class ItemsService
{
	fetchClient       = null;

	constructor(fetchClient)
	{
		this.fetchClient      = fetchClient;
	}

	insertFromMediaPool(id, playlistId)
	{
		const data = {
			playlist_id: playlistId,
			"id": id,
			"source": "mediapool"
		};
		return this.#sendRequest(ItemsApiConfig.INSERT_URI, "POST",  data);
	}

	async #sendRequest(url, method, data)
	{
		let options = {};

		if (method === "GET")
			options = {method, headers: { 'Content-Type': 'application/json' }};
		else
			options = {method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)};

		const result  = await this.fetchClient.fetchData(url, options).catch(error => {
			throw new Error(error.message);
		});

		if (!result || !result.success)
			throw new Error(result.error_message);

		return result;
	}
}