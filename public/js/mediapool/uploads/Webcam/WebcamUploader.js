
export class WebcamUploader
{
    #maxRecordTime = 60;
    #webcam = null;
    #webcamElements = null;
    #directoryView = null;
    #fetchClient = null;
    #uploaderDialog = null;
    #mediaRecorder = null;
    #recordedChunks = [];
    constructor(webcam, webcamElements, directoryView, uploaderDialog, fetchClient)
    {
        this.#webcam         = webcam;
        this.#webcamElements = webcamElements;
        this.#directoryView  = directoryView;
        this.#uploaderDialog = uploaderDialog;
        this.#fetchClient    = fetchClient;
        this.#webcamElements.getToggleWebcam().addEventListener("input", () => this.#toggleWebcam());
        this.#webcamElements.getCapturePhotoButton().addEventListener("click", () => this.#shotPhoto());
        this.#webcamElements.getStartRecordingButton().addEventListener("click", (event) => this.#toggleRecording(event));

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
                const blob = new Blob(this.#recordedChunks, { type: 'video/webm' });
                // Use the Blob for further processing or display in the browser memory
                console.log('Recorded video blob:', blob);

                // Optional: Display the video in a temporary element (not saved)
                const videoURL = URL.createObjectURL(blob);
                const tempVideo = document.createElement('video');
                tempVideo.className = "preview-video";
                tempVideo.src = videoURL;
                tempVideo.controls = true;
                this.#webcamElements.getPreviewRecordsArea().appendChild(tempVideo);

            };
        }
        else
        {
            this.#mediaRecorder.stop();
            event.target.textContent = "Start Aufnahme";
            this.#mediaRecorder = null;
        }

    }

    #shotPhoto()
    {
        const tempImg = document.createElement('img');
        tempImg.className = "preview-video";
        tempImg.src = this.#webcam.snap(document.createElement("canvas"));
        this.#webcamElements.getPreviewRecordsArea().appendChild(tempImg);
    }
}