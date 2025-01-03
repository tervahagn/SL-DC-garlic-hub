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
        const img = document.createElement("img");
        img.src = "./images/widget.png";

        // Asynchrone Verarbeitung starten
        this.processZip(img);

        return img;
    }


    async processZip(img)
    {
        const zip = new JSZip();

        try
        {
            const zipContent = await zip.loadAsync(this.getFile());

            // Überprüfen, ob die Datei "icon.png" existiert
            if (zipContent.files["icon.png"]) {
                const blob = await zipContent.files["icon.png"].async("blob");
                img.src = URL.createObjectURL(blob); // Bildquelle aktualisieren
            } else {
                console.warn("icon.png nicht gefunden. Platzhalter bleibt.");
            }
        } catch (error) {
            console.error("Fehler beim Verarbeiten der ZIP-Datei:", error.message);
        }
    }

}
