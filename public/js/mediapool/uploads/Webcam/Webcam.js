/*
MIT License

Copyright (c) 2020 Benson Ruan
webcam-easy.js  edited 2025 by Nikolaos Sagiadinos

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

export class Webcam
{
    #webcamElement = null;
    #facingMode = "user";
    #webcamList = [];
    #streamList = [];
    #selectedDeviceId = '';
    #webcamSettings = null;

    constructor(webcamElement, facingMode = 'user')
    {
        this.#webcamElement = webcamElement;
        this.#webcamElement.width = this.#webcamElement.width || 640;
        this.#webcamElement.height = this.#webcamElement.height || 360;
        this.#facingMode = facingMode;
        this.#webcamList = [];
        this.#streamList = [];
        this.#selectedDeviceId = '';
    }

    get facingMode()
    {
        return this.#facingMode;
    }

    set facingMode(value)
    {
        this.#facingMode = value;
    }

    get webcamList()
    {
        return this.#webcamList;
    }

    get webcamCount()
    {
        return this.#webcamList.length;
    }

    get selectedDeviceId()
    {
        return this.#selectedDeviceId;
    }

    /* Get all video input devices info */
    getVideoInputs(mediaDevices)
    {
        this.#webcamList = [];
        mediaDevices.forEach(mediaDevice => {
            if (mediaDevice.kind === 'videoinput')
            {
                this.#webcamList.push(mediaDevice);
            }
        });
        if(this.#webcamList.length === 1)
        {
            this.#facingMode = 'user';
        }
        return this.#webcamList;
    }

    /* Get media constraints */
    getMediaConstraints()
    {
        let videoConstraints = {};

        if (this.#selectedDeviceId === '')
            videoConstraints.facingMode =  this.#facingMode;
         else
            videoConstraints.deviceId = { exact: this.#selectedDeviceId};

        videoConstraints.width  = {ideal: 3840};
        videoConstraints.height = {ideal: 2160};

        return {
            video: videoConstraints,
            audio: false
        };
    }

    /* Select camera based on facingMode */
    selectCamera()
    {
        for(let webcam of this.#webcamList)
        {
            if(   (this.#facingMode ==='user' && webcam.label.toLowerCase().includes('front'))
                ||  (this.#facingMode ==='environment' && webcam.label.toLowerCase().includes('back'))
            )
            {
                this.#selectedDeviceId = webcam.deviceId;
                break;
            }
        }
    }

    /* Change Facing mode and selected camera */
    flip()
    {
        this.#facingMode = (this.#facingMode === 'user')? 'environment': 'user';
        this.#webcamElement.style.transform = "";
        this.selectCamera();
    }

    /*
      1. Get permission from user
      2. Get all video input devices info
      3. Select camera based on facingMode
      4. Start stream
    */
    async start(startStream = true) {
        return new Promise((resolve, reject) => {
            this.stop();
            navigator.mediaDevices.getUserMedia(this.getMediaConstraints()) //get permisson from user
                .then(stream => {
                    const track = stream.getVideoTracks()[0];
                    this.#webcamSettings = track.getSettings();
                    track.stop;
                    this.#streamList.push(stream);
                    this.info() //get all video input devices info
                        .then(webcams =>{
                            this.selectCamera();   //select camera based on facingMode
                            if(startStream){
                                this.stream()
                                    .then(facingMode =>{
                                        resolve(this.#facingMode);
                                    })
                                    .catch(error => {
                                        reject(error);
                                    });
                            }else{
                                resolve(this.#selectedDeviceId);
                            }
                        })
                        .catch(error => {
                            reject(error);
                        });
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    /* Get all video input devices info */
    async info(){
        return new Promise((resolve, reject) => {
            navigator.mediaDevices.enumerateDevices()
                .then(devices =>{
                    this.getVideoInputs(devices);
                    resolve(this.#webcamList);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    /* Start streaming webcam to video element */
    async stream()
    {
        return new Promise((resolve, reject) => {
            navigator.mediaDevices.getUserMedia(this.getMediaConstraints())
                .then(stream => {
                    this.#streamList.push(stream);
                    this.#webcamElement.srcObject = stream;
                    if(this.#facingMode === 'user')
                    {
                        this.#webcamElement.style.transform = "scale(-1,1)";
                    }
                    this.#webcamElement.play();
                    resolve(this.#facingMode);
                })
                .catch(error => {
                    console.log(error);
                    reject(error);
                });
        });
    }

    /* Stop streaming webcam */
    stop()
    {
        this.#streamList.forEach(stream => {
            stream.getTracks().forEach(track => {
                track.stop();
            });
        });
    }

    snap(canvasElement)
    {
        canvasElement.height = this.#webcamSettings.height;
        canvasElement.width = this.#webcamSettings.width;
        let context = canvasElement.getContext('2d');
        if(this.#facingMode === 'user')
        {
            context.translate(canvasElement.width, 0);
            context.scale(-1, 1);
        }
        context.clearRect(0, 0, canvasElement.width, canvasElement.height);
        context.drawImage(this.#webcamElement, 0, 0, canvasElement.width, canvasElement.height);

        return canvasElement.toDataURL('image/jpeg', 0.8);
    }
}