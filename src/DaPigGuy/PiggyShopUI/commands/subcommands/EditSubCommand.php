<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\commands\subcommands;

use DaPigGuy\PiggyShopUI\libs\CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyShopUI\PiggyShopUI;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\shops\ShopItem;
use DaPigGuy\PiggyShopUI\shops\ShopSubcategory;
use DaPigGuy\PiggyShopUI\libs\Vecnavium\FormsUI\SimpleForm;
use DaPigGuy\PiggyShopUI\libs\Vecnavium\FormsUI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class EditSubCommand extends BaseSubCommand{

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
            return;
        }
        $this->showMainPage($sender);
    }

    public function showMainPage(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $this->showAddCategoryPage($player);
                        break;
                    case 1:
                        $this->showEditCategoriesPage($player);
                        break;
                    case 2:
                        $this->showRemoveCategoryPage($player);
                        break;
                }
            }
        });
        $form->setTitle("Manage Shop Categories");
        $form->addButton("Add Category");
        $form->addButton("Edit Category");
        $form->addButton("Remove Category");
        $player->sendForm($form);
    }

    public function showAddCategoryPage(Player $player): void
    {
        $form = new CustomForm(function (Player $player, ?array $data): void {
            if ($data !== null) {
                if (PiggyShopUI::getInstance()->getShopCategory($data[0]) !== null) {
                    $player->sendMessage(TextFormat::RED . "A shop category already exists with the name " . $data[0] . ".");
                    return;
                }
                if (strtolower($data[0]) === "edit") {
                    $player->sendMessage(TextFormat::RED . "'" . $data[0] . "' is an invalid shop category name.");
                    return;
                }
                PiggyShopUI::getInstance()->addShopCategory(new ShopCategory($data[0], [], [], $data[1], $data[2] - 1, $data[3]));
                $player->sendMessage(TextFormat::GREEN . "Shop category " . $data[0] . " created successfully.");
            }
            $this->showMainPage($player);
        });
        $form->setTitle("Add Shop Category");
        $form->addInput("Name");
        $form->addToggle("Private", false);
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"]);
        $form->addInput("Image Path/URL", "");
        $player->sendForm($form);
    }

    public function showEditCategoriesPage(Player $player): void
    {
        $categories = PiggyShopUI::getInstance()->getShopCategories();
        $form = new SimpleForm(function (Player $player, ?int $data) use ($categories): void {
            if ($data !== null) {
                if ($data === count($categories)) {
                    $this->showMainPage($player);
                    return;
                }
                $this->showEditCategoryPage($player, $categories[array_keys($categories)[$data]]);
            }
        });
        $form->setTitle("Edit Shop Categories");
        foreach ($categories as $category) {
            $form->addButton($category->getName(), $category->getImageType(), $category->getImagePath());
        }
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function showEditCategoryPage(Player $player, ShopCategory $category): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data) use ($category): void {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $this->showAddCategoryItemPage($player, $category);
                        break;
                    case 1:
                        $this->showEditCategoryItemsPage($player, $category);
                        break;
                    case 2:
                        $this->showRemoveCategoryItemPage($player, $category);
                        break;
                    case 3:
                        $this->showAddCategorySubcategoryPage($player, $category);
                        break;
                    case 4:
                        $this->showEditCategorySubcategoriesPage($player, $category);
                        break;
                    case 5:
                        $this->showRemoveCategorySubcategoryPage($player, $category);
                        break;
                    case 6:
                        $this->showEditCategorySettingsPage($player, $category);
                        break;
                    case 7:
                        if ($category instanceof ShopSubcategory) {
                            $this->showEditCategorySubcategoriesPage($player, $category->getParent());
                            break;
                        }
                        $this->showEditCategoriesPage($player);
                        break;
                }
            }
        });
        $form->setTitle("Edit '" . $category->getName() . "' Category");
        $form->addButton("Add Item");
        $form->addButton("Edit Item");
        $form->addButton("Remove Item");
        $form->addButton("Add Subcategory");
        $form->addButton("Edit Subcategory");
        $form->addButton("Remove Subcategory");
        $form->addButton("Edit Settings");
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function showAddCategoryItemPage(Player $player, ShopCategory $category): void
    {
        $items = array_values($player->getInventory()->getContents());
        $form = new CustomForm(function (Player $player, ?array $data) use ($items, $category): void {
            if ($data !== null) {
                if (!is_numeric($data[5]) || !is_numeric($data[8])) {
                    $player->sendMessage(TextFormat::RED . "Prices must be numeric.");
                    return;
                }
                if (!$data[3] && !$data[6]) {
                    $player->sendMessage(TextFormat::RED . "Item must not be both unpurchasable and unsellable.");
                    return;
                }
                $shopItem = new ShopItem($items[$data[0]], $data[2], $data[3], (float)$data[5], $data[6], (float)$data[8] ?: 0, $data[9] - 1, $data[10]);
                $category->addItem($shopItem);
                $player->sendMessage(TextFormat::GREEN . "Item successfully added.");
            }
            $this->showEditCategoryPage($player, $category);
        });
        $form->setTitle("Add '" . $category->getName() . "' Category Item");
        $form->addDropdown("Item", array_map(function (Item $item): string {
            return $item->getName();
        }, $items));
        $form->addLabel("Item descriptions are optional.");
        $form->addInput("Description");
        $form->addToggle("Can Buy", true);
        $form->addLabel("Do not change 'Buy Price' if 'Can Buy' is disabled.");
        $form->addInput("Buy Price", "", "0");
        $form->addToggle("Can Sell", false);
        $form->addLabel("Do not change 'Sell Price' if 'Can Sell' is disabled.");
        $form->addInput("Sell Price", "", "0");
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"]);
        $form->addInput("Image Path/URL", "");
        $player->sendForm($form);
    }

    public function showEditCategoryItemsPage(Player $player, ShopCategory $category): void
    {
        $items = $category->getItems();
        if (count($items) === 0) {
            $player->sendMessage(TextFormat::RED . "No items exist within this category.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($category, $items): void {
            if ($data !== null) {
                if ($data === count($items)) {
                    $this->showEditCategoryPage($player, $category);
                    return;
                }
                $this->showEditCategoryItemPage($player, $category, $items[array_keys($items)[$data]]);
            }
        });
        $form->setTitle("Edit '" . $category->getName() . "' Category Items");
        foreach ($items as $item) {
            $form->addButton($item->getItem()->getName(), $item->getImageType(), $item->getImagePath());
        }
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function showEditCategoryItemPage(Player $player, ShopCategory $category, ShopItem $item): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($category, $item): void {
            if ($data !== null) {
                if (!is_numeric($data[2]) || !is_numeric($data[4])) {
                    $player->sendMessage(TextFormat::RED . "Prices must be numeric.");
                    return;
                }
                if (!$data[1] && !$data[3]) {
                    $player->sendMessage(TextFormat::RED . "Item must not be both unpurchasable and unsellable.");
                    return;
                }
                $item->setDescription($data[0]);
                $item->setCanBuy($data[1]);
                $item->setBuyPrice((float)$data[2]);
                $item->setCanSell($data[3]);
                $item->setSellPrice((float)$data[4]);
                $item->setImageType($data[5] - 1);
                $item->setImagePath($data[6]);
                $player->sendMessage(TextFormat::GREEN . "Item updated successfully.");
            }
            $this->showEditCategoryPage($player, $category);
        });
        $form->setTitle("Edit Item '" . $item->getItem()->getName() . "'");
        $form->addInput("Description", "", $item->getDescription());
        $form->addToggle("Can Buy", $item->canBuy());
        $form->addInput("Buy Price", "", (string)$item->getBuyPrice());
        $form->addToggle("Can Sell", $item->canSell());
        $form->addInput("Sell Price", "", (string)$item->getSellPrice());
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"], $item->getImageType() + 1);
        $form->addInput("Image Path/URL", "", $item->getImagePath());
        $player->sendForm($form);
    }

    public function showRemoveCategoryItemPage(Player $player, ShopCategory $category): void
    {
        $items = array_values($category->getItems());
        $form = new CustomForm(function (Player $player, ?array $data) use ($category): void {
            if ($data !== null) {
                $category->removeItem($data[0]);
                $player->sendMessage(TextFormat::GREEN . "Item successfully removed.");
            }
            $this->showEditCategoryPage($player, $category);
        });
        $form->setTitle("Remove '" . $category->getName() . "' Category Item");
        $form->addDropdown("Item", array_map(function (ShopItem $item): string {
            return $item->getItem()->getName();
        }, $items));
        $player->sendForm($form);
    }

    public function showAddCategorySubcategoryPage(Player $player, ShopCategory $category): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($category): void {
            if ($data !== null) {
                if (PiggyShopUI::getInstance()->getShopCategory($data[0]) !== null) {
                    $player->sendMessage(TextFormat::RED . "A subcategory already exists with the name " . $data[0] . ".");
                    return;
                }
                $subcategory = new ShopSubcategory($data[0], [], [], $data[1], $data[2] - 1, $data[3]);
                $subcategory->setParent($category);
                $category->addSubCategory($subcategory);
                $player->sendMessage(TextFormat::GREEN . "Subcategory " . $data[0] . " created successfully.");
            }
            $this->showEditCategoryPage($player, $category);
        });
        $form->setTitle("Add Shop Subcategory");
        $form->addInput("Name");
        $form->addToggle("Private", false);
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"]);
        $form->addInput("Image Path/URL", "");
        $player->sendForm($form);
    }

    public function showEditCategorySubcategoriesPage(Player $player, ShopCategory $category): void
    {
        $subcategories = $category->getSubCategories();
        if (count($subcategories) === 0) {
            $player->sendMessage(TextFormat::RED . "No subcategories exist within this category.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($category, $subcategories): void {
            if ($data !== null) {
                if ($data === count($subcategories)) {
                    $this->showEditCategoryPage($player, $category);
                    return;
                }
                $this->showEditCategoryPage($player, $subcategories[array_keys($subcategories)[$data]]);
                return;
            }
            $this->showEditCategoryPage($player, $category);
        });
        $form->setTitle("Edit '" . $category->getName() . "' Category Subcategories");
        foreach ($subcategories as $subcategory) {
            $form->addButton($subcategory->getName(), $subcategory->getImageType(), $subcategory->getImagePath());
        }
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function showRemoveCategorySubcategoryPage(Player $player, ShopCategory $category): void
    {
        $subcategories = array_values($category->getSubCategories());
        $form = new CustomForm(function (Player $player, ?array $data) use ($category): void {
            if ($data !== null) {
                $category->removeSubCategory($data[0]);
                $player->sendMessage(TextFormat::GREEN . "Subcategory successfully removed.");
            }
            $this->showEditCategoryPage($player, $category);
        });
        $form->setTitle("Remove '" . $category->getName() . "' Category Subcategory");
        $form->addDropdown("Subcategory", array_map(function (ShopCategory $subcategory): string {
            return $subcategory->getName();
        }, $subcategories));
        $player->sendForm($form);
    }

    public function showEditCategorySettingsPage(Player $player, ShopCategory $category): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($category): void {
            if ($data !== null) {
                if ($category->getName() !== $data[0]) {
                    if (PiggyShopUI::getInstance()->getShopCategory($data[0]) !== null) {
                        $player->sendMessage(TextFormat::RED . "Could not rename. A shop category already exists with the name.");
                    } else {
                        $category->setName($data[0]);
                        $player->sendMessage(TextFormat::GREEN . "Category renamed to '" . $category->getName() . "'");
                    }
                }
                if ($category->isPrivate() !== $data[1]) {
                    $player->sendMessage(($data[1] ? TextFormat::GREEN : TextFormat::RED) . "Category is no" . ($data[1] ? "w private." : " longer private."));
                }
                $category->setPrivate($data[1]);
                if ($category->getImageType() !== $data[2] - 1 || $category->getImagePath() !== $data[3]) {
                    $player->sendMessage(TextFormat::GREEN . "Category image updated.");
                }
                $category->setImageType($data[2] - 1);
                $category->setImagePath($data[3]);
            }
            $this->showEditCategoryPage($player, $category);
        });
        $form->setTitle("'" . $category->getName() . "' Category Settings");
        $form->addInput("Name", "", $category->getName());
        $form->addToggle("Private", $category->isPrivate());
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"], $category->getImageType() + 1);
        $form->addInput("Image Path/URL", "", $category->getImagePath());
        $player->sendForm($form);
    }

    public function showRemoveCategoryPage(Player $player): void
    {
        $categories = array_values(PiggyShopUI::getInstance()->getShopCategories());
        $form = new CustomForm(function (Player $player, ?array $data) use ($categories): void {
            if ($data !== null) {
                $category = $categories[$data[0]];
                PiggyShopUI::getInstance()->removeShopCategory($category);
                $player->sendMessage(TextFormat::GREEN . "Shop category " . $category->getName() . " removed successfully.");
            }
            $this->showMainPage($player);
        });
        $form->setTitle("Remove Shop Category");
        $form->addDropdown("Category", array_map(function (ShopCategory $category): string {
            return $category->getName();
        }, $categories));
        $player->sendForm($form);

    }

    protected function prepare(): void{
        $this->setPermission("piggyshopui.command.shop.edit");
    }
}