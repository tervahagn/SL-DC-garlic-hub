export class ExternalFileElements
{
	#externalLinkField = document.getElementById("externalLinkField");
	#startFileUpload   = document.getElementById("startExternalFileUpload");

	get externalLinkField()
	{
		return this.#externalLinkField;
	}

	get startFileUpload()
	{
		return this.#startFileUpload;
	}
}