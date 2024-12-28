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

export class ImagePreview extends AbstractPreview
{
    #allowedFormats = ["image/jpeg", "image/png", "image/gif", "image/webp", "image/svg+xml"];

    createPreview()
    {
        if (!this.#allowedFormats.includes(this.getFile().type))
            throw new Error(`\nUnsupported file format: ${this.getFile().type}`);

        const img = document.createElement("img");
        const reader = new FileReader();
        reader.onload = (e) => {
            img.src = e.target.result;
        };
        reader.readAsDataURL(this.getFile());
        return img;
    }
}
