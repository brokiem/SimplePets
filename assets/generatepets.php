<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\pets;

class generatepets {

    public const ARMOR_STAND = "minecraft:armor_stand";
    public const ARROW = "minecraft:arrow";
    public const BAT = "minecraft:bat";
    public const BEE = "minecraft:bee";
    public const BLAZE = "minecraft:blaze";
    public const BOAT = "minecraft:boat";
    public const CAT = "minecraft:cat";
    public const CAVE_SPIDER = "minecraft:cave_spider";
    public const CHEST_MINECART = "minecraft:chest_minecart";
    public const CHICKEN = "minecraft:chicken";
    public const COD = "minecraft:cod";
    public const COMMAND_BLOCK_MINECART = "minecraft:command_block_minecart";
    public const COW = "minecraft:cow";
    public const CREEPER = "minecraft:creeper";
    public const DOLPHIN = "minecraft:dolphin";
    public const DONKEY = "minecraft:donkey";
    public const DRAGON_FIREBALL = "minecraft:dragon_fireball";
    public const DROWNED = "minecraft:drowned";
    public const EGG = "minecraft:egg";
    public const ELDER_GUARDIAN = "minecraft:elder_guardian";
    public const ELDER_GUARDIAN_GHOST = "minecraft:elder_guardian_ghost";
    public const ENDER_CRYSTAL = "minecraft:ender_crystal";
    public const ENDER_DRAGON = "minecraft:ender_dragon";
    public const ENDER_PEARL = "minecraft:ender_pearl";
    public const ENDERMAN = "minecraft:enderman";
    public const ENDERMITE = "minecraft:endermite";
    public const EVOCATION_FANG = "minecraft:evocation_fang";
    public const EVOCATION_ILLAGER = "minecraft:evocation_illager";
    public const EYE_OF_ENDER_SIGNAL = "minecraft:eye_of_ender_signal";
    public const FIREBALL = "minecraft:fireball";
    public const FOX = "minecraft:fox";
    public const GHAST = "minecraft:ghast";
    public const GUARDIAN = "minecraft:guardian";
    public const HOGLIN = "minecraft:hoglin";
    public const HOPPER_MINECART = "minecraft:hopper_minecart";
    public const HORSE = "minecraft:horse";
    public const HUSK = "minecraft:husk";
    public const IRON_GOLEM = "minecraft:iron_golem";
    public const LINGERING_POTION = "minecraft:lingering_potion";
    public const LLAMA = "minecraft:llama";
    public const MAGMA_CUBE = "minecraft:magma_cube";
    public const MINECART = "minecraft:minecart";
    public const MOOSHROOM = "minecraft:mooshroom";
    public const MULE = "minecraft:mule";
    public const OCELOT = "minecraft:ocelot";
    public const PANDA = "minecraft:panda";
    public const PARROT = "minecraft:parrot";
    public const PHANTOM = "minecraft:phantom";
    public const PIG = "minecraft:pig";
    public const PIGLIN = "minecraft:piglin";
    public const PILLAGER = "minecraft:pillager";
    public const POLAR_BEAR = "minecraft:polar_bear";
    public const PUFFERFISH = "minecraft:pufferfish";
    public const RABBIT = "minecraft:rabbit";
    public const RAVAGER = "minecraft:ravager";
    public const SALMON = "minecraft:salmon";
    public const SHEEP = "minecraft:sheep";
    public const SHULKER = "minecraft:shulker";
    public const SHULKER_BULLET = "minecraft:shulker_bullet";
    public const SILVERFISH = "minecraft:silverfish";
    public const SKELETON = "minecraft:skeleton";
    public const SKELETON_HORSE = "minecraft:skeleton_horse";
    public const SLIME = "minecraft:slime";
    public const SMALL_FIREBALL = "minecraft:small_fireball";
    public const SNOW_GOLEM = "minecraft:snow_golem";
    public const SNOWBALL = "minecraft:snowball";
    public const SPIDER = "minecraft:spider";
    public const SPLASH_POTION = "minecraft:splash_potion";
    public const SQUID = "minecraft:squid";
    public const STRAY = "minecraft:stray";
    public const STRIDER = "minecraft:strider";
    public const TNT = "minecraft:tnt";
    public const TNT_MINECART = "minecraft:tnt_minecart";
    public const TROPICALFISH = "minecraft:tropicalfish";
    public const TURTLE = "minecraft:turtle";
    public const VEX = "minecraft:vex";
    public const VILLAGER = "minecraft:villager";
    public const VILLAGER_V2 = "minecraft:villager_v2";
    public const VINDICATOR = "minecraft:vindicator";
    public const WANDERING_TRADER = "minecraft:wandering_trader";
    public const WITCH = "minecraft:witch";
    public const WITHER = "minecraft:wither";
    public const WITHER_SKELETON = "minecraft:wither_skeleton";
    public const WITHER_SKULL = "minecraft:wither_skull";
    public const WOLF = "minecraft:wolf";
    public const ZOGLIN = "minecraft:zoglin";
    public const ZOMBIE = "minecraft:zombie";
    public const ZOMBIE_HORSE = "minecraft:zombie_horse";
    public const ZOMBIE_PIGMAN = "minecraft:zombie_pigman";
    public const ZOMBIE_VILLAGER = "minecraft:zombie_villager";
    public const ZOMBIE_VILLAGER_V2 = "minecraft:zombie_villager_v2";

    public static function main(): void {
        $constants = self::getConstants();

        foreach ($constants as $constant => $value) {
            $const = ucfirst(strtolower(str_replace("_", "", $constant))) . "Pet";

            $contents = "<?php\n\ndeclare(strict_types=1);\n\nnamespace brokiem\simplepets\pets;\n\n";
            $contents .= "use brokiem\simplepets\pets\base\BasePet;\n\n";
            $contents .= "class " . $const . " extends BasePet {\n\n";
            $contents .= "public const SPET_ENTITY_ID = \"$value\";\n\n";
            $contents .= "public \$height = 0.9;\npublic \$width = 0.9;\n\n";
            $contents .= "public function getPetType(): string {\n";
            $contents .= "return \"$const\";\n}\n}";

            file_put_contents("src/brokiem/simplepets/pets/" . $const . ".php", $contents);
        }
    }

    public static function getConstants(): array {
        return (new \ReflectionClass(static::class))->getConstants();
    }

    public static function getDefaultPets(): void {
        $text = "";
        foreach (glob('src/brokiem/simplepets/pets/*.php') as $file) {
            $text .= "\"" . str_replace(".php", "", basename($file)) . "\" => \"" . str_replace("/", "\\", str_replace(".php", "", $file)) . "\",\n";
        }

        var_dump($text);
    }
}

generatepets::main();