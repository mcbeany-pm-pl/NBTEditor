<?php

declare(strict_types=1);

namespace Mcbeany\NBTEditor\sessions;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static Mode EDIT()
 * @method static Mode REMOVE()
 */

final class Mode {
	use EnumTrait;

	protected static function setup() : void{
		self::registerAll(
			new self("edit"),
			new self("remove")
		);
	}

}