export class WebcamElements
{
    #capturePhotoButton   = document.getElementById("capturePhoto");
    #startRecordingButton = document.getElementById("recording");
    #webcamVideo          = document.getElementById("webcamVideo");
    #toggleWebcam         = document.getElementById("toggleWebcam");
    #startWebcamUpload    = document.getElementById("startWebcamUpload");
    #previewRecordsArea   = document.getElementById("previewRecordsArea");

    getCapturePhotoButton()
    {
        return this.#capturePhotoButton;
    }

    getStartRecordingButton()
    {
        return this.#startRecordingButton;
    }

    getWebcamVideo()
    {
        return this.#webcamVideo;
    }

    getToggleWebcam()
    {
        return this.#toggleWebcam;
    }
    getStartWebcamUpload()
    {
        return this.#startWebcamUpload;
    }

    getPreviewRecordsArea()
    {
       return this.#previewRecordsArea;
    }

}