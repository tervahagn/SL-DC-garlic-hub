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

import { DirectoryView } from "../treeview/DirectoryView.js";

export class FileUploader
{
    #directoryView = null;
    #filePreviews  = null;
    #fetchClient   = null;

    constructor(directoryView, filePreviews, fechClient)
    {
        this.#filePreviews  = filePreviews;
        this.#directoryView = directoryView;
        this.#fetchClient   = fechClient;
    }

    initFileUpload(uploadButtonElement)
    {
        uploadButtonElement.addEventListener('click', () => this.uploadFiles());
    }

    uploadFiles()
    {

        const fileList = this.#filePreviews.getFileList();
        if (fileList.length === 0)
        {
            alert("Keine Dateien zum Hochladen ausgewählt!");
            return;
        }

        if (this.#directoryView.getActiveNodeId() === 0)
        {
            alert("Choose a directory first.");
            return;
        }
        const progressBar = document.getElementById('progressBar');
        const progressContainer = document.getElementById('progressContainer');

        (async () => {
            for (const file of fileList) {
                const formData = new FormData();
                formData.append("files[]", file);
                formData.append("node_id", this.#directoryView.getActiveNodeId());

                const apiUrl = '/async/mediapool/upload';
                const options = { method: "POST", body: formData };

                const result = await this.#fetchClient.uploadWithProgress(apiUrl, options, (progress) => {
                    progressBar.style.width = progress + '%';
                    progressBar.textContent = Math.round(progress) + '%';
                });

/*
                const result = await this.#fetchClient.fetchData(apiUrl, options).catch(error => {
                    console.error('Fetch error for file:', file.name, error.message);
                    return null;
                });
*/


                if (!result || !result.success)
                {
                    console.error('Error for file:', file.name, result?.error_message || 'Unknown error');
                }
                else
                {
                    console.log('File uploaded successfully:', file.name);
                }
            }
        })();

    }
}