/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

export class FetchClient
{
    async fetchData(url, options = {})
    {
        const defaultOptions  = {method: 'GET', headers: { 'Accept': 'application/json' } };
        const config          = { ...defaultOptions, ...options };
        const response        = await fetch(url, config);

        this.#checkResponse(response);

        const contentType = response.headers.get('Content-Type');
        if (contentType?.includes('application/json'))
            return await response.json();
         else
            return await response.text();
    }


    async uploadWithProgress(url, options = {}, onProgress)
    {
        const xhr = new XMLHttpRequest();

        return new Promise((resolve, reject) => {
            xhr.open(options.method, url, true);

            // Track upload progress
            xhr.upload.onprogress = (event) => {
                if (event.lengthComputable && onProgress)
                {
                    const percentComplete = (event.loaded / event.total) * 100;
                    onProgress(percentComplete);
                }
            };

            // Handle response
            xhr.onload = () => {
                const response = {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    text: () => Promise.resolve(xhr.responseText),
                    json: () => Promise.resolve(JSON.parse(xhr.responseText))
                };

                if (xhr.status >= 200 && xhr.status < 300)
                {
                    resolve(response.json());
                }
                else
                {
                    reject(new Error(`HTTP-Error: ${xhr.status}`));
                }
            };

            // Handle errors
            xhr.onerror = () => reject(new Error('Network error occurred.'));

            xhr.send(options.body);
        });
    }


    #checkResponse(response)
    {
        if (response.status === 401)
            throw new Error('Unauthorized - Please log in again.');
        else if (response.status === 500)
            throw new Error('Server error - Try again later.');
        else if (!response.ok)
            throw new Error(`HTTP-Error: ${response.status}`);

    }
}
