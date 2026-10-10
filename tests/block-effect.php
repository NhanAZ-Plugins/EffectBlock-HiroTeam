<?php

declare(strict_types=1);

use HiroTeam\blockEffect\BlockEffect;
use HiroTeam\blockEffect\BlockEffectListener;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\EffectManager;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\math\Vector3;

$serverSource = $argv[1] ?? null;
if (!is_string($serverSource) || !is_file($serverSource . '/vendor/autoload.php')) {
    fwrite(STDERR, "Usage: php tests/block-effect.php /path/to/Axolotl-PM [plugin PHAR]\n");
    exit(2);
}
require $serverSource . '/vendor/autoload.php';
$phar = $argv[2] ?? null;
if (is_string($phar)) {
    require 'phar://' . realpath($phar) . '/src/HiroTeam/blockEffect/BlockEffect.php';
    require 'phar://' . realpath($phar) . '/src/HiroTeam/blockEffect/BlockEffectListener.php';
} else {
    require dirname(__DIR__) . '/src/HiroTeam/blockEffect/BlockEffect.php';
    require dirname(__DIR__) . '/src/HiroTeam/blockEffect/BlockEffectListener.php';
}

final class TestWorld extends World {
    public ?Vector3 $lastPosition = null;

    public function __construct(public Block $testBlock) {}

    public function getBlock(Vector3 $pos, bool $cached = true, bool $addToCache = true): Block {
        $this->lastPosition = $pos;
        return $this->testBlock;
    }
}

final class TestEffects extends EffectManager {
    /** @var list<EffectInstance> */
    public array $added = [];

    public function __construct() {}

    public function add(EffectInstance $effect): bool {
        $this->added[] = $effect;
        return true;
    }
}

final class TestPlayer extends Player {
    public function __construct(private TestWorld $testWorld, private TestEffects $testEffects) {}

    public function __destruct() {}

    public function getWorld(): World {
        return $this->testWorld;
    }

    public function getEffects(): EffectManager {
        return $this->testEffects;
    }
}

function check(bool $condition, string $case): void {
    if (!$condition) {
        throw new RuntimeException("Failed: $case");
    }
}

$world = new TestWorld(VanillaBlocks::STONE());
$effects = new TestEffects();
$player = new TestPlayer($world, $effects);
$configured = new EffectInstance(VanillaEffects::POISON(), 400, 0, true);
$plugin = (new ReflectionClass(BlockEffect::class))->newInstanceWithoutConstructor();
(new ReflectionProperty(BlockEffect::class, 'rules'))->setValue($plugin, [$world->testBlock->getStateId() => [$configured]]);
$listener = new BlockEffectListener($plugin);
$from = new Location(0, 1, 0, $world, 0, 0);
$to = new Location(0, 1, 0, $world, 0, 0);
$listener->onBlock(new PlayerMoveEvent($player, $from, $to));

check(count($effects->added) === 1, 'matching block adds one effect');
check($effects->added[0] !== $configured, 'effect instance is copied for each player');
check($effects->added[0]->getType() === VanillaEffects::POISON(), 'effect type is preserved');
check($effects->added[0]->getDuration() === 400, 'configured duration is preserved');
check($world->lastPosition?->getY() === 0.0, 'block below destination is inspected');

$world->testBlock = VanillaBlocks::SAND();
$listener->onBlock(new PlayerMoveEvent($player, $from, $to));
check(count($effects->added) === 1, 'unconfigured block adds no effect');

echo "PASS: 6 block effect cases\n";
