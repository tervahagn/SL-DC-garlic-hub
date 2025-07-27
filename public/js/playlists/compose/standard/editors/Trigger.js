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

	addWallclock()
	{
		try
		{
			let url = ThymianConfig.main_site + "?site=playlists_async_trigger"
				+ url_separator + "item_id=" + item_id + url_separator + "action=add_wallclock"
			;

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
			MyRequest.onload = function ()
			{
				if (MyRequest.status !== 200) {
					jThymian.printError(MyRequest.statusText);
					return;
				}

				let jsonResponse = JSON.parse(MyRequest.responseText);
				let wallclocks   = document.getElementById("wallclocks")
				let item_id      = wallclocks.childElementCount;
				// use replaceAll (defined by the ECMAScript 2021) is too young for March 2022. maybe later
				wallclocks.insertAdjacentHTML('beforeend', jsonResponse.html.replace(/\[WALLCLOCK_COUNT]/g, item_id));

				MyTriggerForm.initDateTimeTriggerFunctions(item_id)
			};
			MyRequest.onerror = function ()
			{
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname)
			};
			MyRequest.send(null);
		}
		catch (err)
		{
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	}

	addAccesskey()
	{
		try
		{
			let url = ThymianConfig.main_site + "?site=playlists_async_trigger"
				+ url_separator + "item_id=" + item_id + url_separator + "action=add_accesskey"
			;

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
			MyRequest.onload = function ()
			{
				if (MyRequest.status !== 200) {
					jThymian.printError(MyRequest.statusText);
					return;
				}

				let jsonResponse = JSON.parse(MyRequest.responseText);
				let accesskeys   = document.getElementById("accesskeys")
				let item_id      = accesskeys.childElementCount;
				// use replaceAll (defined by the ECMAScript 2021) is too young for March 2022. maybe later
				accesskeys.insertAdjacentHTML('beforeend', jsonResponse.html.replace(/\[ACCESSKEY_COUNT]/g, item_id));
				MyTriggerForm.initAccessKeyTriggerFunctions(item_id)
			};
			MyRequest.onerror = function ()
			{
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname)
			};
			MyRequest.send(null);
		}
		catch (err)
		{
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	}

	addTouch()
	{
		try
		{
			let url = ThymianConfig.main_site + "?site=playlists_async_trigger"
				+ url_separator + "item_id=" + item_id + url_separator + "action=add_touch"
			;

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
			MyRequest.onload = function ()
			{
				if (MyRequest.status !== 200) {
					jThymian.printError(MyRequest.statusText);
					return;
				}

				let jsonResponse = JSON.parse(MyRequest.responseText);
				let touches   = document.getElementById("touches")
				let item_id   = touches.childElementCount;
				// use replaceAll (defined by the ECMAScript 2021) is too young for March 2022. maybe later
				touches.insertAdjacentHTML('beforeend', jsonResponse.html.replace(/\[TOUCH_COUNT]/g, item_id));
				MyTriggerForm.initTouchTriggerFunctions(item_id)
			};
			MyRequest.onerror = function ()
			{
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname)
			};
			MyRequest.send(null);
		}
		catch (err)
		{
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	}

	addNotify()
	{
		try
		{
			let url = ThymianConfig.main_site + "?site=playlists_async_trigger"
				+ url_separator + "item_id=" + item_id + url_separator + "action=add_notify"
			;

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
			MyRequest.onload = function ()
			{
				if (MyRequest.status !== 200) {
					jThymian.printError(MyRequest.statusText);
					return;
				}

				let jsonResponse = JSON.parse(MyRequest.responseText);
				let notifies     = document.getElementById("notifies")
				let item_id      = notifies.childElementCount;
				// use replaceAll (defined by the ECMAScript 2021) is too young for March 2022. maybe later
				notifies.insertAdjacentHTML('beforeend', jsonResponse.html.replace(/\[NOTIFY_COUNT]/g, item_id));
				MyTriggerForm.initNotifyTriggerFunctions(item_id)
			};
			MyRequest.onerror = function ()
			{
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname)
			};
			MyRequest.send(null);
		}
		catch (err)
		{
			ThymianLog.logException(err);
			jThymian.printError(err);
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

		this.#editDialog.openDialog();

	}

}

