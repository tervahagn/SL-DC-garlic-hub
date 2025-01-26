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

export class WidgetPreview extends AbstractPreview
{
    createPreview()
    {
        const img = this.createPreviewElement();
        this.processZip(img);
        return img;
    }


    async processZip(img)
    {
        const zip = new JSZip();
        try
        {
            const zipContent = await zip.loadAsync(this.getFile());

            const targetFile = zipContent.files["icon.png"]
                ? "icon.png" : zipContent.files["icon.jpg"]
                    ? "icon.jpg" : null;

            if (targetFile)
            {
                const blob = await zipContent.files[targetFile].async("blob");
                img.src = URL.createObjectURL(blob); // Bildquelle aktualisieren
            }
        }
        catch (error)
        {
            console.error("Error in zip-process:", error.message);
        }
    }

    createPreviewElement()
    {
        const img = document.createElement("img");
        img.src = "/images/icons/widget.svg";

        return img;
    }
}
