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

import {PlayerActions} from "./PlayerActions.js";
import {PlayerService} from "../PlayerService.js";
import {FetchClient} from "../../core/FetchClient.js";
import {AutocompleteFactory} from "../../core/AutocompleteFactory.js";
import {CreateContextMenu} from "../../playlists/overview/CreateContextMenu.js";
import {PlayerSettingsContextMenu} from "./PlayerSettingsContextMenu.js";

document.addEventListener("DOMContentLoaded", function()
{
	const contextMenu = new PlayerSettingsContextMenu(
		document.getElementById("playerSettingsContextMenuTemplate")
	);
	contextMenu.init(document.getElementsByClassName("player-contextmenu"),);

	const playerService = new PlayerService(new FetchClient())
    const autocompleteFactory = new AutocompleteFactory();
    const playerActions =  new PlayerActions(autocompleteFactory, playerService);
    playerActions.init();
});