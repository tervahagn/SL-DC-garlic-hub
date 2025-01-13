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

export class MediaService
{
    fetchClient       = null;
    static MEDIALIST_URI = '/async/mediapool/media/';
    static MEDIAMOVE_URI = '/async/mediapool/media/move';
    static MEDIACOPY_URI = '/async/mediapool/media/copy';

    constructor(fetchClient)
    {
        this.fetchClient      = fetchClient;
    }

    async loadMedia(nodeId)
    {
        const url  = MediaService.MEDIALIST_URI + nodeId;
        const data = await this.fetchClient.fetchData(url);

        if (data.error)
            throw new Error(data.error_text);

        return data;
    }

    async moveMedia(mediaId, nodeId)
    {
        const url  = MediaService.MEDIAMOVE_URI;
        const dataToSend  = {"media_id": mediaId, "node_id": nodeId};
        const options= {method: "POST", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dataToSend)}
        const data = await this.fetchClient.fetchData(url, options);

        if (data.error)
            throw new Error(data.error_text);

        return data;
    }

    async copyMedia(mediaId)
    {
        const url  = MediaService.MEDIACOPY_URI;
        const dataToSend  = {"media_id": mediaId};
        const options= {method: "POST", headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dataToSend)}
        const data = await this.fetchClient.fetchData(url);

        if (data.error)
            throw new Error(data.error_text);

        return data;
    }

}
