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
import {Utils} from "../../../../core/Utils.js";

export class PlayListsProperties
{
	#playlistDuration = document.getElementById('playlistDuration');
	#totalItems       = document.getElementById('totalItems');
	#totalFilesize    = document.getElementById('totalFilesize');
	#toggleShuffle    = document.getElementById("toggleShuffle");
	#shufflePicking   = document.getElementById("shufflePicking");
	#saveChanges      = document.getElementById("saveChanges");

	#playlistsService = null;
	#lang             = null;


	constructor(playlistsService, lang)
	{
		this.#playlistsService = playlistsService;
		this.#lang = lang;
	}

	init(playlistId)
	{
		this.#toggleShuffle.addEventListener('click', async () =>
		{
			await this.#playlistsService.toggleShuffle(playlistId);
			PlayListsProperties.notifySave();
		});
		this.#shufflePicking.addEventListener('change', async () =>
		{
			await this.#playlistsService.shufflePicking(playlistId, this.#shufflePicking.value);
			PlayListsProperties.notifySave();
		});
		this.#saveChanges.addEventListener('click', async () =>
		{
			await this.#playlistsService.export(playlistId);
			PlayListsProperties.removeSave();
		});
	}

	static notifySave()
	{
		document.getElementById("saveChanges").classList.add("notify-save");
	}

	static removeSave()
	{
		document.getElementById("saveChanges").classList.remove("notify-save");
	}


	display(playlistProperties)
	{
		this.#playlistDuration.innerHTML = Utils.formatSecondsToTime(playlistProperties.duration);
		this.#totalItems.innerHTML = playlistProperties.count_items;

		this.#shufflePicking.innerHTML = Array.from({length: playlistProperties.count_items})
			.map((_, i) => `<option value="${i}">${i === 0 ? lang['picking_all'] : i}</option>`)
			.join('');
		this.#totalFilesize.innerHTML    = Utils.formatBytes(playlistProperties.filesize);
		// properties.owner_duration;


		this.#toggleShuffle.checked = playlistProperties.shuffle === 1;
		this.#shufflePicking.value = playlistProperties.shuffle_picking;
	}


}
