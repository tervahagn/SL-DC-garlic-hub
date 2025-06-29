/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

import { ImagePreview } from "./ImagePreview.js";
import { VideoPreview } from "./VideoPreview.js";
import { AudioPreview } from "./AudioPreview.js";
import { PdfPreview } from "./PdfPreview.js";
import { WidgetPreview } from "./WidgetPreview.js";
import {MiscellaneousPreview} from "./MiscellaneousPreview.js";

export class PreviewFactory
{
    create(file)
    {
        if (file.type.startsWith("image/"))
        {
            return new ImagePreview(file);
        }
        else if (file.type.startsWith("video/"))
        {
            return new VideoPreview(file);
        }
        else if (file.type.startsWith("audio/"))
        {
            return new AudioPreview(file);
        }
        else if (file.type === "application/pdf")
        {
            return new PdfPreview(file);
        }
        else if (file.type === "application/wgt" || file.type === "application/widget" || file.name.endsWith(".wgt"))
        {
            return new WidgetPreview(file);
        }
		else if (file.name.endsWith(".csv") || file.name.endsWith(".json") || file.name.endsWith(".xml") ||
			file.name.endsWith(".dmg") || file.name.endsWith(".rss") || file.name.endsWith(".atom") ||
			file.name.endsWith(".pkg") || file.name.endsWith(".apk") || file.name.endsWith(".zip") ||
			file.name.endsWith(".smil")
		)
        {
            return new MiscellaneousPreview(file);
        }
		else
			throw new Error("File type: " + file.type +" is not supported");
    }
}
