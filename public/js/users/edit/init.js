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
"use strict";
document.addEventListener("DOMContentLoaded", function()
{
	const copy = document.getElementsByClassName("copy-verification-link");
	for (let i = 0; i < copy.length; i++)
	{
		copy[i].addEventListener('click', () =>
		{
			const id = copy[i].dataset.id;
			const inputField = document.getElementById(id);
			navigator.clipboard.writeText(inputField.value)
				.then(() => {
					const saveColor = inputField.style.backgroundColor;
					inputField.style.backgroundColor = "lightgreen";
					setTimeout(() => {
						inputField.style.backgroundColor = saveColor;
					}, 100);
				})
				.catch(err => {
					copy[i].style.backgroundColor = "red";
				});
		});
	}
});