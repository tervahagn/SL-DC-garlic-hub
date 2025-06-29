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
"use strict";

import {InsertContextMenu} from "./InsertContextMenu.js";
import {SelectorFactory}   from "./SelectorFactory.js";
import {ItemsService}      from "./items/ItemsService.js";
import {FetchClient}       from "../../../core/FetchClient.js";
import {ItemList}          from "./items/ItemList.js";
import {ItemFactory}       from "./items/ItemFactory.js";
import {DragDropHandler}   from "./DragDropHandler.js";
import {PlaylistsProperties} from "./playlists/PlaylistsProperties.js";
import {PlaylistsService} from "./playlists/PlaylistsService.js";
import {WidgetsService}   from "./editors/WidgetsService.js";
import {WidgetFactory} from "./editors/WidgetFactory.js";

document.addEventListener("DOMContentLoaded", function ()
{
	const dropTarget = document.getElementById("thePlaylist");
	const playlistId = document.getElementById("playlist_id").value;

	const playlistsService    = new PlaylistsService(new FetchClient());
	const itemsService        = new ItemsService(new FetchClient());
	const playlistsProperties = new PlaylistsProperties(playlistsService, lang);
	const itemsList           = new ItemList(new ItemFactory(new WidgetFactory()), itemsService, dropTarget, playlistsProperties);
	const dragDropHandler     = new DragDropHandler(dropTarget, itemsService, itemsList);
	dragDropHandler.playlistId = playlistId;

	const insertContextMenu = new InsertContextMenu(new SelectorFactory(playlistsService), dragDropHandler);

	insertContextMenu.init(playlistId);
	itemsList.buildPlaylist(playlistId);

	playlistsProperties.init(playlistId);

	const widgetEdit = document.getElementsByClassName("edit-widget");
	for (let i = 0; i < widgetEdit.length; i++)
	{
		widgetEdit[i].onclick = function ()
		{
			let widgetService = new WidgetsService(new FetchClient());
			let widgetData =  widgetService.fetchConfiguration(widgetEdit[i].parentElement.dataset.itemid);

			/*
	let jsonResponse = JSON.parse(MyRequest.responseText);

	this.MyWidgetForm = new CreateWidgetForm(jsonResponse.preferences, jsonResponse.values, jsonResponse.translations);
	this.MyWidgetForm.parsePreferences();
	this.MyModalEdit = new TModalContainer('');
	this.MyWidgetForm.openOverlay(this.MyModalEdit);
	*/

		}
	}


});