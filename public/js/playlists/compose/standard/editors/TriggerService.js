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
'use strict';

import {BaseService} from "../../../../core/Base/BaseService.js";

export class TriggerService extends BaseService
{
	async fetchBeginTrigger(id)
	{
		let url = "/async/playlists/item/begin-trigger/" + id;
		return await this._sendRequest(url, "GET");
	}

	async storeBeginTrigger(id, data)
	{
		let url = "/async/playlists/item/begin-trigger";
		data["item_id"] = id;

		return await this._sendRequest(url, "PATCH", data);
	}
}
