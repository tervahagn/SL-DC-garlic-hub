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

import { AbstractBaseUploader } from "../AbstractBaseUploader.js";

export class WebcamUploader extends AbstractBaseUploader
{
    #maxRecordTime  = 300; // 5min
    #webcam         = null;
    #filePreviews = null;
	#durationList = [];
    #mediaRecorder  = null;
    #recordedChunks = [];

    constructor(webcam, filePreviews, domElements, directoryView, uploaderDialog, fetchClient)
    {
		super(domElements, directoryView, uploaderDialog, fetchClient);
        this.#webcam       = webcam;
        this.#filePreviews = filePreviews;

        this.domElements.capturePhotoButton.addEventListener("click", () => this.#capturePhoto());
        this.domElements.startRecordingButton.addEventListener("click", (event) => this.#toggleRecording(event));
        this.domElements.selectCamera.addEventListener("change", (event) => this.#selectWebcam(event));
        this.domElements.toggleCamera.addEventListener("click", (event) => this.#toggleWebcam(event));
		this.domElements.startFileUpload.disabled = true;
		this.domElements.startFileUpload.addEventListener('click', () => this.uploadFiles());

        this.#selectCameras();
	}

	uploadFiles()
	{
		const fileList = this.#filePreviews.getFileList();
		if (fileList.length === 0)
		{
			alert("No files selected for upload.");
			return;
		}

		if (this.directoryView.getActiveNodeId() === 0)
		{
			alert("Choose a directory first.");
			return;
		}

		(async () => {
			for (const [id, file] of /** @type {Object.<string, File>} */ Object.entries(fileList))
			{
				// maybe some files in the queue where deleted.
				let container = document.querySelector(`[data-preview-id="${id}"]`);
				if (!container)
					continue;
				try
				{

					this.uploaderDialog.disableActions();
					const formData = new FormData();
					formData.append("files[]", file);
					formData.append("node_id", this.directoryView.getActiveNodeId());

					const apiUrl   = '/async/mediapool/upload';
					const options  = {method: "POST", body: formData};

					const progressBar = this.createProgressbar(container);

					this.fetchClient.initUploadWithProgress();
					let xhr = this.fetchClient.getUploadProgressHandle();
					this.#filePreviews.setUploadHandler(xhr, id);
					/**
					 * @type {{ error_message?: string, success: boolean }}
					 */
					const results = await this.fetchClient.uploadWithProgress(apiUrl, options, (progress) => {
						progressBar.style.display = "block";
						progressBar.style.width = progress + "%";
						progressBar.textContent = Math.round(progress) + "%";
					});

					for (const result of results)
					{
						if (!result?.success)
							console.error('Error for file:', file.name, result?.error_message || 'Unknown error');
						else
							this.#filePreviews.removeFromPreview(id);
					}

				}
				catch(error)
				{
					if (error.message === 'Upload aborted.')
						console.log('Upload aborted for file:', file.name);
					else
					{
						console.log('Upload failed for file:', file.name, '\n', error.message);
						container.className = "previewContainerError";
					}
					this.uploaderDialog.enableActions()
				}
				finally
				{
					this.uploaderDialog.enableActions()
				}

			}
		})();
	}

    #selectWebcam(event)
    {
        const selectedValue = event.target.value;
        this.#webcam.selectCamera(selectedValue);
        this.domElements.toggleCamera.disabled = selectedValue === "-";

    }

    async #toggleWebcam(event)
    {
        if(event.target.checked)
        {
            await this.#webcam.startCamera();
            this.domElements.selectCamera.disabled = true;
            this.domElements.capturePhotoButton.disabled = false;
            this.domElements.startRecordingButton.disabled = false;

        }
        else
        {
            this.#webcam.stopCamera();
            this.domElements.selectCamera.disabled = false;
            this.domElements.capturePhotoButton.disabled = true;
            this.domElements.startRecordingButton.disabled = true;
        }
    }

    #toggleRecording(event)
    {
        if (this.#mediaRecorder === null || this.#mediaRecorder.state !== 'recording')
        {
            let stream = this.domElements.webcamVideo.srcObject;
            this.#recordedChunks = [];
	        this.#mediaRecorder = new MediaRecorder(stream);

            this.#mediaRecorder.start(1000); // 1000ms = 1 sec per chunk
			const startTime = Date.now();
            event.target.textContent = "Stop Aufnahme";

            setTimeout(() => {
                if (this.#mediaRecorder.state === 'recording')
                {
                    this.#mediaRecorder.stop();
                    event.target.textContent = "Start Aufnahme";
                }
            }, this.#maxRecordTime * 1000);

            this.#mediaRecorder.ondataavailable = (event) => {
                this.#recordedChunks.push(event.data);
            };

            this.#mediaRecorder.onstop = () => {
                const blob = new Blob(this.#recordedChunks, { type: "video/webm;" });
				const endTime = Date.now();
				const durationInSeconds = (endTime - startTime) / 1000; // ms to sec

				const file = new File([blob], "recorded-video.webm", { type: blob.type });
				const metadata = {"duration":  durationInSeconds };
				this.#filePreviews.addFile(file, metadata);
				this.#mediaRecorder = null;

            };
        }
        else
        {
            this.#mediaRecorder.stop();
            event.target.textContent = "Start Aufnahme";
            this.#mediaRecorder = null;
        }

    }

    #capturePhoto()
    {
        const dataURL = this.#webcam.capturePhoto(document.createElement("canvas"))

        const [header, base64] = dataURL.split(',');
        const mimeType = header.match(/:(.*?);/)[1]; // MIME-Typ extrahieren

        const binary = atob(base64);
        const array = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++)
        {
            array[i] = binary.charCodeAt(i);
        }

        const file = new File([array], "webcam_shoot.jpg", { type: mimeType });

        this.#filePreviews.addFile(file, null);
    }

    #selectCameras()
    {
        this.#webcam.detectVideoDevices().then(videoDevices =>
        {
            for(let webcam of videoDevices)
            {
                this.domElements.addCameraToSelect(webcam.deviceId, webcam.label)
            }
        })
        .catch(err => {console.error('Fehler:', err);});
    }

}