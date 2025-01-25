/*
 * SpicyCam: JavaScript Lib for easy Camera access
 *
 * SpicyCam is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * SpicyCam is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with [Project Name].  If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2025 Nikolaos Sagiadinos <niko@saghiadinos.de>
 */

/**
 * This class was inspired by Benson Ruan webcam-easy
 */
export class SpicyScreen
{
	#imageQuality    = 0.8
    #videoElement    = null;
    #camerasList     = [];
    #streamList      = [];
    #currentDeviceId = '';
    #settings        = null;
	#isMirror        = false;

    constructor(videoElement)
    {
        this.#videoElement        = videoElement;
        this.#videoElement.width  = this.#videoElement.width || 640;
        this.#videoElement.height = this.#videoElement.height || 360;
    }


    getMediaConstraints()
    {
        const videoConstraints    = {};

        videoConstraints.width    = {ideal: 3840};
        videoConstraints.height   = {ideal: 2160};

        return {video: videoConstraints, audio: false};
    }


    async startScreencast()
    {
		return new Promise((resolve, reject) => {
			navigator.mediaDevices.getDisplayMedia({ video: true, audio: true})
				.then(stream => {
					const track = stream.getVideoTracks()[0];

					this.#settings = track.getSettings();
					this.#streamList.push(stream);
					this.#videoElement.srcObject = stream;

					this.#videoElement.play();
					resolve(this.#currentDeviceId);
				})
				.catch(error => {
					console.log(error);
					reject(error);
				});
		});
	}

    stopScreencast()
    {
        this.#streamList.forEach(stream => {
            stream.getTracks().forEach(track => {
                track.stop();
            });
        });

		this.#videoElement.srcObject = null; // reset video element
    }

    capturePhoto(canvasElement)
    {
		return this.capturePhotoAsJpeg(canvasElement);
    }

	capturePhotoAsPng(canvasElement)
	{
		canvasElement = this.#handleCanvas(canvasElement);
		return canvasElement.toDataURL('image/png');
	}

	capturePhotoAsJpeg(canvasElement)
	{
		canvasElement = this.#handleCanvas(canvasElement);
		return canvasElement.toDataURL('image/jpeg', this.#imageQuality);
	}

	capturePhotoAsWebp(canvasElement)
	{
		canvasElement = this.#handleCanvas(canvasElement);
		return canvasElement.toDataURL('image/webp', this.#imageQuality);
	}

	#handleCanvas(canvasElement)
	{
		canvasElement.height = this.#settings.height;
		canvasElement.width = this.#settings.width;
		let context = canvasElement.getContext('2d');
		if(this.#isMirror)
		{
			context.translate(canvasElement.width, 0);
			context.scale(-1, 1);
		}
		context.clearRect(0, 0, canvasElement.width, canvasElement.height);
		context.drawImage(this.#videoElement, 0, 0, canvasElement.width, canvasElement.height);

		return canvasElement;
	}
}