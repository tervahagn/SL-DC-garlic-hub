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

import {BaseTypes} from "./BaseTypes.js";

export class NotifyEdit extends BaseTypes
{
	#valueField = null;

	init(data)
	{
		this.cloneNode("notifyTemplate");
		this.addRemoveListener();

		this.#valueField = this.node.querySelector(".edit-notify");
		this.#valueField.addEventListener("keypress", (evt) =>
		{
			if (/[a-zA-Z0-9]/i.test(evt.key) === false)
				evt.preventDefault()
		});

		if (data.hasOwnProperty("notify"))
			this.#valueField.value = data.notify;
	}



	getValues()
	{
		let obj = {}
		obj.notify = this.#valueField.value;

		return obj;
	}

}
