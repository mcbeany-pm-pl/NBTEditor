<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\menus;

use dktapps\pmforms\BaseForm;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Mcbeany\NBTEditor\NBTEditor;
use pocketmine\item\Item;
use pocketmine\nbt\NbtException;
use pocketmine\player\Player;

class SaveTagMenu extends BaseTagMenu{

	protected function getForm() : BaseForm{
		return new MenuForm(
			NBTEditor::NBTEDITOR,
			"How do you want to save the tag?",
			$this->getElements(),
			function(Player $player, int $selectedOption) : void{
				if(!$this->isViewer($player)){
					return;
				}
				$this->onResponse($selectedOption);
			},
			function(Player $player) : void{
				if(!$this->isViewer($player)){
					return;
				}
				$this->getSession()->reload();
			}
		);
	}

	/**
	 * @return MenuOption[]
	 */
	protected function getElements() : array{
		return [
			new MenuOption("Save to hand"),
			new MenuOption("Save as copy"),
			new MenuOption("Back")
		];
	}

	/**
	 * @param int $result
	 */
	protected function onResponse($result) : void{
		$player = $this->getSession()->getPlayer();
		try{
			$currTag = $this->getSession()->getCurrTag();
			if($currTag === null){
				$this->getSession()->reload();
				return;
			}
			$item = Item::nbtDeserialize($currTag);
			switch($result){
				case 0:
					$player->getInventory()->setHeldItemIndex($this->getSession()->getHeldIndex());
					$player->getInventory()->setItemInHand($item);
					$this->getSession()->reload();
					break;
				case 1:
					if(!empty($notFit = $player->getInventory()->addItem($item))){
						$player->dropItem(...$notFit);
					}
					$this->getSession()->reload();
					break;
				case 2:
					$this->getSession()->openPrevTag();
					break;
			}
		}catch(NbtException $e){
			$player->sendMessage($e->getMessage());
		}
	}

}
