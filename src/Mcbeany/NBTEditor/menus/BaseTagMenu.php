<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\menus;

use dktapps\pmforms\BaseForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\CustomFormElement;
use dktapps\pmforms\MenuOption;
use Mcbeany\NBTEditor\sessions\Session;
use pocketmine\nbt\tag\Tag;
use pocketmine\player\Player;

abstract class BaseTagMenu{

	public function __construct(
		private Session $session,
		private Tag $tag
	) {
	}

	abstract protected function getForm() : BaseForm;
	/**
	 * @return MenuOption[]|CustomFormElement[]
	 */
	abstract protected function getElements() : array;
	/**
	 * @param int|CustomFormResponse $result
	 */
	abstract protected function onResponse($result) : void;

	public function isViewer(Player $player) : bool{
		return $player->getUniqueId()->equals($this->getSession()->getPlayer()->getUniqueId());
	}

	public function send() : void{
		$this->getSession()->getPlayer()->sendForm($this->getForm());
	}

	public function getSession() : Session{
		return $this->session;
	}

	public function getTag() : Tag{
		return $this->tag;
	}

}