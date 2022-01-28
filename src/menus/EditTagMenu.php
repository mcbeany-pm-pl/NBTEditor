<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\menus;

use dktapps\pmforms\BaseForm;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\CustomFormElement;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use Mcbeany\NBTEditor\NBTEditor;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use function sprintf;
use function strval;

class EditTagMenu extends BaseTagMenu{

	protected function getForm() : BaseForm{
		return new CustomForm(
			NBTEditor::NBTEDITOR,
			$this->getElements(),
			function(Player $player, CustomFormResponse $data) : void{
				if(!$this->isViewer($player)){
					return;
				}
				$this->onResponse($data);
			},
			function(Player $player) : void{
				if(!$this->isViewer($player)){
					return;
				}
				$this->getSession()->reloadEdit();
				$this->getSession()->openPrevTag();
			}
		);
	}

	/**
	 * @return CustomFormElement[]
	 */
	protected function getElements() : array{
		$parent = $this->getSession()->getParentTag();
		if($parent === null){
			return [];
		}
		return [
			new Label(
				"label",
				sprintf(
					"You are currently editing a %s Tag.\nIn key `%s` of a %s Tag",
					NBTEditor::getTagName($this->getTag()->getType()),
					(string) $this->getSession()->getEditTagKey(),
					NBTEditor::getTagName($parent->getType())
				)
			),
			new Input(
				"value",
				"Enter new tag's value:",
				"",
				strval($this->getTag()->getValue())
			)
		];
	}

	/**
	 * @param CustomFormResponse $result
	 */
	protected function onResponse($result) : void{
		$input = $result->getString("value");
		$value = match($this->getTag()->getType()){
			NBT::TAG_Byte, NBT::TAG_Int, NBT::TAG_Long, NBT::TAG_Short => (int) $input,
			NBT::TAG_Double, NBT::TAG_Float => (float) $input,
			default => $input
			// TODO: Byte array & int array
		};
		$parent = $this->getSession()->getParentTag();
		$method = "set";
		if($parent instanceof CompoundTag){
			$method .= NBTEditor::getTagName($this->getTag()->getType());
		}
		$parent->{$method}($this->getSession()->getEditTagKey(), $value);
		$this->getSession()->openPrevTag();
	}

}