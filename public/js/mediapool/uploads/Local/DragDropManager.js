/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

export class DragDropManager
{
    #dropzone    = null;
    #filePreview = null;
    #fileInput = null;

    constructor(dropzone, filePreview, fileInput)
    {
        this.#dropzone    = dropzone;
        this.#filePreview = filePreview;
        this.#fileInput   = fileInput;
    }

    init()
    {
        this.#dropzone.addEventListener('dragover', (e) => this.onDragOver(e));
        this.#dropzone.addEventListener('dragleave', () => this.onDragLeave());
        this.#dropzone.addEventListener('drop', (e) => this.onDrop(e));
        this.#dropzone.addEventListener('click', () => fileInput.click());
        this.#fileInput.addEventListener('change', (event) => {
            const files = event.target.files;
            this.#filePreview.handleFiles(files);
        });


    }

    onDragOver(event)
    {
        event.preventDefault();
        this.#dropzone.style.borderColor = "#007bff";
    }

    onDragLeave()
    {
        this.#dropzone.style.borderColor = "#ccc";
    }

    onDrop(event)
    {
        event.preventDefault();
        this.#dropzone.style.borderColor = "#ccc";
        const files =  Array.from(event.dataTransfer.files);
        this.#filePreview.handleFiles(files);
    }
}