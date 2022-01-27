<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor;

use Mcbeany\NBTEditor\menus\ContainerTagMenu;
use Mcbeany\NBTEditor\menus\ImmutableTagMenu;
use Mcbeany\NBTEditor\sessions\Session;
use Mcbeany\NBTEditor\sessions\SessionManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use function array_search;
use function preg_replace;
use function sprintf;
use function strlen;
use function strtolower;
use function substr;

class NBTEditor extends PluginBase{
	const NBTEDITOR = "NBTEditor";

	private SessionManager $sessionManager;
	/** @var array<string, mixed> $constants */
	// Should be array<string, int> but PHPStan :((
	private static array $constants;

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents(
			$this->sessionManager = new SessionManager,
			$this
		);
		self::$constants = (new \ReflectionClass(NBT::class))->getConstants();
	}

	public function getSessionManager() : SessionManager{
		return $this->sessionManager;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($command->getName() === strtolower(self::NBTEDITOR)){
			if($sender instanceof Player){
				if(!$sender->hasPermission("nbteditor.command")){
					return false;
				}
				$tag = $sender->getInventory()->getItemInHand()->nbtSerialize();
				$session = $this->getSessionManager()->getSession($sender);
				$session->setHeldIndex($sender->getInventory()->getHeldItemIndex());
				self::openEditor($session, $tag);
			}
		}
		return true;
	}

	public static function openEditor(Session $session, Tag $tag) : void{
		if(ContainerTagMenu::isContainerTag($tag)){
			/** @var CompoundTag|ListTag $tag */
			// Again PHPStan >:(
			if($session->getCurrTag() === null){
				if(!$tag instanceof CompoundTag){
					return;
				}
				$session->setCurrTag($tag);
			}
			(new ContainerTagMenu($session, $tag))->send();
			return;
		}
		(new ImmutableTagMenu($session, $tag))->send();
	}

	/**
	 * @return string E.g. "Compound"
	 */
	public static function getTagName(int $tagType) : string{
		$constant = array_search($tagType, self::$constants, true);
		if(!$constant){
			throw new \InvalidArgumentException("Invalid tag type $tagType");
		}
		return substr($constant, strlen("TAG_"));
	}

	/**
	 * @see "https://github.com/tryashtar/nbt-studio/blob/master/NbtStudio/Resources/wiki"
	 * @see https://stackoverflow.com/a/19533226
	 */
	public static function getTagIcon(string $tagName) : string{
		return sprintf(
			"https://github.com/tryashtar/nbt-studio/blob/master/NbtStudio/Resources/wiki/tag_%s.png?raw=true",
			strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $tagName) ?? "")
		);
	}

}
