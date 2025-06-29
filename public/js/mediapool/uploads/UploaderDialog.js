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

export class UploaderDialog
{
	#directoryView  = null;
	#dialogElements = null;
    #disableEscape  = false;

    constructor(dialogElements, directoryView)
    {
		this.#dialogElements = dialogElements;
		this.#directoryView  = directoryView;

        this.#dialogElements.openUploadDialog.disabled  = true;
		this.#initEvents();
		this.#initTabSwitching();
		this.#dialogElements.uploaderDialog.addEventListener('close', () => {
			this.#directoryView.reloadCurrentNode();
		});
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && this.#disableEscape)
				event.preventDefault();
		});

	}

    enableActions()
    {
        this.#dialogElements.closeUploadDialog.disabled  = false;
        this.#dialogElements.closeDialog.disabled        = false;
        this.#disableEscape                              = false;
    }

    disableActions()
    {
		this.#dialogElements.closeUploadDialog.disabled  = true;
		this.#dialogElements.closeDialog.disabled        = true;
		this.#disableEscape                              = true;
    }

    #initEvents()
    {
        this.#dialogElements.openUploadDialog.addEventListener('click', () => this.#dialogElements.uploaderDialog.showModal());
        this.#dialogElements.closeDialog.addEventListener('click', () => this.#dialogElements.uploaderDialog.close());
        this.#dialogElements.closeUploadDialog.addEventListener("click", () => {this.#dialogElements.uploaderDialog.close();});
    }

    #initTabSwitching()
    {
		this.#dialogElements.tabButtons.forEach(button => {
            button.addEventListener('click', () => {

                // Deactivate all tabs
				this.#dialogElements.tabButtons.forEach(btn => btn.classList.remove('active'));
				this.#dialogElements.tabContents.forEach(content => content.classList.remove('active'));

                // Activate the selected tab
                button.classList.add('active');
				this.#dialogElements.getTargetTab(button.dataset.tab).classList.add('active');
            });
        });
    }
}

