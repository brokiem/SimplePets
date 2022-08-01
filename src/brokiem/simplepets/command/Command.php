<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\command;

use brokiem\simplepets\pets\base\BasePet;
use brokiem\simplepets\pets\base\CustomPet;
use brokiem\simplepets\SimplePets;
use EasyUI\element\Button;
use EasyUI\element\Dropdown;
use EasyUI\element\Input;
use EasyUI\element\Option;
use EasyUI\utils\FormResponse;
use EasyUI\variant\CustomForm;
use EasyUI\variant\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Command extends \pocketmine\command\Command implements PluginOwned {

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (isset($args[0])) {
            switch (strtolower($args[0])) {
                case "ui":
                    if (!($sender instanceof Player)) {
                        return;
                    }

                    if (!$sender->hasPermission("simplepets.ui")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    $form = new SimpleForm("Spawn Pet");

                    foreach (SimplePets::getInstance()->getPetManager()->getRegisteredPets() as $type => $class) {
                        $form->addButton(new Button($type, null, function(Player $player) use ($type) {
                            $dropdown = new Dropdown("Pet type");
                            $dropdown->addOption(new Option($type, $type));

                            $form = new CustomForm("Spawn $type");
                            $form->addElement("pet_type", $dropdown);
                            $form->addElement("pet_name", new Input("Pet name"));

                            $form->setSubmitListener(function(Player $player, FormResponse $response) {
                                $pet_name = $response->getInputSubmittedText("pet_name") ?? $player->getName();
                                $pet_type = $response->getDropdownSubmittedOptionId("pet_type");

                                if ($pet_type === null) {
                                    return;
                                }

                                if (!Player::isValidUserName($pet_name)) {
                                    $player->sendMessage("Invalid pet name. Only supported [A-z] with max 16 length character;");
                                    return;
                                }

                                if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()])) {
                                    foreach (SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()] as $petName => $petId) {
                                        $pet = $player->getServer()->getWorldManager()->findEntity($petId);

                                        if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                            $pet->despawn();
                                            SimplePets::getInstance()->getPetManager()->removeActivePet($player, $petName);
                                        }
                                    }
                                }

                                SimplePets::getInstance()->getPetManager()->spawnPet($player, $pet_type, $pet_name);

                                $player->sendMessage("§b" . str_replace("Pet", " Pet", $pet_type) . " §awith the name §b" . $pet_name . " §ahas been successfully spawned");
                            });

                            $player->sendForm($form);
                        }));
                    }

                    $sender->sendForm($form);
                    break;
                case "spawn":
                    if (!$sender->hasPermission("simplepets.spawn")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    if (isset($args[1], $args[2])) {
                        $player = Server::getInstance()->getPlayerByPrefix($args[1]);

                        if ($player === null) {
                            $sender->sendMessage("§cPlayer with name $args[1] doesnt exists");
                            return;
                        }

                        if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()][$args[2]])) {
                            $sender->sendMessage("§c{$player->getName()} already have a pet with the name " . $args[2]);
                        } else {
                            if (isset(SimplePets::getInstance()->getPetManager()->getRegisteredPets()[$args[2]])) {
                                if (isset($args[4]) && is_numeric($args[4]) and (float)$args[4] > 0 and (float)$args[4] < 10) {
                                    if (isset($args[5]) && is_bool($args[5])) {
                                        SimplePets::getInstance()->getPetManager()->spawnPet($player, $args[2], $args[3], (float)$args[4], (bool)$args[5]);
                                    } else {
                                        SimplePets::getInstance()->getPetManager()->spawnPet($player, $args[2], $args[3], (float)$args[4]);
                                    }
                                } else {
                                    SimplePets::getInstance()->getPetManager()->spawnPet($player, $args[2], $args[3]);
                                }

                                $sender->sendMessage("§b" . str_replace("Pet", " Pet", $args[2]) . " §awith the name §b" . $args[3] . " §ahas been successfully spawned to §b" . $player->getName());
                            } else {
                                $sender->sendMessage("§cPet with type §4" . $args[2] . " §cis not registered. §aTry §b/spet petlist §ato view registered pets");
                            }
                        }
                    } else {
                        $sender->sendMessage("§cUsage: /spet spawn <player> <petType> <petName> <petSize> <petBaby>");
                    }
                    break;
                case "remove":
                case "delete":
                if (!$sender->hasPermission("simplepets.remove")) {
                    $sender->sendMessage("§cYou don't have permission to run this command");
                    return;
                }

                if (isset($args[1], $args[2])) {
                    $player = Server::getInstance()->getPlayerByPrefix($args[1]);

                    if ($player === null) {
                        $sender->sendMessage("§cPlayer with name $args[1] doesnt exists");
                        return;
                    }

                    if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()][$args[2]])) {
                        $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()][$args[2]];
                        $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                        if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                            $pet->despawn();
                        }

                        SimplePets::getInstance()->getPetManager()->removeActivePet($player, $args[2]);
                        SimplePets::getInstance()->getDatabaseManager()->removePet($player, $args[2]);
                        $sender->sendMessage("§aPet with the name §b" . $args[2] . " §afrom §b{$player->getName()} §ahas been successfully removed");
                    } else {
                        $sender->sendMessage("§a{$player->getName()} don't have a pet with the name §b" . $args[2]);
                    }
                } else {
                    $sender->sendMessage("§cUsage: /spet remove <player> <petName>");
                }
                break;
                case "inventory":
                case "inv":
                    if (!SimplePets::getInstance()->getConfig()->get("enable-inventory")) {
                        $sender->sendMessage("§cPet inventory feature is disabled!");
                        return;
                    }

                    if ($sender instanceof Player) {
                        if (isset($args[2])) {
                            if (!$sender->hasPermission("simplepets.inv.other")) {
                                $sender->sendMessage("§cYou don't have permission to run this command");
                                return;
                            }

                            $player = Server::getInstance()->getPlayerByPrefix($args[1]);

                            if ($player === null) {
                                $sender->sendMessage("§cPlayer with name $args[1] doesnt exists");
                            } else {
                                if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()][$args[2]])) {
                                    $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()][$args[2]];
                                    $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                                    if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                        $pet->getInventoryMenu()->send($player, $pet->getName());
                                    }
                                } else {
                                    $sender->sendMessage("§a{$player->getName()} don't have a pet with the name §b" . $args[2]);
                                }

                                return;
                            }
                        } elseif (isset($args[1])) {
                            if (!$sender->hasPermission("simplepets.inv")) {
                                $sender->sendMessage("§cYou don't have permission to run this command");
                                return;
                            }

                            if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]])) {
                                $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]];
                                $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                    if ($pet->isInvEnabled()) {
                                        $pet->getInventoryMenu()->send($sender, $pet->getName());
                                    } else {
                                        $sender->sendMessage("§cInventory access to your pet named §4" . $pet->getName() . " §cis disabled");
                                    }
                                }
                            } else {
                                $sender->sendMessage("§aYou don't have a pet with the name §b" . $args[1]);
                            }
                        } else {
                            $sender->sendMessage("§cUsage: /spet inv <player> <petName>");
                        }
                    }
                    break;
                case "ride":
                    if (!SimplePets::getInstance()->getConfig()->get("enable-riding")) {
                        $sender->sendMessage("§cPet riding feature is disabled!");
                        return;
                    }

                    if (!$sender->hasPermission("simplepets.ride")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    if ($sender instanceof Player) {
                        if (isset($args[1])) {
                            if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]])) {
                                $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]];
                                $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                    if ($pet->isRidingEnabled()) {
                                        $pet->link($sender);
                                    } else {
                                        $sender->sendMessage("§cRiding access to your pet named §4" . $pet->getName() . " §cis disabled");
                                    }
                                }
                            } else {
                                $sender->sendMessage("§aYou don't have a pet with the name §b" . $args[1]);
                            }
                        } else {
                            $sender->sendMessage("§cUsage: /spet ride <petName>");
                        }
                    }
                    break;
                case "petlist":
                    if (!$sender->hasPermission("simplepets.petlist")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    $message = "§bSimplePets pet list:\n";

                    foreach (SimplePets::getInstance()->getPetManager()->getRegisteredPets() as $type => $class) {
                        $message .= "§b- §a" . $type . "\n";
                    }

                    $sender->sendMessage($message);
                    break;
                case "help":
                    $sender->sendMessage("\n§7---- ---- ---- - ---- ---- ----\n§eCommand List:\n§2» /spet petlist\n§2» /spet spawn <player> <petType> <petName> <petSize>\n§2» /spet remove <player> <petName>\n§2» /spet inv <player> <petName>\n§2» /spet ride <petName>\n§7---- ---- ---- - ---- ---- ----");
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Subcommand '$args[0]' not found! Try '/spet help' for help.");
            }
        } else {
            $sender->sendMessage("§7---- ---- [ §aSimplePets§7 ] ---- ----\n§bAuthor: @brokiem\n§3Source Code: github.com/brokiem/SimplePets\nVersion " . $this->getOwningPlugin()->getDescription()->getVersion() . "\n§7---- ---- ---- - ---- ---- ----");
        }
    }

    public function getOwningPlugin(): Plugin {
        return SimplePets::getInstance();
    }
}