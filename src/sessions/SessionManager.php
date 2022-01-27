<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\sessions;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

final class SessionManager implements Listener{

	/** @var array<int, Session> $sessions */
	private array $sessions = [];

	private function createSession(Player $player) : void{
		$this->sessions[$player->getId()] = new Session($player);
	}

	public function getSession(Player $player) : Session{
		return $this->sessions[$player->getId()];
	}

	private function destroySession(Player $player) : void{
		unset($this->sessions[$player->getId()]);
	}

	public function onJoin(PlayerJoinEvent $playerJoinEvent) : void{
		$this->createSession($playerJoinEvent->getPlayer());
	}

	public function onQuit(PlayerQuitEvent $playerQuitEvent) : void{
		$this->destroySession($playerQuitEvent->getPlayer());
	}

}