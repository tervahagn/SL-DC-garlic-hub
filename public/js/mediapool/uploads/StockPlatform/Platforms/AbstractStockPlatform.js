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
export class AbstractStockPlatform
{
	#apiToken = "";
	#fetchClient = null;
	#resultsList = {};

	constructor(fetchClient)
	{
		this.#fetchClient = fetchClient;
	}

	search(query)
	{
		throw "search() must be implemented in subclass";
	}

	get resultsList()
	{
		return this.#resultsList;
	}

	get apiToken() { return this.#apiToken; }

	set apiToken(value)	{ this.#apiToken = value; }


	get fetchClient() {	return this.#fetchClient; }
}