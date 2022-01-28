<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\sessions;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

final class SessionManager implements Listener{

	/** @var array<string, Session> $sessions */
	private array $sessions = [];

	private function createSession(Player $player) : void{
		$this->sessions[$player->getName()] = new Session($player);
	}

	public function getSession(Player $player) : Session{
		return $this->sessions[$player->getName()];
	}

	private function destroySession(Player $player) : void{
		unset($this->sessions[$player->getName()]);
	}

	public function onJoin(PlayerJoinEvent $playerJoinEvent) : void{
		$this->createSession($playerJoinEvent->getPlayer());
	}

	public function onQuit(PlayerQuitEvent $playerQuitEvent) : void{
		$this->destroySession($playerQuitEvent->getPlayer());
	}

}