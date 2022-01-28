<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\menus;

use dktapps\pmforms\BaseForm;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\CustomFormElement;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use Mcbeany\NBTEditor\NBTEditor;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use function array_map;
use function explode;
use function is_numeric;
use function trim;

class AddTagMenu extends BaseTagMenu{

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
				$this->getSession()->openPrevTag();
			}
		);
	}

	/**
	 * @return CustomFormElement[]
	 */
	protected function getElements() : array{
		/** @var callable $method */
		$method = [NBTEditor::class, 'getTagName'];
		$tags = array_map($method, NBTEditor::$constants);
		return [
			new Dropdown(
				"tag",
				"Select tag type",
				$tags
			),
			new Dropdown(
				"tagType",
				"If your tag is a list, you can include a tag type",
				$tags
			),
			new Input(
				"value",
				"Enter tag value. If your tag is an int array, you can enter a comma-separated list of values, otherwise if your tag is a compound or list, you can leave this blank"
			),
			new Input(
				"key",
				"Enter tag key. If your tag is a list, you can leave this blank"
			)
		];
	}

	/**
	 * @param CustomFormResponse $result
	 */
	protected function onResponse($result) : void{
		$tag = null;
		$value = $result->getString("value");
		$key = $result->getString("key");
		switch($result->getInt("tag")){
			case NBT::TAG_End:
				$this->getSession()->openPrevTag();
				return;
			case NBT::TAG_Byte:
				$tag = new ByteTag((int) $value);
				break;
			case NBT::TAG_ByteArray:
				$tag = new ByteArrayTag(is_numeric($value) ? $value : "0");
				break;
			case NBT::TAG_Compound:
				$tag = CompoundTag::create();
				break;
			case NBT::TAG_Double:
				$tag = new DoubleTag((float) $value);
				break;
			case NBT::TAG_Float:
				$tag = new FloatTag((float) $value);
				break;
			case NBT::TAG_Int:
				$tag = new IntTag((int) $value);
				break;
			case NBT::TAG_IntArray:
				$tag = new IntArrayTag(array_map(fn (string $v) : int => (int) trim($v), explode(",", $value)));
				break;
			case NBT::TAG_List:
				$tag = new ListTag([], $result->getInt("tagType"));
				break;
			case NBT::TAG_Long:
				$tag = new LongTag((int) $value);
				break;
			case NBT::TAG_Short:
				$tag = new ShortTag((int) $value);
				break;
			case NBT::TAG_String:
				$tag = new StringTag($value);
				break;
		}
		if($tag === null){
			$this->getSession()->reload();
			return;
		}
		if($this->getTag() instanceof CompoundTag){
			if($this->getTag()->getTag($key) === null){
				$this->getTag()->setTag($key, $tag);
			}
			$this->getSession()->openPrevTag();
			return;
		}
		$tagType = $this->getTag()->getTagType();
		if(!is_numeric($key) or ($tagType !== NBT::TAG_End and $tagType !== $tag->getType())){
			$this->getSession()->openPrevTag();
			return;
		}
		$this->getTag()->push($tag);
		$this->getSession()->openPrevTag();
	}

	public function getTag() : CompoundTag|ListTag{
		/** @var CompoundTag|ListTag $tag */
		$tag = parent::getTag();
		if(!ContainerTagMenu::isContainerTag($tag)){
			throw new \InvalidArgumentException("Tag must be a CompoundTag or ListTag");
		}
		return $tag;
	}

}