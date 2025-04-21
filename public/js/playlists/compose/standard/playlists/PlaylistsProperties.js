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

export class PlaylistsProperties
{
	#playlistDuration = document.getElementById('playlistDuration');
	#totalItems       = document.getElementById('totalItems');
	#totalFilesize    = document.getElementById('totalFilesize');
	#toggleShuffle    = document.getElementById("toggleShuffle");
	#shufflePicking   = document.getElementById("shufflePicking");
	#playerExport     = document.getElementById("playerExport");
	#pickingValue     = 0;
	#playlistsService = null;
	#lang             = null;

	constructor(playlistsService, lang)
	{
		this.#playlistsService = playlistsService;
		this.#lang = lang;
	}

	init(playlistId)
	{
		this.#toggleShuffle.addEventListener('click', async () => {
			const result = await this.#playlistsService.toggleShuffle(playlistId);
		//	if (!result.success)

			this.display(result.playlist_metrics);
			PlaylistsProperties.notifySave();
		});
		this.#shufflePicking.addEventListener('change', async () => {

			this.#pickingValue = this.#shufflePicking.value;

			const result = await this.#playlistsService.shufflePicking(playlistId, this.#pickingValue);
			this.display(result.playlist_metrics);

			PlaylistsProperties.notifySave();
		});
		this.#playerExport.addEventListener('click', async () =>
		{
			await this.#playlistsService.export(playlistId);
			PlaylistsProperties.removeSave();
		});
	}

	static notifySave()
	{
		document.getElementById("playerExport").classList.add("notify-save");
	}

	static removeSave()
	{
		document.getElementById("playerExport").classList.remove("notify-save");
	}

	setOptions(playlist)
	{
		this.#toggleShuffle.checked = playlist.shuffle === 1;
		this.#pickingValue   = playlist.shuffle_picking;
	}


	display(playlistMetrics)
	{
		this.#playlistDuration.innerHTML = Utils.formatSecondsToTime(playlistMetrics.duration);
		this.#totalItems.innerHTML = playlistMetrics.count_items;

		this.#shufflePicking.innerHTML = Array.from({length: playlistMetrics.count_items})
			.map((_, i) => `<option value="${i}">${i === 0 ? lang['picking_all'] : i}</option>`)
			.join('');
		this.#totalFilesize.innerHTML    = Utils.formatBytes(playlistMetrics.filesize);
		// properties.owner_duration;

		this.#updateShufflePickingOptions(playlistMetrics.count_items)

	}

	#updateShufflePickingOptions(countItems)
	{
		while (this.#shufflePicking.options.length > 1)
		{
			this.#shufflePicking.remove(1);
		}

		const maxOptions = countItems - 1;
		for (let i = 1; i <= maxOptions; i++)
		{
			const option = document.createElement("option");
			option.value = i.toString();
			option.textContent = i.toString();
			this.#shufflePicking.appendChild(option);
		}

		this.#calculatePickingValue(countItems);
		this.#shufflePicking.value = this.#pickingValue;
	}

	#calculatePickingValue(countItems)
	{
		if (this.#pickingValue > countItems)
		{
			 this.#pickingValue = countItems;
		}
	}

}
