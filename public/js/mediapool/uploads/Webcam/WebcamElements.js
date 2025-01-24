export class WebcamElements
{
    #capturePhotoButton   = document.getElementById("capturePhoto");
    #startRecordingButton = document.getElementById("recording");
    #webcamVideo          = document.getElementById("webcamVideo");
    #startWebcamUpload    = document.getElementById("startWebcamUpload");
    #previewRecordsArea   = document.getElementById("previewRecordsArea");
    #selectCamera         = document.getElementById("selectCamera");
    #toggleCamera         = document.getElementById("toggleWebcam");

    getCapturePhotoButton()
    {
        return this.#capturePhotoButton;
    }

    getToggleCamera()
    {
        return this.#toggleCamera;
    }


    getStartRecordingButton()
    {
        return this.#startRecordingButton;
    }

    getWebcamVideo()
    {
        return this.#webcamVideo;
    }

    getStartWebcamUpload()
    {
        return this.#startWebcamUpload;
    }

    getPreviewRecordsArea()
    {
       return this.#previewRecordsArea;
    }

    getSelectCamera()
    {
        return this.#selectCamera;
    }

    addCameraToSelect(cameraId, cameraName)
    {
        const option       = document.createElement('option');
        option.value       = cameraId;
        option.textContent = cameraName;

        this.#selectCamera.appendChild(option);
    }

}