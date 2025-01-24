
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
        this.#webcamElements.getToggleWebcam().addEventListener("input", () => this.#toggleWebcam());
        this.#webcamElements.getCapturePhotoButton().addEventListener("click", () => this.#shootPhoto());
        this.#webcamElements.getStartRecordingButton().addEventListener("click", (event) => this.#toggleRecording(event));
    }

    disableUploadButton()
    {
        this.#webcamElements.getStartWebcamUpload().disabled = true;
    }

    enableUploadButton()
    {
        this.#webcamElements.getStartWebcamUpload().disabled = false;
    }

    #toggleWebcam()
    {
       if(this.#webcamElements.getToggleWebcam().checked)
           this.#webcam.start();
       else
          this.#webcam.stop();
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
        const dataURL = this.#webcam.snap(document.createElement("canvas"))

        const [header, base64] = dataURL.split(',');
        const mimeType = header.match(/:(.*?);/)[1]; // MIME-Typ extrahieren

        // Base64 zu Binär dekodieren und Länge berechnen
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

}