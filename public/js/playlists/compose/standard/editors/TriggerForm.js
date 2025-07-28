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
	#triggerData = null;
	#addWallclock = null;
	#wallclocksContainer = null;
	#addAccesskey = null;
	#accesskeysContainer = null;
	#addTouch = null;
	#touchesContainer = null;
	#addNotify = null;
	#notifiesContainer = null;
	#registerEditors = null;

	constructor(triggertypeFactory, registerEditors)
	{
		this.#triggerTypeFactory = triggertypeFactory;
		this.#registerEditors = registerEditors;
	}

	init(triggerData)
	{
		this.#triggerData  = triggerData;
		this.#addWallclock = document.getElementById("addWallclock");
		this.#wallclocksContainer = document.getElementById("wallclocksContainer");
		this.#addWallclock.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("wallclock");
			editor.init();
			this.#wallclocksContainer.appendChild(editor.getEditor());
			this.#registerEditors.addWallclockEditor(editor);
		});
		this.#addAccesskey = document.getElementById("addAccesskey");
		this.#accesskeysContainer = document.getElementById("accesskeysContainer");
		this.#addAccesskey.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("accesskey");
			editor.init();
			this.#accesskeysContainer.appendChild(editor.getEditor());
			this.#registerEditors.addAccesskeyEditor(editor);
		});
		this.#addTouch     = document.getElementById("addTouch");
		this.#touchesContainer = document.getElementById("touchesContainer");
		this.#addTouch.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("touch");
			editor.init();
			this.#touchesContainer.appendChild(editor.getEditor());
			this.#registerEditors.addTouchEditor(editor);
		});
		this.#addNotify    = document.getElementById("addNotify");
		this.#notifiesContainer = document.getElementById("notifiesContainer");
		this.#addNotify.addEventListener("click", () => {
			const editor = this.#triggerTypeFactory.create("notify");
			editor.init();
			this.#notifiesContainer.appendChild(editor.getEditor());
			this.#registerEditors.addNotifyEditor(editor);
		});
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

		let wallclocks = this.collectWallclockValues();
		if (Object.keys(wallclocks).length > 0)
			triggers.wallclocks = wallclocks;

		let accesskeys = this.collectAccesskeyValues();
		if (Object.keys(accesskeys).length > 0)
			triggers.accesskeys = accesskeys;

		let touches = this.collectTouchValues();
		if (Object.keys(touches).length > 0)
			triggers.touches = touches;

		let notifies = this.#registerEditors.collectNotifyValues();
		if (Object.keys(notifies).length > 0)
			triggers.notifies = notifies;

		return triggers;
	}

	collectWallclockValues()
	{
		let wallclocks = document.getElementById("wallclocks").children;
		let ar_return = [];
		for (let i = 0; i < wallclocks.length; i++)
		{
			let ar = {}
			let wallclock_id        = getUnitIdFromAttrId(wallclocks[i].id);
			ar.iso_date  = document.getElementById("edit_datetime_" + wallclock_id).value;
			if (ar.iso_date === "")
			{
				document.getElementById("edit_datetime_" + wallclock_id).style.color = "red";
				this.has_save_errors = true;
			}
			else
			{
				document.getElementById("edit_datetime_" + wallclock_id).style.color = "black";
			}
			// datetime-local did not commit seconds when 0. Let's add it here to get a valid ISO-date.
			if (ar.iso_date.length < 17)
				ar.iso_date += ':00';

			let weekday             = document.getElementById("edit_weekday_" + wallclock_id).value;
			if (weekday === "0")
				ar.weekday = 0;
			else
			{
				let prefix       = document.getElementById("edit_weekday_prefix_" + wallclock_id).value;
				if (prefix === "-")
					ar.weekday = "-" + weekday;
				else
					ar.weekday = "+" + weekday;
			}
			let el = document.getElementsByName('select_repeats_' +wallclock_id);
			let repeat = "-1";
			for(let j = 0; j < el.length; j++)
			{
				if (el[j].checked)
					repeat = el[j].value
			}

			if (repeat > 0)
				ar.repeat_counts = document.getElementById('numbers_repeats_' + wallclock_id).value;
			else
				ar.repeat_counts = repeat;

			if (repeat === "-1")
			{
				ar.repeat_minutes = 0;
				ar.repeat_hours   = 0;
				ar.repeat_days    = 0;
				ar.repeat_weeks   = 0;
				ar.repeat_months  = 0;
				ar.repeat_years   = 0;
			}
			else
			{
				ar.repeat_minutes = document.getElementById('repeat_minutes_' + wallclock_id).value;
				ar.repeat_hours   = document.getElementById('repeat_hours_' + wallclock_id).value;
				ar.repeat_days    = document.getElementById('repeat_days_' + wallclock_id).value;
				ar.repeat_weeks   = document.getElementById('repeat_weeks_' + wallclock_id).value;
				ar.repeat_months  = document.getElementById('repeat_months_' + wallclock_id).value;
				ar.repeat_years   = document.getElementById('repeat_years_' + wallclock_id).value;
			}

			ar_return.push(ar);
		}

		return ar_return;
	}
	collectAccesskeyValues()
	{
		let accesskeys = document.getElementById("accesskeys").children;
		let ar_return = [];
		for (let i = 0; i < accesskeys.length; i++)
		{
			let ar = {}
			let accesskeys_id = getUnitIdFromAttrId(accesskeys[i].id);
			let accesskey = document.getElementById("edit_accesskey_" + accesskeys_id).value
			if (accesskey !== "") {
				ar.accesskey = accesskey;
				ar_return.push(ar);
			}
		}
		return ar_return;
	}

	collectTouchValues()
	{
		let touches = document.getElementById("touches").children;
		let ar_return = [];
		for (let i = 0; i < touches.length; i++)
		{
			let ar = {}
			let touches_id    = getUnitIdFromAttrId(touches[i].id);
			let touch_item_id = document.getElementById("edit_touch_item_" + touches_id).value
			if (touch_item_id !== "0")
			{
				ar.touch_item_id  = touch_item_id;
				ar_return.push(ar);
			}
		}

		return ar_return;
	}

}