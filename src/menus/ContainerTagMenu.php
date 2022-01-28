<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\menus;

use dktapps\pmforms\BaseForm;
use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Mcbeany\NBTEditor\NBTEditor;
use Mcbeany\NBTEditor\sessions\Mode;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\player\Player;
use function array_keys;
use function array_values;
use function count;
use function sprintf;
use function strval;
use function ucfirst;

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
			new MenuOption("Save"),
			new MenuOption("Add tag"),
			new MenuOption(sprintf(
				"Switch to %s mode",
				ucfirst($this->getSession()->otherMode()->name())),
			)
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
			case 2:
				$this->getSession()->insertPrevTag($this->getTag());
				(new AddTagMenu($this->getSession(), $this->getTag()))->send();
				break;
			case 3:
				$this->getSession()->switchMode();
				NBTEditor::openEditor($this->getSession(), $this->getTag());
				break;
			default:
				$response -= count($this->getElements()) - $this->getTag()->count();
				if($this->getSession()->getMode()->equals(Mode::REMOVE())){
					if($this->getTag() instanceof ListTag){
						$this->getTag()->remove($response);
					}else{
						$this->getTag()->removeTag(array_keys($this->getTag()->getValue())[$response]);
					}
					NBTEditor::openEditor($this->getSession(), $this->getTag());
					return;
				}
				$this->getSession()->insertPrevTag($this->getTag());
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