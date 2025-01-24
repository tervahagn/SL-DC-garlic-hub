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
    #webcamList = [];
    #streamList = [];
    #selectedDeviceId = '';
    #selectedDevice = null;
    #webcamSettings = null;

    constructor(webcamElement)
    {
        this.#webcamElement = webcamElement;
        this.#webcamElement.width = this.#webcamElement.width || 640;
        this.#webcamElement.height = this.#webcamElement.height || 360;
        this.#webcamList = [];
        this.#streamList = [];
        this.#selectedDeviceId = '';
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

    /* Get media constraints */
    getMediaConstraints()
    {
        let videoConstraints = {};

        videoConstraints.deviceId = {exact: this.#selectedDeviceId};
        videoConstraints.width    = {ideal: 3840};
        videoConstraints.height   = {ideal: 2160};

        return {video: videoConstraints, audio: false};
    }

    selectCamera(deviceid)
    {
        for(let webcam of this.#webcamList)
        {
            if (deviceid === webcam.deviceId)
            {
                this.#selectedDeviceId = webcam.deviceId;
                this.#selectedDevice   = webcam;
                return;
            }
        }
    }

    detectVideoDevices()
    {
        return new Promise(async (resolve, reject) => {
            try
            {
                await navigator.mediaDevices.getUserMedia({ video: true });
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.#webcamList = devices.filter(device => device.kind === 'videoinput');
                if(this.#webcamList.length > 0)
                    this.#selectedDeviceId = this.#webcamList[0].deviceId;

                resolve(this.#webcamList);
            }
            catch (err)
            {
                reject(err);
            }
        });
    }


    /* Start streaming webcam to video element */
    async start()
    {
        return new Promise((resolve, reject) => {
            navigator.mediaDevices.getUserMedia(this.getMediaConstraints())
                .then(stream => {
                    const track = stream.getVideoTracks()[0];
                    this.#webcamSettings = track.getSettings();

                    this.#streamList.push(stream);
                    this.#webcamElement.srcObject = stream;

                    if(this.#selectedDevice.label.toLowerCase().includes('front'))
                        this.#webcamElement.style.transform = "scale(-1,1)";

                    this.#webcamElement.play();
                    resolve(this.#selectedDeviceId);
                })
                .catch(error => {
                    console.log(error);
                    reject(error);
                });
        });
    }

    stop()
    {
        this.#streamList.forEach(stream => {
            stream.getTracks().forEach(track => {
                track.stop();
            });
        });
    }

    shootPhoto(canvasElement)
    {
        canvasElement.height = this.#webcamSettings.height;
        canvasElement.width = this.#webcamSettings.width;
        let context = canvasElement.getContext('2d');
        if(this.#selectedDevice.label.toLowerCase().includes('front'))
        {
            context.translate(canvasElement.width, 0);
            context.scale(-1, 1);
        }
        context.clearRect(0, 0, canvasElement.width, canvasElement.height);
        context.drawImage(this.#webcamElement, 0, 0, canvasElement.width, canvasElement.height);

        return canvasElement.toDataURL('image/jpeg', 0.8);
    }
}