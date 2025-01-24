
export class WebcamUploader
{
    #maxRecordTime = 60;
    #webcam = null;
    #webcamElements = null;
    #webcamPreviews = null;
    #directoryView = null;
    #fetchClient = null;
    #uploaderDialog = null;
    #mediaRecorder = null;
    #recordedChunks = [];

    constructor(webcam, webcamPreviews, webcamElements, directoryView, uploaderDialog, fetchClient)
    {
        this.#webcam         = webcam;
        this.#webcamPreviews = webcamPreviews;
        this.#webcamElements = webcamElements;
        this.#directoryView  = directoryView;
        this.#uploaderDialog = uploaderDialog;
        this.#fetchClient    = fetchClient;
        this.#webcamElements.getCapturePhotoButton().addEventListener("click", () => this.#shootPhoto());
        this.#webcamElements.getStartRecordingButton().addEventListener("click", (event) => this.#toggleRecording(event));
        this.#webcamElements.getSelectCamera().addEventListener("change", (event) => this.#selectWebcam(event));
        this.#webcamElements.getToggleCamera().addEventListener("click", (event) => this.#toggleWebcam(event));

        this.#selectCameras();
    }

    disableUploadButton()
    {
        this.#webcamElements.getStartWebcamUpload().disabled = true;
    }

    enableUploadButton()
    {
        this.#webcamElements.getStartWebcamUpload().disabled = false;
    }

    #selectWebcam(event)
    {
        const selectedValue = event.target.value;
        this.#webcam.selectCamera(selectedValue);
        if (selectedValue === "-")
            this.#webcamElements.getToggleCamera().disabled = true;
        else
            this.#webcamElements.getToggleCamera().disabled = false;

    }

    #toggleWebcam(event)
    {
        if(event.target.checked)
        {
            this.#webcam.start();
            this.#webcamElements.getSelectCamera().disabled = true;
            this.#webcamElements.getCapturePhotoButton().disabled = false;
            this.#webcamElements.getStartRecordingButton().disabled = false;

        }
        else
        {
            this.#webcam.stop();
            this.#webcamElements.getSelectCamera().disabled = false;
            this.#webcamElements.getCapturePhotoButton().disabled = true;
            this.#webcamElements.getStartRecordingButton().disabled = true;
        }
    }

    #toggleRecording(event)
    {
        if (this.#mediaRecorder === null || !this.#mediaRecorder.state === 'recording')
        {
            let stream = this.#webcamElements.getWebcamVideo().srcObject;
            this.#recordedChunks = [];
            this.#mediaRecorder = new MediaRecorder(stream);
            this.#mediaRecorder.start(1000); // 1000ms = 1 Sekunde pro Chunk
            let startTime = Date.now();
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

    #shootPhoto()
    {
        const dataURL = this.#webcam.shootPhoto(document.createElement("canvas"))

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


    #estimateImageSize(base64String)
    {
        const base64Data = base64String.split(',')[1]; // remove prefixes
        return Math.floor(base64Data.length * 3 / 4);
    }

    #selectCameras()
    {
        this.#webcam.detectVideoDevices().then(videoDevices =>
        {
            for(let webcam of videoDevices)
            {
                this.#webcamElements.addCameraToSelect(webcam.deviceId, webcam.label)
            }
        })
        .catch(err => {console.error('Fehler:', err);});


    }

}