/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

export class TreeViewElements
{
	#mediapoolTree    = document.getElementById("mediapoolTree");
	#currentPath      = document.getElementById("currentPath");
	#treeViewFilter   = document.getElementById("treeViewFilter");

	#editFolderDialog = document.getElementById("editFolderDialog");
	#closeEditDialog  = document.getElementById("closeEditDialog");
	#menuTemplate     = document.getElementById("treeViewContextMenuTemplate");

	get mediapoolTree()
	{
		return this.#mediapoolTree;
	}

	get currentPath()
	{
		return this.#currentPath;
	}

	get treeViewFilter()
	{
		return this.#treeViewFilter;
	}

	get editFolderDialog()
	{
		return this.#editFolderDialog;
	}

	get closeEditDialog()
	{
		return this.#closeEditDialog;
	}

	initialize()
	{
		this.#mediapoolTree    = document.getElementById("mediapoolTree");
		this.#currentPath      = document.getElementById("currentPath");
		this.#treeViewFilter   = document.getElementById("treeViewFilter");

		this.#editFolderDialog = document.getElementById("editFolderDialog");
		this.#closeEditDialog  = document.getElementById("closeEditDialog");
		this.#menuTemplate     = document.getElementById("treeViewContextMenuTemplate");
	}

	get menuTemplate()
	{
		return this.#menuTemplate;
	}
}