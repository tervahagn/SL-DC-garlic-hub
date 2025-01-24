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

import { AbstractPreview } from "./AbstractPreview.js";
import * as pdfjsLib from "/js/external/pdf.min.mjs";
pdfjsLib.GlobalWorkerOptions.workerSrc = "/js/external/pdf.worker.min.mjs";

export class PdfPreview extends AbstractPreview
{
    createPreview()
    {
        const canvas = this.createPreviewElement();
        const context = canvas.getContext("2d");

        const reader = new FileReader();
        reader.onload = async (e) => {
            const pdfData = new Uint8Array(e.target.result);
            const pdf     = await pdfjsLib.getDocument(pdfData).promise;

            const page     = await pdf.getPage(1); // Renders only first page
            const viewport = page.getViewport({ scale: 1 });

            canvas.width  = viewport.width;
            canvas.height = viewport.height;

            const renderContext = {
                canvasContext: context,
                viewport: viewport,
            };

            await page.render(renderContext).promise;
        };

        reader.readAsArrayBuffer(this.getFile());
        return canvas;
    }

    createPreviewElement()
    {
        return document.createElement("canvas");
    }
}
