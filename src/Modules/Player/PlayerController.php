<?php
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

namespace App\Modules\Player;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
	#[Route('/player', name: 'player')]
	public function Player(): Response
	{
		return $this->render('dummy.html.twig', [
			'DUMMYHEADER' => 'Player Overview',
			'DUMMYTEXT' => 'Welcome to the Player overview! ',
		]);
	}

	#[Route('/player/edit', name: 'player_edit')]
	public function editPlayer(): Response
	{
		return $this->render('dummy.html.twig', [
			'DUMMYHEADER' => 'Player Edit',
			'DUMMYTEXT' => 'Welcome to the Player edit! ',
		]);
	}
}