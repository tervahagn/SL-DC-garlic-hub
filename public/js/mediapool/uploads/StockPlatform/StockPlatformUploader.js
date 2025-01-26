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

import { LocalFilesUploader } from "../Local/LocalFilesUploader.js";

export class StockPlatformUploader extends LocalFilesUploader
{
	#stockPlatformFactory = null;
	#stockPlatform = null;

	constructor(stockPlatformFactory, filePreviews, domElements, directoryView, uploaderDialog, fetchClient)
	{
		super(filePreviews, domElements, directoryView, uploaderDialog, fetchClient);
		this.#stockPlatformFactory = stockPlatformFactory;

		for (const [key, value] of Object.entries(this.#stockPlatformFactory.platforms)) {
			this.domElements.addPlatform(key);
		}
		this.domElements.selectStockPlatform.addEventListener("click", (event) => this.#selectPlatform(event));
		this.domElements.savePlatformApiToken.addEventListener("click", (event) => this.#saveApiToken(event));
		this.domElements.checkSearchPlatform.addEventListener("click", (event) => this.#toggleSearchConfig(event));
		this.domElements.checkConfigPlatform.addEventListener("click", (event) => this.#toggleSearchConfig(event));
	}


	#selectPlatform(event)
	{
		let platform = event.target.value;
		this.#stockPlatform = this.#stockPlatformFactory.selectPlatform(platform);
		if (this.#stockPlatform == null)
			return;
		const hasToken = this.#stockPlatform.hasApiToken()
		this.domElements.toggleSearchConfig(hasToken);
		this.domElements.toggleSearchInPlatform(hasToken);

		if (hasToken)
			this.domElements

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
}