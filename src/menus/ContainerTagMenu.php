<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\menus;

use dktapps\pmforms\BaseForm;
use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Mcbeany\NBTEditor\NBTEditor;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\player\Player;
use function array_keys;
use function array_values;
use function count;
use function sprintf;
use function strval;

class ContainerTagMenu extends BaseTagMenu{

	protected function getForm() : BaseForm{
		return new MenuForm(
			NBTEditor::NBTEDITOR,
			"Select a tag to edit",
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
		$buttons = [
			new MenuOption($this->getSession()->hasPrevTags() ? "Back" : "Exit"),
			new MenuOption("Save")
		];
		foreach($this->getTag() as $key => $tagValue){
			$value = $tagValue->getValue();
			$tagValueName = NBTEditor::getTagName($tagValue->getType());
			if($tagValue instanceof \Countable){
				$type = "Compound";
				if($tagValue instanceof ListTag){
					$type = NBTEditor::getTagName($tagValue->getTagType());
				}
				$value = sprintf(
					"%s[%d]",
					$type,
					$tagValue->count()
				);
			}
			$buttons[] = new MenuOption(
				sprintf(
					"%s (%s)\nValue: %s",
					$key,
					$tagValueName,
					strval($value)
				),
				new FormIcon(NBTEditor::getTagIcon($tagValueName))
			);
		}
		return $buttons;
	}

	/**
	 * @param int $response
	 */
	protected function onResponse($response) : void{
		switch($response){
			case 0:
				if($this->getSession()->hasPrevTags()){
					$this->getSession()->openPrevTag();
					return;
				}
				$this->getSession()->reload();
				break;
			case 1:
				$this->getSession()->insertPrevTag($this->getTag());
				$currTag = $this->getSession()->getCurrTag();
				if($currTag === null){
					$this->getSession()->reload();
					return;
				}
				(new SaveTagMenu($this->getSession(), $currTag))->send();
				break;
			default:
				$this->getSession()->insertPrevTag($this->getTag());
				$response -= count($this->getElements()) - $this->getTag()->count();
				$selectedTag = array_values($this->getTag()->getValue())[$response];
				if(!self::isContainerTag($selectedTag)){
					$this->getSession()->setParentTag($this->getTag());
					$this->getSession()->setEditTagKey(array_keys($this->getTag()->getValue())[$response]);
				}
				NBTEditor::openEditor($this->getSession(), $selectedTag);
				break;
		}
	}

	public function getTag() : CompoundTag|ListTag{
		/** @var CompoundTag|ListTag $tag */
		$tag = parent::getTag();
		if(!self::isContainerTag($tag)){
			throw new \InvalidArgumentException("Tag must be a CompoundTag or ListTag");
		}
		return $tag;
	}

	public static function isContainerTag(Tag $tag) : bool{
		return $tag instanceof CompoundTag or $tag instanceof ListTag;
	}

}