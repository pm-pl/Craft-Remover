<?php

namespace Valres\CraftRemover;

use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use ReflectionClass;
use ReflectionException;

class Main extends PluginBase
{
    private array $removed = [];

    /** @throws ReflectionException */
    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $config = $this->getConfig();

        foreach($config->get("remove-craft") as $identifier){
            $item = StringToItemParser::getInstance()->parse($identifier);
            if($item instanceof Item){
                $this->removed[] = $item->getTypeId();
            }
        }

        $craftManager = Server::getInstance()->getCraftingManager();
        $reflectionClass = new ReflectionClass($craftManager);

        $recipes = $craftManager->getCraftingRecipeIndex();
        $newRecipes = [];

        foreach($recipes as $recipe){
            $valid = true;

            if($recipe instanceof ShapedRecipe || $recipe instanceof ShapelessRecipe){
                foreach($recipe->getResults() as $item){
                    if(in_array($item->getTypeId(), $this->removed)) $valid = false;
                }
            }

            if ($valid) $newRecipes[] = $recipe;
        }

        $property = $reflectionClass->getProperty("craftingRecipeIndex");
        $property->setAccessible(true);
        $property->setValue($craftManager, $newRecipes);
        $property->setAccessible(false);
    }
}
