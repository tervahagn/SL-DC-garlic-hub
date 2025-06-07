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

import {BaseService} from "../../../../core/Base/BaseService.js";

export class WidgetsService extends BaseService
{
	MyModalEdit = {};
	fetchClient = null;

	async fetchConfiguration(id)
	{
		let url = "/async/playlists/widget/fetch/" + id;
		return await this._sendRequest(url, "GET");
	}

	/*
	saveWidgetValues(json_values) {
		try {
			let url = ThymianConfig.main_site + "?site=playlists_async_widget"
				+ url_separator + "item_id=" + this.itemId + url_separator + "action=save";

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("POST", url, true);
			MyRequest.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			MyRequest.onload = () => {
				if (MyRequest.status !== 200) {
					jThymian.printError(MyRequest.statusText);
				} else {
					let jsonResponse = JSON.parse(MyRequest.responseText);
					if (jsonResponse.success === false) {
						this.MyModalEdit.setErrorText(jsonResponse.message);
					} else {
						this.MyModalEdit.close();
						jPlaylist.setSaveAlert();
					}
				}
			};
			MyRequest.onerror = () => {
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname);
			};
			let body = JSON.stringify(json_values);

			MyRequest.send(body);
		} catch (err) {
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	}

	 */
}
