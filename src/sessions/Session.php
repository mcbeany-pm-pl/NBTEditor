<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\sessions;

use Mcbeany\NBTEditor\NBTEditor;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use function array_pop;

class Session{

	const NONE = -1;

	private int $heldIndex = self::NONE;
	private ?CompoundTag $currTag = null;
	private CompoundTag|ListTag|null $parentTag;
	private int|string $editTagKey = self::NONE;
	/** @var array<CompoundTag|ListTag> $prevTags */
	private array $prevTags = [];

	public function __construct(
		private Player $player
	) {
	}

	public function getHeldIndex() : int{
		return $this->heldIndex;
	}

	public function setHeldIndex(int $heldIndex) : void{
		$this->heldIndex = $heldIndex;
	}

	public function getCurrTag() : ?CompoundTag{
		return $this->currTag;
	}

	public function setCurrTag(?CompoundTag $currTag) : void{
		$this->currTag = $currTag;
	}

	public function hasPrevTags() : bool{
		return !empty($this->prevTags);
	}

	public function insertPrevTag(CompoundTag|ListTag $prevTag) : void{
		$this->prevTags[] = $prevTag;
	}

	public function openPrevTag() : void{
		$prevTag = array_pop($this->prevTags);
		if($prevTag === null){
			return;
		}
		NBTEditor::openEditor($this, $prevTag);
	}

	public function getParentTag() : CompoundTag|ListTag|null{
		return $this->parentTag;
	}

	public function setParentTag(CompoundTag|ListTag|null $parentTag) : void{
		$this->parentTag = $parentTag;
	}

	public function getEditTagKey() : int|string{
		return $this->editTagKey;
	}

	public function setEditTagKey(int|string $editTagKey) : void{
		$this->editTagKey = $editTagKey;
	}

	public function reload() : void{
		$this->heldIndex = self::NONE;
		$this->currTag = null;
		$this->prevTags = [];
	}

	public function reloadEdit() : void{
		$this->parentTag = null;
		$this->editTagKey = self::NONE;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

}