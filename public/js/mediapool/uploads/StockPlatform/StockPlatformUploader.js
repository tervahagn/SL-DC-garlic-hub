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
			if (checkbox.checked)
			{
				this.domElements.downloadStatus.innerHTML = "Start downloading...";
				const mediaUrl = await this.#stockPlatform.determineMediaDownloadUrl(checkbox.getAttribute("data-download"));
				await this.uploadExternalFile(mediaUrl);
				this.domElements.downloadStatus.innerHTML = "Finish Downloading";
			}
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

		this.domElements.searchResultsArea.innerHTML = "";

		const results = await this.#stockPlatform.search(this.domElements.searchTerms.value);

		if (results === null)
			return;

		for (const result of results)
		{
			this.#addSearchResult(result);
		}
	}

	async #loadNextPage()
	{
		return; // temporary disabled to save requests during developing
		if (this.domElements.searchResultsArea.scrollTop + this.domElements.searchResultsArea.clientHeight >= this.domElements.searchResultsArea.scrollHeight)
		{
			if (this.#stockPlatform === null)
				return;

			const results = await this.#stockPlatform.loadNextPage();

			if (results === null)
				return;

			for (const result of results)
			{
				this.#addSearchResult(result);
			}
		}
	}

	#addSearchResult(result)
	{
		const container = document.getElementById("result-media-template").content.cloneNode(true).firstElementChild;
		if (result.type === "image")
		{
			const img = container.querySelector(".result-thumbnail");
			img.src = result.thumb;
			img.id = result.id;
			img.alt = result.metadata.description;
			const imgPreview = container.querySelector(".result-preview");
			imgPreview.src = result.preview;
			imgPreview.alt = result.metadata.description;
		}
		const downloadChecker = container.querySelector(".result-checkbox");
		downloadChecker.setAttribute("data-download", result.downloadUrl);
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