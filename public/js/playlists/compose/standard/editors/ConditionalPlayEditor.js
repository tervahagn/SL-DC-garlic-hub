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

class ConditionalPlayEditor
{
	constructor()
	{
		this.item_id = 0;
		this.MyModalEdit = {};
	}

	load(element) {
		this.item_id = getUnitIdFromAttrId(element.id, 1);
		this.retrieveTimerDialog();
	}

	retrieveTimerDialog()
	{
		try {
			let url = ThymianConfig.main_site + "?site=playlists_async_conditional_play"
				+ url_separator + "item_id=" + this.item_id + url_separator + "action=retrieve";

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
			MyRequest.onload = () => {
				if (MyRequest.status !== 200) {
					jThymian.printError(MyRequest.statusText);
				} else {
					let jsonResponse = JSON.parse(MyRequest.responseText);
					MyConditionalPlayForm = new CreateConditionalPlayForm(jsonResponse.html, jsonResponse.title);
					this.MyModalEdit = new TModalContainer('');

					MyConditionalPlayForm.openOverlay(this.MyModalEdit);
				}
			};
			MyRequest.onerror = () => {
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname);
			};
			MyRequest.send(null);
		} catch (err) {
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	}

	saveValues(values) {
		try {
			let url = ThymianConfig.main_site + "?site=playlists_async_conditional_play"
				+ url_separator + "item_id=" + this.item_id + url_separator + "action=save" + values;

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
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
						let icon = document.getElementById("conditional_play_id_" + this.item_id);
						if (MyConditionalPlayForm.enable_conditional_play.checked) {
							icon.classList.add("icon_values_setted");
						} else {
							icon.classList.remove("icon_values_setted");
						}
						jPlaylist.setSaveAlert();
					}
				}
			};
			MyRequest.onerror = () => {
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname);
			};

			MyRequest.send(null);
		} catch (err) {
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	}
}