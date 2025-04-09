import {MediaApiConfig} from "../../../../mediapool/media/MediaApiConfig.js";
import {PlaylistsApiConfig} from "./PlaylistsApiConfig.js";

export class PlaylistsService
{
	async loadSelectorTemplate()
	{
		const url    = PlaylistsApiConfig.SELECTOR;
		const result = await this.#sendRequest(url, "GET", null);

		return result.template;
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

