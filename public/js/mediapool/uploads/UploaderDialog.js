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

class UploaderDialog
{
    #dialog = null;
    #openButton = null;
    #closeButton = null;
    #closeElement = null;

    constructor(dialog, openButton, closeButton, closeDialogButton)
    {
        this.#dialog       = dialog;
        this.#openButton   = openButton;
        this.#closeButton  = closeButton;
        this.#closeElement = closeDialogButton;

        this.#initEvents();
        this.#initTabSwitching();
    }

    #initEvents()
    {
        this.#openButton.addEventListener('click', () => this.#dialog.showModal());
        this.#closeButton.addEventListener('click', () => this.#dialog.close());
        this.#closeElement.addEventListener("click", () => {this.#dialog.close();});
    }

    #initTabSwitching()
    {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {

                // Deactivate all tabs
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Activate the selected tab
                button.classList.add('active');
                const targetTab = document.getElementById(button.dataset.tab);
                targetTab.classList.add('active');
            });
        });
    }
}

