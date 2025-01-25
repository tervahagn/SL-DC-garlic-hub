export class LocalFilesElements
{
	#startFileUpload = document.getElementById("startLocalFilesUpload");
	#dropzone        = document.getElementById("dropzone");
	#dropzonePreview = document.getElementById("dropzonePreview");
	#fileInput       = document.getElementById("fileInput");

	get startFileUpload()
	{
		return this.#startFileUpload;
	}

	get dropzone()
	{
		return this.#dropzone;
	}

	get dropzonePreview()
	{
		return this.#dropzonePreview;
	}

	get fileInput()
	{
		return this.#fileInput;
	}
}