<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\command;

use brokiem\simplepets\entity\pets\base\BasePet;
use brokiem\simplepets\entity\pets\base\CustomPet;
use brokiem\simplepets\SimplePets;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;

class Command extends \pocketmine\command\Command implements PluginOwned {

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (isset($args[0])) {
            switch (strtolower($args[0])) {
                case "spawn":
                    if ($sender instanceof Player) {
                        if (isset($args[2])) {
                            SimplePets::getInstance()->getPetManager()->spawnPet($sender, $args[1], $args[2]);
                            $sender->sendMessage("Pet spawned!");
                        } else {
                            $sender->sendMessage("Usage: /spet spawn <petType> <petName>");
                        }
                    } else {
                        $sender->sendMessage("Only player can run this command");
                    }
                    break;
                case "remove":
                case "delete":
                    if ($sender instanceof Player) {
                        if (isset($args[1])) {
                            if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]])) {
                                $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]];
                                $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                    SimplePets::getInstance()->getPetManager()->despawnPet($pet);
                                }

                                SimplePets::getInstance()->getDatabaseManager()->removePet($sender, $args[1]);
                                $sender->sendMessage("Pet removed!");
                            } else {
                                $sender->sendMessage("Pet with name " . $args[1] . " not found");
                            }
                        } else {
                            $sender->sendMessage("Usage: /spet remove <petName>");
                        }
                    }
                    break;
            }
        }
    }

    public function getOwningPlugin(): Plugin {
        return SimplePets::getInstance();
    }
}