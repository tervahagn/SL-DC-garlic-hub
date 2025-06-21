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
export class BaseService
{
	#fetchClient       = null;

	constructor(fetchClient)
	{
		this.#fetchClient      = fetchClient;
	}

	/**
	 * as JavaScript do not have protected methods, we use the old "private" workaround
	 */
	async _sendRequest(url, method, data)
	{
		let options;

		if (method === "GET")
			options = {method, headers: { 'Content-Type': 'application/json' }};
		else
		{
			data.csrf_token = this.#detectCsrfTokenInMetaTag();

			options = {method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)};
		}

		const result  = await this.#fetchClient.fetchData(url, options).catch(error => {
			throw new Error(error.message);
		});

		if (!result || !result.success)
			throw new Error(result.error_message);

		return result;
	}

	#detectCsrfTokenInMetaTag()
	{
		const metaTag = document.querySelector('meta[name="csrf-token"]');
		if (metaTag && metaTag.hasAttribute('content'))
			return metaTag.getAttribute('content');

		throw new Error("No CSRF token found in meta tag");
	}
}