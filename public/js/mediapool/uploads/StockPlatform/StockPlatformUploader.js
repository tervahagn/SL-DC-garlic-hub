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

import { BaseUploader } from "../BaseUploader.js";

export class StockPlatformUploader extends BaseUploader
{
	#stockPlatformFactory = null;
	#stockPlatform = null;

	constructor(stockPlatformFactory, domElements, directoryView, uploaderDialog, fetchClient)
	{
		super(domElements, directoryView, uploaderDialog, fetchClient);
		this.#stockPlatformFactory = stockPlatformFactory;

		for (const [key, value] of Object.entries(this.#stockPlatformFactory.platforms))
		{
			this.domElements.addPlatform(key);
		}

		if (localStorage.getItem("lastPlatform") !== null)
		{
			this.#selectPlatform(localStorage.getItem("lastPlatform"));
			this.domElements.selectStockPlatform.value = localStorage.getItem("lastPlatform");
		}

		this.domElements.selectStockPlatform.addEventListener("click", (event) => this.#selectPlatform(event.target.value));
		this.domElements.savePlatformApiToken.addEventListener("click", (event) => this.#saveApiToken(event));
		this.domElements.checkSearchPlatform.addEventListener("click", (event) => this.#toggleSearchConfig(event));
		this.domElements.checkConfigPlatform.addEventListener("click", (event) => this.#toggleSearchConfig(event));
		this.domElements.searchStockPlatform.addEventListener("click", (event) => this.#search(event));
		this.domElements.startFileUpload.addEventListener("click", () => this.#startDownload());

		this.domElements.searchResultsArea.addEventListener('scroll', () => this.#loadNextPage());
	}

	async #startDownload()  //Downlod from Medis-Platform, but upload to us
	{
		const checkboxes = document.querySelectorAll('.result-checkbox');
		for (const checkbox of checkboxes)
		{
			if (!checkbox.checked)
				continue;

			if (!Object.hasOwn(this.#stockPlatform.resultList, checkbox.id))
				continue;

			const uri = this.#stockPlatform.resultList[checkbox.id].downloadUrl;
			const url = new URL(uri);
			const pathname = url.pathname;
			const filename = pathname.split('/').pop();

			this.domElements.downloadStatus.innerHTML = "Start downloading: " + filename;;

			const mediaUrl = await this.#stockPlatform.determineMediaDownloadUrl(uri);

			let metadata   = this.#stockPlatform.resultList[checkbox.id].metadata;
			await this.uploadExternalFile(mediaUrl, metadata);

			this.domElements.downloadStatus.innerHTML = "Finish downloading: " + filename;

			checkbox.checked = false;

		}
	}

	#selectPlatform(platform)
	{
		this.#stockPlatform = this.#stockPlatformFactory.selectPlatform(platform);
		if (this.#stockPlatform == null)
			return;

		const hasToken = this.#stockPlatform.hasApiToken()
		this.domElements.toggleSearchConfig(hasToken);
		this.domElements.toggleSearchInPlatform(hasToken);
		this.domElements.toogleHasVideo(this.#stockPlatform.hasVideos);

		localStorage.setItem("lastPlatform", platform);
	}

	#saveApiToken()
	{
		if (this.domElements.platformApiToken.value !== "")
			this.#stockPlatform.saveToken(this.domElements.platformApiToken.value);

		this.domElements.toggleSearchConfig(this.#stockPlatform.apiToken !== "");
	}

	#toggleSearchConfig(event)
	{
		if (event.target.id === "checkSearchPlatform")
			this.domElements.toggleSearchInPlatform(event.target.checked);
		else
			this.domElements.toggleSearchInPlatform(!event.target.checked);

		if (this.#stockPlatform.hasApiToken())
			this.domElements.platformApiToken.value = this.#stockPlatform.apiToken;

	}

	async #search(event)
	{
		if (this.#stockPlatform === null)
			return;

		this.#stockPlatform.resetResultList();

		this.domElements.searchResultsArea.innerHTML = "";

		const results = await this.#stockPlatform.search(this.domElements.searchTerms.value);

		if (results === null)
			return;

		for (const [id, item] of Object.entries(results))
		{
			this.#addSearchResult(id, item);
		}
	}

	async #loadNextPage()
	{
//		return; // temporary disabled to save requests during developing
		if (this.domElements.searchResultsArea.scrollTop + this.domElements.searchResultsArea.clientHeight >= this.domElements.searchResultsArea.scrollHeight)
		{
			if (this.#stockPlatform === null)
				return;

			const results = await this.#stockPlatform.loadNextPage();

			if (results === null)
				return;

			for (const [id, item] of Object.entries(results))
			{
				this.#addSearchResult(id, item);
			}
		}
	}

	#addSearchResult(id, item)
	{
		const container = document.getElementById("result-media-template").content.cloneNode(true).firstElementChild;
		if (item.type === "image")
		{
			const img = container.querySelector(".result-thumbnail");
			img.src = item.thumb;
			img.alt = item.metadata.description;
			const imgPreview = container.querySelector(".result-preview");
			imgPreview.src = item.preview;
			imgPreview.alt = item.metadata.description;
		}
		const downloadChecker = container.querySelector(".result-checkbox");
		downloadChecker.setAttribute("id", id);
		downloadChecker.addEventListener("click", (event) => this.#markedDownload(event));

		this.domElements.searchResultsArea.appendChild(container);
	}

	#markedDownload(event)
	{
		const checkboxes  = document.querySelectorAll('.result-checkbox');
		const isAnyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

		this.domElements.startFileUpload.disabled = !isAnyChecked;
	}
}