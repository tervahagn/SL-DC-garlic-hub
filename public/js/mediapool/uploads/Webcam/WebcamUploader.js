
export class WebcamUploader
{
    #maxRecordTime = 60;
    #webcam = null;
    #domElements = null;
    #webcamPreviews = null;
    #directoryView = null;
    #fetchClient = null;
    #uploaderDialog = null;
    #mediaRecorder = null;
    #recordedChunks = [];

    constructor(webcam, webcamPreviews, domElements, directoryView, uploaderDialog, fetchClient)
    {
        this.#webcam         = webcam;
        this.#webcamPreviews = webcamPreviews;
        this.#domElements    = domElements;
        this.#directoryView  = directoryView;
        this.#uploaderDialog = uploaderDialog;
        this.#fetchClient    = fetchClient;
        this.#domElements.capturePhotoButton.addEventListener("click", () => this.#capturePhoto());
        this.#domElements.startRecordingButton.addEventListener("click", (event) => this.#toggleRecording(event));
        this.#domElements.selectCamera.addEventListener("change", (event) => this.#selectWebcam(event));
        this.#domElements.toggleCamera.addEventListener("click", (event) => this.#toggleWebcam(event));
		this.#domElements.startFileUpload.disabled = true;

        this.#selectCameras();
	}

    disableUploadButton()
    {
        this.#domElements.startFileUpload.disabled = true;
    }

    enableUploadButton()
    {
        this.#domElements.startFileUpload.disabled = false;
    }

    #selectWebcam(event)
    {
        const selectedValue = event.target.value;
        this.#webcam.selectCamera(selectedValue);
        this.#domElements.toggleCamera.disabled = selectedValue === "-";

    }

    async #toggleWebcam(event)
    {
        if(event.target.checked)
        {
            await this.#webcam.startCamera();
            this.#domElements.selectCamera.disabled = true;
            this.#domElements.capturePhotoButton.disabled = false;
            this.#domElements.startRecordingButton.disabled = false;

        }
        else
        {
            this.#webcam.stopCamera();
            this.#domElements.selectCamera.disabled = false;
            this.#domElements.capturePhotoButton.disabled = true;
            this.#domElements.startRecordingButton.disabled = true;
        }
    }

    #toggleRecording(event)
    {
        if (this.#mediaRecorder === null || !this.#mediaRecorder.state === 'recording')
        {
            let stream = this.#domElements.webcamVideo.srcObject;
            this.#recordedChunks = [];
            this.#mediaRecorder = new MediaRecorder(stream);
            this.#mediaRecorder.start(1000); // 1000ms = 1 Sekunde pro Chunk
           // let startTime = Date.now(); Todo: set a timer
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
                const blob = new Blob(this.#recordedChunks, { type: "video/webm" });
                // Use the Blob for further processing or display in the browser memory
                console.log('Recorded video blob:', blob);

                const file = new File([blob], "recorded-video.webm", { type: blob.type });

                this.#webcamPreviews.addFile(file);

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

        this.#webcamPreviews.addFile(file);
    }

    #selectCameras()
    {
        this.#webcam.detectVideoDevices().then(videoDevices =>
        {
            for(let webcam of videoDevices)
            {
                this.#domElements.addCameraToSelect(webcam.deviceId, webcam.label)
            }
        })
        .catch(err => {console.error('Fehler:', err);});
    }

    #disableActions()
    {
        this.#uploaderDialog.disableActions();
        this.enableUploadButton();
        document.getElementById("linkTab").disabled = true;
    }

    #enableActions()
    {
        this.#uploaderDialog.enableActions();
        this.disableUploadButton();
        document.getElementById("linkTab").disabled = false;
    }

}