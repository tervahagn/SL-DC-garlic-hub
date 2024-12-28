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

import { UploaderDialog } from "./UploaderDialog.js";
import { FilePreviews } from "./FilePreviews.js";
import { DragDropManager } from "./DragDropManager.js";

document.addEventListener("DOMContentLoaded", function()
{
    const uploaderDialog = new UploaderDialog(
        document.getElementById('uploaderDialog'),
        document.getElementById('openUploadDialog'),
        document.getElementById('closeDialog'),
        document.getElementById("closeUploadDialog")
    );
    uploaderDialog.init();

    const filePreviews = new FilePreviews(
        document.getElementById('dropzone-preview'),
    )
    const dragDropManager = new DragDropManager(
        document.getElementById('dropzone'),
        filePreviews
    );

//    const fileUploader = new FileUploader('#dragDropTab .upload-button', dragDropManager.fileList);


});

