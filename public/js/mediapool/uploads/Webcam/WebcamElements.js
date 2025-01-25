
export class WebcamElements
{
	#capturePhotoButton   = document.getElementById("capturePhoto");
	#startRecordingButton = document.getElementById("recording");
	#webcamVideo          = document.getElementById("webcamVideo");
	#previewRecordsArea   = document.getElementById("previewRecordsArea");
	#selectCamera         = document.getElementById("selectCamera");
	#toggleCamera         = document.getElementById("toggleWebcam");
	#startFileUpload      = document.getElementById("startCameraUpload");

	get capturePhotoButton()
	{
		return this.#capturePhotoButton;
	}

	get startRecordingButton()
	{
		return this.#startRecordingButton;
	}

	get webcamVideo()
	{
		return this.#webcamVideo;
	}

	get previewRecordsArea()
	{
		return this.#previewRecordsArea;
	}

	get selectCamera()
	{
		return this.#selectCamera;
	}

	get toggleCamera()
	{
		return this.#toggleCamera;
	}

	get startFileUpload()
	{
		return this.#startFileUpload;
	}

	addCameraToSelect(cameraId, cameraName)
	{
		const option = document.createElement('option');
		option.value = cameraId;
		option.textContent = cameraName;

		this.#selectCamera.appendChild(option);
	}

}