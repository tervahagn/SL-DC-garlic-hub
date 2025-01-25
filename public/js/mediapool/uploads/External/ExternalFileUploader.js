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

export class ExternalFileUploader
{
	#domElements    = null
    #directoryView  = null;
    #fetchClient    = null;
    #uploaderDialog = null;

    constructor(domElements, directoryView, uploaderDialog, fetchClient)
    {
        this.#domElements    = domElements;
        this.#directoryView  = directoryView;
        this.#uploaderDialog = uploaderDialog;
        this.#fetchClient    = fetchClient;

		this.#domElements.startFileUpload.addEventListener("click", () => this.uploadFile());
		this.#domElements.externalLinkField.addEventListener("input", () => this.#handleUploadButton());
        this.disableUploadButton();
    }


    uploadFile()
    {
        if (this.#directoryView.getActiveNodeId() === 0)
        {
            alert("Choose a directory first.");
            return;
        }

        (async () => {
                const filePath = this.#domElements.externalLinkField.value;
                try
                {

                    this.#disableActions();
                    const formData = new FormData();
                    formData.append("node_id", this.#directoryView.getActiveNodeId());
                    formData.append("external_link", filePath);

                    const apiUrl   = '/async/mediapool/uploadExternal';
                    const options  = {method: "POST", body: formData};

                    const result = await this.#fetchClient.fetchData(apiUrl, options);

                    if (!result || !result.success)
                        console.error('Error for file:', filePath, result?.error_message || 'Unknown error');
                    else
                    {
						this.#domElements.externalLinkField.value = "";
                        this.disableUploadButton();
                    }


                }
                catch(error)
                {
                    if (error.message === 'Upload aborted.')
                        console.log('Upload aborted for file:', filePath);
                    else
                    {
                        console.log('Upload failed for file:', filePath, '\n', error.message);
                    }
                    this.#enableActions()
                }
                finally
                {
                    this.#enableActions()
                }

        })();

    }

    #handleUploadButton()
    {
        if (this.#domElements.externalLinkField.validity.valid)
            this.enableUploadButton();
        else
            this.disableUploadButton();

    }

    disableUploadButton()
    {
		this.#domElements.startFileUpload.disabled = true;
    }

    enableUploadButton()
    {
		this.#domElements.startFileUpload.disabled = false;
    }

    #disableActions()
    {
        this.#uploaderDialog.disableActions();
        document.getElementById("linkTab").disabled = true;
    }

    #enableActions()
    {
        this.#uploaderDialog.enableActions();
        document.getElementById("linkTab").disabled = false;
    }


}