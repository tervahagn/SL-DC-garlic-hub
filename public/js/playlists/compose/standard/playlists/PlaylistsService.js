import {PlaylistsApiConfig} from "./PlaylistsApiConfig.js";
import {BaseService} from "../../../../core/Base/BaseService.js";

export class PlaylistsService extends BaseService
{
	async loadSelectorTemplate()
	{
		const url    = PlaylistsApiConfig.SELECTOR_URI;
		const result = await this._sendRequest(url, "GET", null);

		return result.template;
	}

}

