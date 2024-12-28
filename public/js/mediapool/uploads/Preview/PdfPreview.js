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

/**
 * We are using pdf.js version 3.9 because from version 4.0 upwards it seems that an absurd 1 MB worker is required in addition.
 * For creating a thumbnail 3.9 with 312 KB is good enough.
 */
export class PdfPreview extends AbstractPreview
{

    createPreview()
    {
        const canvas = document.createElement("canvas");
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
}
