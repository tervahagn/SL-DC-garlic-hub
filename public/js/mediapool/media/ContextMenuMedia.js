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

export class ContextMenuMedia
{
    #menuElement = null;
    #mediaModel  = null;
    #treeDialog  = null;

    constructor(menuElement, nodesModel, treeDialog)
    {
        this.#menuElement = menuElement;
        this.#mediaModel  = nodesModel;
        this.#treeDialog  = treeDialog;
    }

    show(event)
    {
        document.querySelectorAll('.context_menu').forEach(el => el.remove());  // remove all previous menu
        this.#menuElement.style.left = `${event.pageX}px`;
        this.#menuElement.style.top = `${event.pageY}px`;
        document.body.appendChild(this.#menuElement);
        document.addEventListener('click', () => this.#menuElement.remove(), {once: true});
    }
/*
    addEditEvent(editNodeElement, currentTreeNode, lang)
    {
        editNodeElement.addEventListener("click", () => {
            this.#treeDialog.prepareShow("edit_folder", lang);
            this.#treeDialog.show();
        });
    }

    addRemoveEvent(removeNodeElement, currentTreeNode)
    {
        removeNodeElement.addEventListener("click", () => {
            (async () => {

                const apiUrl = "/async/mediapool/node";
                const dataToSend = {"node_id": currentTreeNode.key};
                const options = {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(dataToSend)
                }

                const result = await this.mediaModel.fetchData(apiUrl, options).catch(error => {
                    console.error('Fetch error:', error.message);
                    return null;
                });

                if (!result || !result.success) {
                    console.error('Error:', result?.error_message || 'Unknown error');
                    return;
                }

                currentTreeNode.remove();
            })();
        });
    }

 */
}