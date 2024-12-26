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
class TreeDialog
{
    #dialogElement = null;
    #closeElement = null;
    #currentNode  = 0;
    #action       = "";
    #directoryView = null;
    #nodesModel = null;

    constructor(dialog_element, close_element, directoryView, nodesModel)
    {
        this.#dialogElement = dialog_element;
        this.#closeElement  = close_element;
        this.#directoryView  = directoryView;
        this.#nodesModel     = nodesModel;

        this.#addCancelEvent();
        this.#addSaveEvent();
    }

    setCurrentNode(node)
    {
        this.#currentNode = node;
    }


    prepareShow(action, lang)
    {
        this.#action = action;
        this.#dialogElement.querySelector("legend").textContent = lang[action];


    }

    show()
    {
        switch (this.#action) {
            case "add_root_folder":
            case "add_sub_folder":
            case "delete_folder":
                document.getElementById("folder_name").value = "";
                break;
            case "edit_folder":
                document.getElementById("folder_name").value = this.#currentNode.title;
                break;
            default:
                throw new Error("Unknown action for show");
        }

        this.#dialogElement.showModal();
    }

    #addSaveEvent()
    {
        // also for closing the dialog with the cancel button
        this.#dialogElement.addEventListener('close', () => {
            if (this.#dialogElement.returnValue !== "submit")
               return;

            (async () => {
                const method     = this.#determineMethod();
                const apiUrl     = '/async/mediapool/node';
                const dataToSend = this.#determineDataToSend();
                const options    = {method: method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dataToSend)}

                const result = await this.#nodesModel.fetchData(apiUrl, options).catch(error => {
                    console.error('Fetch error:', error.message);
                    return null;
                });

                if (!result || !result.success)
                {
                    console.error('Error:', result?.error_message || 'Unknown error');
                    return;
                }

                switch (this.#action) {
                    case "add_root_folder":
                        this.#directoryView.addChildren(result.data.id, result.data.new_name);
                        break;
                    case "add_sub_folder":
                        break;
                    case "edit_folder":
                        this.#currentNode.setTitle(result.data.new_name);
                        break;
                }

            })();
        });
    }

    #determineMethod()
    {
        if (this.#currentNode  === 0)
            throw new Error("no node selected");

        switch (this.#action) {
            case "add_root_folder":
            case "add_sub_folder":
                return "POST";
            case "edit_folder":
                return "PATCH";
            case "delete_folder":
                return "DELETE";
            default:
                throw new Error("Unknown action");
        }

    }

    #determineDataToSend()
    {
        if (this.#currentNode  === 0)
            throw new Error("no node selected");

        switch (this.#action) {
            case "add_root_folder":
                return {"node_id": 0, "name": document.getElementById("folder_name").value};
            case "add_sub_folder":
                return {"node_id": this.#currentNode.key, "name": document.getElementById("folder_name").value};
            case "edit_folder":
                return {"node_id": this.#currentNode.key, "name": document.getElementById("folder_name").value};
            case "delete_folder":
                return {"node_id": this.#currentNode.key };
            default:
                throw new Error("Unknown action");
        }
    }


    #addCancelEvent()
    {
        this.#closeElement.addEventListener("click", () => {
            this.#dialogElement.close("cancel");
        });
    }

}