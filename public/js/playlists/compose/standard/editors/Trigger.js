/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
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

import {PlaylistsProperties} from "../playlists/PlaylistsProperties.js";

export class Trigger
{
	#editDialog = null;
	#triggerForm = null;
	#triggerService = null;
	#itemData = null;
	#html = null;

	constructor(editDialog, triggerForm, triggerService)
	{
		this.#editDialog    = editDialog;
		this.#triggerForm    = triggerForm;
		this.#triggerService = triggerService;
	}

	async fetchBeginTrigger (itemId)
	{
		const result = await this.#triggerService.fetchBeginTrigger(itemId);
		if (result.success === true)
		{
			this.#itemData = result.data;
			this.#html = result.html;
		}
	}

	initDialog()
	{
		this.#editDialog.setTitle(this.#itemData.item_name);
		this.#editDialog.setId(this.#itemData.item_id);
		this.#editDialog.setContent(this.#html);

		this.#triggerForm.init(this.#itemData.begin_trigger);

		let saveCallBack = async (e) =>
		{
			e.preventDefault();
			let values = this.#triggerForm.collectValues();
			let result = await this.#triggerService.storeBeginTrigger(this.#itemData.item_id, values);
			if (result.success === false)
			{
				this.#editDialog.setErrorMessage(result.error_message);
			}
			else
			{
				this.#editDialog.closeDialog();
				PlaylistsProperties.notifySave();
			}

		}

		this.#editDialog.onSave(saveCallBack);
		this.#editDialog.onCancel();
		this.#editDialog.setWidth("80%")
		this.#editDialog.openDialog();

	}

}

