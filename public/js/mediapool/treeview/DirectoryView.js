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

import { ContextMenu } from "./ContextMenu.js";

export class DirectoryView
{
    #tree = null;
    #tree_element = null;
    static DEBUG_LEVEL= 3;
    static SOURCE_URI   = '/async/mediapool/node/0';
    static LAZYLOAD_URI =  '/async/mediapool/node/';

    constructor(tree_element, current_path)
    {
        this.#tree_element = tree_element;
        this.#tree = new mar10.Wunderbaum({
            debugLevel: DirectoryView.DEBUG_LEVEL,
            element: this.#tree_element,
            source: { url: DirectoryView.SOURCE_URI },
            selectMode: "single",
            lazyLoad: function (e){
                return { url:DirectoryView.LAZYLOAD_URI + e.node.key, params: { parentKey: e.node.key } };
             },
            activate: (e) => {current_path.innerText = "/" + e.node.getPath()},
            filter: {autoApply: true, mode: "hide"},
        });
    }

    addFilter(tree_filter)
    {
        tree_filter.addEventListener("input", (event) => {
            this.#tree.filterNodes(event.target.value, { mode: "hide" });
        })
    }

    addContextMenu(nodesModel, treeDialog, lang)
    {
        this.#tree_element.addEventListener("contextmenu", (event) => {
            event.preventDefault();
            const currentTreeNode = this.setActiveNodeFromEventTarget(event.target);

            const menu = document.querySelector('#context_menu_tree').content.cloneNode(true).firstElementChild;
            let contextMenu    = new ContextMenu(menu, nodesModel, treeDialog);
            contextMenu.show(event);

            const editNodeElement = document.getElementById("edit_node");
            contextMenu.addEditEvent(editNodeElement, currentTreeNode, lang);

            const addNodeElement = document.getElementById("add_node");
            contextMenu.addAddEvent(addNodeElement, currentTreeNode, lang);

            const removeNodeElement = document.getElementById("remove_node");
            contextMenu.addRemoveEvent(removeNodeElement, currentTreeNode);
        });
    }

    setActiveNodeFromEventTarget(event_target)
    {
        // getNode is static for some reason
        const node = mar10.Wunderbaum.getNode(event_target);
        node.setActive(true);

        return node;
    }

    addRootChild(key, folder_name)
    {
        this.#tree.addChildren({ key:  key, title: folder_name, isFolder: true });
    }

    addSubChild(currentNode, key, folder_name)
    {
        currentNode.addChildren({ key:  key, title: folder_name, isFolder: true });
    }

}