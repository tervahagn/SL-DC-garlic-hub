<?php
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


namespace App\Modules\Player\IndexCreation\Builder\Preparers;

class CategoriesPreparer extends AbstractPreparer implements PreparerInterface
{
	private string $smil;

	public function setSmil(string $smil): static
	{
		$this->smil = $smil;
		return $this;
	}

	public function prepare(): string
	{
		preg_match_all('@(?=begin_categories ).* @', $this->smil, $ar_match);

		// it is required to traverse every founded category comment
		// as we set to un greedy this will remove only one occurrence per round
		foreach ($ar_match[0] as $preg_matched)
		{
			$foundCategories = substr($preg_matched, 17, strlen($preg_matched) - 18);

			$ar_found   = explode(';', $foundCategories);
			$match      = false;

			foreach($this->playerEntity->getCategories() as $key => $and)
			{
				if (count(array_intersect($and, $ar_found)) == count($and))
				{
					$match = true;
					break;
				}
			}

			if ($match)
				$this->smil = $this->removeCategoriesCommentsOnly($foundCategories);
			else
				$this->smil = $this->removeCategoryBlock($foundCategories);
		}
		return $this->smil;
	}

	protected function removeCategoryBlock(string $foundCategories): string
	{
		return preg_replace('@<!-- begin_categories ' . $foundCategories . ' -->.*<!-- end_categories ' . $foundCategories . ' -->\n@isU', '', $this->smil);
	}

	private function removeCategoriesCommentsOnly(string $categories): string
	{
		return str_replace(array(
			'<!-- begin_categories ' . $categories . ' -->' . "\n",
			'<!-- end_categories ' . $categories . ' -->' . "\n"
		), '', $this->smil);
	}
}