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


export class WebcamElements
{
	#detectCameras        = document.getElementById("detectCameras");
	#capturePhotoButton   = document.getElementById("capturePhoto");
	#startRecordingButton = document.getElementById("recording");
	#webcamVideo          = document.getElementById("webcamVideo");
	#previewRecordsArea   = document.getElementById("previewRecordsArea");
	#selectCamera         = document.getElementById("selectCamera");
	#toggleCamera         = document.getElementById("toggleWebcam");
	#startFileUpload      = document.getElementById("startCameraUpload");

	get detectCameras() {return this.#detectCameras; }

	get capturePhotoButton() { return this.#capturePhotoButton;	}

	get startRecordingButton() { return this.#startRecordingButton; }

	get webcamVideo() { return this.#webcamVideo; }

	get previewRecordsArea() { return this.#previewRecordsArea; }

	get selectCamera() { return this.#selectCamera; }

	get toggleCamera() { return this.#toggleCamera; }

	get startFileUpload() {	return this.#startFileUpload;}

	addCameraToSelect(cameraId, cameraName)
	{
		const option = document.createElement('option');
		option.value = cameraId;
		option.textContent = cameraName;

		this.#selectCamera.appendChild(option);
	}

}