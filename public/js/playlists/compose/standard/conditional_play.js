let MyConditionalPlayForm;
class CreateConditionalPlayForm
{
	html_form = "";
	title = "";
	enable_conditional_play = null;
	edit_conditional_play   = null;
	enable_date_period      = null;
	edit_date_period        = null;
	date_from               = null;
	date_until              = null;
	enable_time_period      = null;
	edit_time_period        = null;
	time_from               = null;
	time_until              = null;
	constructor(html, title)
	{
		this.html_form = html
		this.title     = title;
	}
	handleConditionalPlayCheckBox()
	{
		if (this.enable_conditional_play.checked)
			this.edit_conditional_play.style.display = "block";
		else
		{
			this.enable_date_period.checked = false;
			this.handleDatePeriodCheckBox();
			this.enable_time_period.checked = false;
			this.handleTimePeriodCheckBox();
			this.edit_conditional_play.style.display = "none";
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
		this.enable_conditional_play = document.getElementById("enable_conditional_play");
		this.edit_conditional_play   = document.getElementById("edit_conditional_play");
		this.enable_conditional_play.onclick = () =>
		{
			this.handleConditionalPlayCheckBox();
		}
	}
	handleDatePeriodCheckBox()
	{
		if (this.enable_date_period.checked)
			this.edit_date_period.style.display = "block";
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
		this.date_from    = document.getElementById("date_from");
		this.date_until   = document.getElementById("date_until");
		this.enable_date_period.onclick = () =>
		{
			this.handleDatePeriodCheckBox();
		}
	}
	handleTimePeriodCheckBox()
	{
		if (this.enable_time_period.checked)
			this.edit_time_period.style.display = "block";
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
			this.handleTimePeriodCheckBox();
		}
	}
	initWeekDays()
	{
		$(function()
		{
			let Sunday = null;
			let Monday = null;
			let Tuesday = null;
			let Wednesday = null;
			let Thursday = null;
			let Friday = null;
			let Saturday = null;
			function setSliderRange(i, from, until)
			{
				document.getElementById("range1_from_"+i ).innerHTML = convertMinutesToTime(from * 15);
				document.getElementById("range1_until_"+i ).innerHTML = convertMinutesToTime(until * 15);
			}
			$( ".slider-range" ).slider({
				range: true,
				min: 0,
				max: 96,
				step:1,
				animate:true,
				values: [ 0, 96 ],
				create: function( event, ui )
				{
					let i = getUnitIdFromAttrId($(this).attr("id"), 1);
					switch (parseInt(i))
					{
						case 1:
							Sunday = $(this);
							break;
						case 2:
							Monday = $(this);
							break;
						case 4:
							Tuesday = $(this);
							break;
						case 8:
							Wednesday = $(this);
							break;
						case 16:
							Thursday = $(this);
							break;
						case 32:
							Friday = $(this);
							break;
						case 64:
							Saturday = $(this);
							break;
					}

					if (document.getElementById("enable_weekday_"+i).checked)
					{
						let from  = document.getElementById("hidden_range_from_"+i).value;
						let until = document.getElementById("hidden_range_until_"+i).value;
						$(this).slider("enable");
						$(this).slider("values" , 0, from);
						$(this).slider("values" , 1, until);
						setSliderRange(i, from, until);
					}
					else
					{
						$("#edit_weekday_"+i).prop("checked", false);
						document.getElementById("range1_from_"+i ).innerHTML = "";
						document.getElementById("range1_until_"+i ).innerHTML = "";
						$(this).slider("values" , 0, 0);
						$(this).slider("values" , 1, 96);
						$(this).slider("disable");
					}
				},
				slide: function( event, ui )
				{
					let slider_id = getUnitIdFromAttrId($(this).attr("id"), 1);
					setSliderRange(slider_id, ui.values[0], ui.values[1]);
				}
			});

			$( ".enable_weekdays" ).click(function()
			{
				let weekday_id = getUnitIdFromAttrId($(this).attr("id"), 1);

				let widget = null;
				// because JavaScript is an illogical unlearnable language made from idiots for idiots
				switch (parseInt(weekday_id))
				{
					case 1:
						widget = Sunday;
						break;
					case 2:
						widget = Monday;
						break;
					case 4:
						widget = Tuesday;
						break;
					case 8:
						widget = Wednesday;
						break;
					case 16:
						widget = Thursday;
						break;
					case 32:
						widget = Friday;
						break;
					case 64:
						widget = Saturday;
						break;
				}

				widget.slider("values" , 0 , 0);
				widget.slider("values" , 1 , 96);
				if (document.getElementById("enable_weekday_" + weekday_id).checked)
				{
					widget.slider("enable");
					document.getElementById("range1_from_" + weekday_id ).innerHTML = "00:00";
					document.getElementById("range1_until_" + weekday_id ).innerHTML = "00:00";
				}
				else
				{
					widget.slider("disable");
					document.getElementById("range1_from_" + weekday_id ).innerHTML = "";
					document.getElementById("range1_until_" + weekday_id ).innerHTML = "";
				}
			});
		});
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
		if (this.enable_conditional_play.checked)
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
		if (this.enable_conditional_play.checked)
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
	openOverlay(MyView)
	{
		MyView.setContentSize(600, 400);
		MyView.setContent(this.html_form);
		MyView.setTitle(this.title);
		MyView.open();

		this.initDatePeriod();
		this.handleDatePeriodCheckBox()
		this.initTimePeriod();
		this.handleTimePeriodCheckBox();
		this.initWeekDays();
		this.initConditionalPlay();
		this.handleConditionalPlayCheckBox()

		let save = document.getElementById("save");
		save.onclick  = () =>
		{
			let result = this.collectValues();
			ConditionalPlayEditor.saveValues(result);
		}
		let cancel = document.getElementById("cancel");
		cancel.onclick = function ()
		{
			MyView.close();
		}
	}

}

ConditionalPlayEditor = {

	item_id : 0,
	MyModalEdit : {},
	load: function (element)
	{
		item_id = getUnitIdFromAttrId(element.id, 1);

		this.retrieveTimerDialog();
	},
	retrieveTimerDialog: function()
	{
		try
		{
			let url = ThymianConfig.main_site + "?site=playlists_async_conditional_play"
				+ url_separator + "item_id=" + item_id + url_separator + "action=retrieve"
			;

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
			MyRequest.onload = function ()
			{
				if (MyRequest.status !== 200)
				{
					jThymian.printError(MyRequest.statusText);
				}
				else
				{
					let jsonResponse = JSON.parse(MyRequest.responseText);
					MyConditionalPlayForm = new CreateConditionalPlayForm(jsonResponse.html, jsonResponse.title);
					MyModalEdit = new TModalContainer('');

					MyConditionalPlayForm.openOverlay(MyModalEdit);
				}
			};
			MyRequest.onerror = function ()
			{
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname)
			};
			MyRequest.send(null);
		}
		catch (err)
		{
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	},
	saveValues: function(values)
	{
		try
		{
			let url = ThymianConfig.main_site + "?site=playlists_async_conditional_play"
				+ url_separator + "item_id=" + item_id + url_separator + "action=save" + values;

			let MyRequest = new XMLHttpRequest();
			MyRequest.open("GET", url, true);
			MyRequest.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			MyRequest.onload = function ()
			{
				if (MyRequest.status !== 200)
				{
					jThymian.printError(MyRequest.statusText);
				}
				else
				{
					let jsonResponse = JSON.parse(MyRequest.responseText);
					if (jsonResponse.success === false)
					{
						MyModalEdit.setErrorText(jsonResponse.message);
					}
					else
					{
						MyModalEdit.close();
						let icon = document.getElementById("conditional_play_id_" + item_id);
						if (MyConditionalPlayForm.enable_conditional_play.checked)
						{
							icon.classList.add("icon_values_setted");
						}
						else
						{
							icon.classList.remove("icon_values_setted");
						}
						jPlaylist.setSaveAlert();
					}
				}
			};
			MyRequest.onerror = function ()
			{
				jThymian.printError(MyRequest.statusText);
				ThymianLog.log(MyRequest.statusText, 0, window.location.pathname)
			};

			MyRequest.send(null);
		}
		catch (err)
		{
			ThymianLog.logException(err);
			jThymian.printError(err);
		}
	}
};
