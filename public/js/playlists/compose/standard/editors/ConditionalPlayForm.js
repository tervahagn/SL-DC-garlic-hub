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

export class ConditionalPlayForm
{
	#enable_conditional_play = null;
	#edit_conditional_play   = null;
	enable_date_period      = null;
	edit_date_period        = null;
	date_from               = null;
	date_until              = null;
	enable_time_period      = null;
	edit_time_period        = null;
	time_from               = null;
	time_until              = null;
	#conditionalPlaySliderFactory = null;


	constructor(conditionalPlaySliderFactory)
	{
		this.#conditionalPlaySliderFactory = conditionalPlaySliderFactory;
	}

	init()
	{
		this.#enable_conditional_play = document.getElementById("enable_conditional_play");
		this.#edit_conditional_play   = document.getElementById("edit_conditional_play");

		this.initDatePeriod();
		this.#handleDatePeriodCheckBox()
		this.initTimePeriod();
		this.#handleTimePeriodCheckBox();
		this.initWeekDays();
		this.initConditionalPlay();
		this.handleConditionalPlayCheckBox()
	}

	handleConditionalPlayCheckBox()
	{
		if (this.#enable_conditional_play.checked)
			this.#edit_conditional_play.style.display = "block";
		else
		{
			this.enable_date_period.checked = false;
			this.#handleDatePeriodCheckBox();
			this.enable_time_period.checked = false;
			this.#handleTimePeriodCheckBox();
			this.#edit_conditional_play.style.display = "none";
			let enable_weekdays = document.getElementsByClassName("enable_weekdays");
			for (let i = 0; i < enable_weekdays.length; i++)
			{
				enable_weekdays[i].checked = true;
				enable_weekdays[i].click();
			}
		}
	}
	initConditionalPlay()
	{
		this.#enable_conditional_play.onclick = () =>
		{
			this.handleConditionalPlayCheckBox();
		}
	}
	#handleDatePeriodCheckBox()
	{
		if (this.enable_date_period.checked)
			this.edit_date_period.style.display = "flex";
		else
		{
			this.date_from.value    = "";
			this.date_until.value   = "";
			this.edit_date_period.style.display = "none";
		}
	}
	initDatePeriod()
	{
		this.enable_date_period = document.getElementById("enable_date_period");
		this.edit_date_period   = document.getElementById("edit_date_period");
		this.date_from          = document.getElementById("date_from");
		this.date_until         = document.getElementById("date_until");
		this.enable_date_period.onclick = () =>
		{
			this.#handleDatePeriodCheckBox();
		}
	}

	#handleTimePeriodCheckBox()
	{
		if (this.enable_time_period.checked)
			this.edit_time_period.style.display = "flex";
		else
		{
			this.time_from.value    = "00:00";
			this.time_until.value   = "00:00";
			this.edit_time_period.style.display = "none";
		}
	}
	initTimePeriod()
	{
		this.enable_time_period = document.getElementById("enable_time_period");
		this.edit_time_period   = document.getElementById("edit_time_period");
		this.time_from    = document.getElementById("time_from");
		this.time_until   = document.getElementById("time_until");
		this.enable_time_period.onclick = () =>
		{
			this.#handleTimePeriodCheckBox();
		}
	}

	initWeekDays()
	{
		let sunday    = this.#createWeekdaySliderGroup(1);
		let monday    = this.#createWeekdaySliderGroup(2);
		let tuesday   = this.#createWeekdaySliderGroup(4);
		let wednesday = this.#createWeekdaySliderGroup(8);
		let thursday  = this.#createWeekdaySliderGroup(16);
		let friday    = this.#createWeekdaySliderGroup(32);
		let saturday  = this.#createWeekdaySliderGroup(64);
	}

	#createWeekdaySliderGroup(weekdayId, from = 0, until = 96)
	{
		return this.#conditionalPlaySliderFactory.create(weekdayId, from, until);
	}

	collectValues()
	{
		return this.getDateTimeFromHTML() + this.getWeekdaysFromHTML();
	}

	getDateTimeFromHTML()
	{
		let	date_from_val  = "0000-00-00";
		let	date_until_val = "0000-00-00";
		let	time_from_val  = "00:00";
		let	time_until_val = "00:00";
		if (this.#enable_conditional_play.checked)
		{
			if (this.enable_date_period.checked)
			{
				if (date_from.value !== "")
					date_from_val  = date_from.value;
				if (date_until.value !== "")
					date_until_val = date_until.value;
			}
			if (this.enable_time_period.checked)
			{
				time_from_val  = time_from.value;
				time_until_val = time_until.value;
			}
		}
		return url_separator + "date_from=" +date_from_val +
			url_separator + "date_until="+date_until_val +
			url_separator + "time_from="+time_from_val +
			url_separator + "time_until="+time_until_val;
	}

	getWeekdaysFromHTML()
	{
		let edit_weekdays   = 0;
		let weektimes_from  = "";
		let weektimes_until = "";
		if (this.#enable_conditional_play.checked)
		{
			$('#checkboxes_weekdays :checked').each(function ()
			{
				var i = parseInt($(this).val());
				edit_weekdays += i;
				weektimes_from += $("#slider_" + i).slider("values", 0) + "|";
				weektimes_until += $("#slider_" + i).slider("values", 1) + "|";
			});
		}
		return url_separator + "weekdays="	+ edit_weekdays +
			url_separator + "weektimes_from=" + weektimes_from.substr(0, weektimes_from.length-1) +
			url_separator + "weektimes_until=" + weektimes_until.substr(0, weektimes_until.length-1);
	}



}
