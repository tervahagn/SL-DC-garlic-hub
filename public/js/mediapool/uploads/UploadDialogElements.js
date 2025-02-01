/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

export class UploadDialogElements
{
	static 	#openUploadDialog  = document.getElementById('openUploadDialog');

	#uploaderDialog    = document.getElementById('uploaderDialog');
	#closeDialog       = document.getElementById('closeDialog');
	#closeUploadDialog = document.getElementById("closeUploadDialog");
	#tabButtons        = document.querySelectorAll('.tab-button');
	#tabContents       = document.querySelectorAll('.tab-content');

	get uploaderDialog() { return this.#uploaderDialog; }

	static get openUploadDialog() {return this.#openUploadDialog; }

	get openUploadDialog() { return UploadDialogElements.#openUploadDialog; }

	get closeDialog() { return this.#closeDialog; }

	get closeUploadDialog() { return this.#closeUploadDialog; }

	get tabButtons() { return this.#tabButtons; }

	get tabContents() {	return this.#tabContents; }

	getTargetTab(tab)
	{
		return document.getElementById(tab);
	}
}