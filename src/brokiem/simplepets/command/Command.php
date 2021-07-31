<?php

declare(strict_types=1);

namespace brokiem\simplepets\command;

use brokiem\simplepets\SimplePets;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

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
                            $sender->sendMessage("Usage: /spet <petType> <petName>");
                        }
                    } else {
                        $sender->sendMessage("Only player can run this command");
                    }
                    break;
            }
        }
    }

    public function getOwningPlugin(): Plugin {
        return SimplePets::getInstance();
    }
}