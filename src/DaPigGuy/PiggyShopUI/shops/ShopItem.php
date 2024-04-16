<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use DaPigGuy\PiggyShopUI\utils\Utils;
use pocketmine\item\Item;

class ShopItem
{
    public function __construct(public Item $item, public string $description, public bool $canBuy, public float $buyPrice, public bool $canSell, public float $sellPrice, public int $imageType, public string $imagePath)
    {
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function canBuy(): bool
    {
        return $this->canBuy;
    }

    public function setCanBuy(bool $canBuy): void
    {
        $this->canBuy = $canBuy;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getBuyPrice(): float
    {
        return $this->buyPrice;
    }

    public function setBuyPrice(float $buyPrice): void
    {
        $this->buyPrice = $buyPrice;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function canSell(): bool
    {
        return $this->canSell;
    }

    public function setCanSell(bool $canSell): void
    {
        $this->canSell = $canSell;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getSellPrice(): float
    {
        return $this->sellPrice;
    }

    public function setSellPrice(float $sellPrice): void
    {
        $this->sellPrice = $sellPrice;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getImageType(): int
    {
        return $this->imageType;
    }

    public function setImageType(int $imageType): void
    {
        $this->imageType = $imageType;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): void
    {
        $this->imagePath = $imagePath;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function serialize(): array{
        return [
            "item" => Utils::jsonSerialize($this->item),
            "description" => $this->description, 
            "canBuy" => $this->canBuy, 
            "buyPrice" => $this->buyPrice, 
            "canSell" => $this->canSell, 
            "sellPrice" => $this->sellPrice, 
            "imageType" => $this->imageType, 
            "imagePath" => $this->imagePath];
    }
}