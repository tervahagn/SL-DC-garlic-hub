import {ItemsApiConfig} from "./ItemsApiConfig.js";
import {BaseService}    from "../../../../core/Base/BaseService.js";

export class ItemsService extends BaseService
{
	insertFromMediaPool(id, playlistId, position)
	{
		const data = {
			playlist_id: playlistId,
			"id": id,
			"position": position,
			"source": "media"
		};
		return this._sendRequest(ItemsApiConfig.INSERT_URI, "POST",  data);
	}

	async updateItemsOrders(playlistId, itemsPositions)
	{
		const url = ItemsApiConfig.BASE_URI;
		const data = {
			playlist_id: playlistId,
			items_positions: itemsPositions
		};
		return await this._sendRequest(url, "PATCH", data);
	}

	async delete(playlistId, itemId)
	{
		const url = ItemsApiConfig.BASE_URI;
		const data = {
			playlist_id: playlistId,
			"item_id": itemId
		};
		return await this._sendRequest(url, "DELETE", data);
	}

	async loadByPlaylistId(playlistId)
	{
		const url = ItemsApiConfig.LOAD_PLAYLIST_ITEMS_URI + "/" + playlistId;
		return await this._sendRequest(url, "GET",  []);
	}
}