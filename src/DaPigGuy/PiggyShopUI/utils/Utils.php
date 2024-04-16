<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\utils;

use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\TextFormat;
use ReflectionClass;

class Utils
{
    private static array $replacements;

    public static function init(): void {
        foreach ((new ReflectionClass(TextFormat::class))->getConstants() as $color => $code) {
            if (is_string($code)) self::$replacements["{" . $color . "}"] = $code;
        }
    }

    public static function translateColorTags(string $message): string
    {
        return str_replace(array_keys(self::$replacements), self::$replacements, $message);
    }

    /**
     * by fernanACM
     * @param Item $item
     * @return array
     */
    public static function jsonSerialize(Item $item): array{
        $itemData["id"] = base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($item->nbtSerialize())));
        return $itemData;
	}

    /**
     * by fernanACM
     * @param array $data
     * @return Item
     */
    public static function legacyStringJsonDeserialize(array $data): Item{
        $data = base64_decode($data["id"]);
        $item = (new LittleEndianNbtSerializer())->read($data);
        return Item::nbtDeserialize($item->mustGetCompoundTag());
	}
}