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

import {TreeViewApiConfig} from "./TreeViewApiConfig.js";

export class TreeViewService
{
	#fetchClient = null;

	constructor(fetchClient)
	{
		this.#fetchClient = fetchClient;
	}

	async addNode(nodeId, name)
	{
		const sendData = {"node_id": nodeId, "name": name};
		const options    = {method: "POST", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(sendData)}

		const result = await this.#fetchClient.fetchData(TreeViewApiConfig.BASE_NODE_URI, options).catch(error => {
			throw new Error(error.message);
		});

		if (!result || !result.success)
			throw new Error(result.error_text);
	}


	async editNode(nodeId, data)
	{
		const sendData = {"node_id": nodeId, "name": name};
		const options    = {method: "PATCH", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(sendData)}

		const result = await this.#fetchClient.fetchData(TreeViewApiConfig.BASE_NODE_URI, options).catch(error => {
			throw new Error(error.message);
		});

		if (!result || !result.success)
			throw new Error(result.error_text);
		
		return result;
	}


	async deleteNode(nodeId)
	{
		const sendData = {"node_id": nodeId};
		const options    = {method: "DELETE", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(sendData)}

		const result = await this.#fetchClient.fetchData(TreeViewApiConfig.BASE_NODE_URI, options).catch(error => {
			throw new Error(error.message);
		});

		if (!result || !result.success)
			throw new Error(result.error_text);

	}



	async moveNodeTo(sourceKey, targetKey, targetRegion)
	{
		const dataToSend = {"src_node_id": sourceKey, "target_node_id": targetKey, "target_region": targetRegion};
		const options    = {method: "POST", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dataToSend)}

		const result = await this.#fetchClient.fetchData(TreeViewApiConfig.MOVE_NODE_URI, options).catch(error => {
			throw new Error(error.message);
		});

		if (!result || !result.success)
			throw new Error(result.error_text);
	}



}