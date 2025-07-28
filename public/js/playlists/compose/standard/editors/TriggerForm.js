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

export class TriggerForm
{
	#triggerTypeFactory = null;
	#addWallclock = null;
	#wallclocksContainer = null;
	#addAccesskey = null;
	#accesskeysContainer = null;
	#addTouch = null;
	#touchesContainer = null;
	#addNotify = null;
	#notifiesContainer = null;
	#editorsManager = null;

	constructor(triggertypeFactory, editorsManager)
	{
		this.#triggerTypeFactory = triggertypeFactory;
		this.#editorsManager = editorsManager;
	}

	init(triggerData)
	{
		this.#editorsManager.triggerData  = triggerData;
		this.#addWallclock = document.getElementById("addWallclock");
		this.#wallclocksContainer = document.getElementById("wallclocksContainer");
		this.#addWallclock.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("wallclock");
			const html   = this.#editorsManager.registerWallclockEditor(editor);
			this.#wallclocksContainer.appendChild(html);
		});

		this.#addAccesskey = document.getElementById("addAccesskey");
		this.#accesskeysContainer = document.getElementById("accesskeysContainer");
		this.#addAccesskey.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("accesskey");
			const html   = this.#editorsManager.addAccesskeyEditor(editor);
			this.#accesskeysContainer.appendChild(html);
		});

		this.#addTouch     = document.getElementById("addTouch");
		this.#touchesContainer = document.getElementById("touchesContainer");
		this.#addTouch.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("touch");
			const html   = this.#editorsManager.addTouchEditor(editor);
			this.#touchesContainer.appendChild(html);
		});

		this.#addNotify    = document.getElementById("addNotify");
		this.#notifiesContainer = document.getElementById("notifiesContainer");
		this.#addNotify.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("notify");
			const html = this.#editorsManager.addNotifyEditor(editor);
			this.#notifiesContainer.appendChild(html);
		});

		// init existing from data
		if (triggerData.hasOwnProperty("notifies"))
		{
			for (let i = 0; i < triggerData.notifies.length; i++)
			{
				const editor = this.#triggerTypeFactory.create("notify");
				const html = this.#editorsManager.addNotifyEditor(editor);
				this.#notifiesContainer.appendChild(html);
			}
		}
	}

	initDateTimeTriggerFunctions(item_id)
	{
		document.getElementById("edit_weekday_" + item_id).onchange = function ()
		{
			if (document.getElementById("edit_weekday_" + item_id).value !== "0")
				document.getElementById("edit_weekday_prefix_" + item_id).style.visibility = "visible";
			else
				document.getElementById("edit_weekday_prefix_" + item_id).style.visibility = "hidden";
		}
		document.getElementById("remove_wallclock_" + item_id).onclick = function ()
		{
			document.getElementById("wallclock_" + item_id).remove();
		}
		document.getElementById("select_no_repeats_" + item_id).onclick = function ()
		{
			document.getElementById("numbers_repeats_" + item_id).style.visibility = "hidden";
			document.getElementById("repeats_" + item_id).style.visibility = "hidden";
		}
		document.getElementById("select_indefinite_repeats_" + item_id).onclick = function ()
		{
			document.getElementById("numbers_repeats_" + item_id).style.visibility = "hidden";
			document.getElementById("repeats_" + item_id).style.visibility = "visible";
		}
		document.getElementById("select_number_repeats_" + item_id).onclick = function ()
		{
			document.getElementById("numbers_repeats_" + item_id).style.visibility = "visible";
			document.getElementById("repeats_" + item_id).style.visibility = "visible";
		}

	}

	initFunctionsGlobals()
	{
		let edit_weekday = document.getElementsByClassName("edit_weekday");
		for (let i = 0; i < edit_weekday.length; i++)
		{
			this.initDateTimeTriggerFunctions(getUnitIdFromAttrId(edit_weekday[i].id, 1));
		}
		let edit_accesskey = document.getElementsByClassName("edit_accesskey");
		for (let i = 0; i < edit_accesskey.length; i++)
		{
			this.initAccessKeyTriggerFunctions(getUnitIdFromAttrId(edit_accesskey[i].id, 1));
		}
		let edit_touch_item = document.getElementsByClassName("edit_touch_item");
		for (let i = 0; i < edit_touch_item.length; i++)
		{
			this.initTouchTriggerFunctions(getUnitIdFromAttrId(edit_touch_item[i].id, 1));
		}
		let edit_notify = document.getElementsByClassName("edit_notify");
		for (let i = 0; i < edit_notify.length; i++)
		{
			this.initNotifyTriggerFunctions(getUnitIdFromAttrId(edit_notify[i].id, 1));
		}
	}

	collectValues()
	{
		let triggers = {}

		let wallclocks = this.#editorsManager.collectWallclockValues();
		if (Object.keys(wallclocks).length > 0)
			triggers.wallclocks = wallclocks;

		let accesskeys = this.#editorsManager.collectAccesskeyValues();
		if (Object.keys(accesskeys).length > 0)
			triggers.accesskeys = accesskeys;

		let touches = this.#editorsManager.collectTouchValues();
		if (Object.keys(touches).length > 0)
			triggers.touches = touches;

		let notifies = this.#editorsManager.collectNotifyValues();
		if (Object.keys(notifies).length > 0)
			triggers.notifies = notifies;

		return triggers;
	}
}