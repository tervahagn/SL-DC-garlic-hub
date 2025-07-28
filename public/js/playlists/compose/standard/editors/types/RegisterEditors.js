/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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
'use strict';

export class RegisterEditors
{
	#wallclocksEditors = [];
	#accesskeysEditors = [];
	#touchesEditors = [];
	#notifyEditors = [];

	addWallclockEditor(editor)
	{
		this.#wallclocksEditors.push(editor);
	}

	addAccesskeyEditor(editor)
	{
		this.#accesskeysEditors.push(editor);
	}

	addTouchEditor(editor)
	{
		this.#touchesEditors.push(editor);
	}

	collectTouchValues()
	{
		let values = [];
		for (let i = 0; i < this.#touchesEditors.length; i++)
		{
			if (this.#touchesEditors[i].exists)
			{
				let ar = {}
				ar.touch_item_id  = this.#touchesEditors[i].getValues();
				values.push(ar);
			}

		}

		return values;
	}

	addNotifyEditor(editor)
	{
		this.#notifyEditors.push(editor);
	}

	collectNotifyValues()
	{
		let values = [];
		for (let i = 0; i < this.#notifyEditors.length; i++)
		{
			if (this.#notifyEditors[i].exists)
			{
				let ar = {}
				ar.notify = this.#notifyEditors[i].getValues
				values.push(ar);
			}
		}
		return values;
	}
}
