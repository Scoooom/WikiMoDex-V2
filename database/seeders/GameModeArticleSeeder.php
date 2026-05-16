<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WikiArticle;

class GameModeArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'slug'     => 'chaos-mode',
                'title'    => 'Chaos Mode',
                'category' => 'Game Modes',
                'order'    => 10,
                'content'  => <<<MD
# Chaos Mode

Taking inspiration from roguelike masters like Slay the Spire and Inscryption, Chaos Mode lets you **choose your own path** through every run. Rather than following a fixed wave sequence, you'll navigate a branching map of encounters — planning your route and adapting to what the Void throws at you.

## How It Works

At the start of each wave group, you're presented with a battle path — a branching map of nodes. Each node represents a different type of encounter. Choose wisely: the path you take determines what you'll face next.

> Plan your path, and do your best to overcome the Chaos within the VOID!

## Chaos Mode Variants

Chaos Mode comes in several flavours, each changing your starting conditions:

### By Starter Type

| Variant | Description |
|---|---|
| **Chaos Journey** | Assemble your team from Pokémon you've already caught or hatched, including fusions. |
| **Chaos Rogue** | Draft 2 random Pokémon to start your run. Use ΩGOLD to reroll your options — each run is unique. |
| **Chaos Void** | Journey-style run that goes all the way to the Void endgame (1000 waves). |
| **Chaos Rogue Void** | Rogue-style run going to 1000 waves. |
| **Chaos Infinite / Chaos Infinite Rogue** | Effectively endless — final wave is 100,000. |

### Nuzlocke Variants

Each base variant also has Nuzlight and Nuzlocke versions:

- **Nuzlight** — a lighter Nuzlocke: fainted Pokémon are benched but not permanently lost.
- **Nuzlocke** — traditional rules apply: fainted Pokémon are permanently removed.
- Both are available as **Draft** variants (Rogue-style starters).

### By Length

Each Chaos mode comes in three lengths:

| Suffix | Final Wave |
|---|---|
| *(none — Abyss)* | 500 |
| **Midnight** | 200 |
| **FTL** (Faster Than Light) | 100 |

Void modes have different caps: Abyss = 1000, Midnight = 400, FTL = 200.

## Tips

- Use Chaos Journey if you want to bring your strongest caught Pokémon into the run.
- Use Chaos Rogue if you prefer a fresh draft challenge each time.
- Shorter variants (Midnight / FTL) are great for practising without committing to a full run.
- ΩGOLD carries over between runs — save it to reroll bad Rogue starters or unlock fusions.
MD,
            ],
            [
                'slug'     => 'gauntlet-mode',
                'title'    => 'Gauntlet Mode',
                'category' => 'Game Modes',
                'order'    => 20,
                'content'  => <<<MD
# Gauntlet Mode

Gauntlet Mode is PokeVoid's take on PokeRogue's classic wave-based format — intensively remixed with PokeVoid's mechanics, game-breaking items, and non-stop rival encounters. If Chaos Mode is about planning your path, Gauntlet Mode is about surviving the storm.

> Enter the Gauntlet! Take on wave after wave of intense encounters!

## How It Works

Unlike Chaos Mode, Gauntlet runs follow a **linear wave sequence** — no branching paths, no route planning. Every wave comes at you in order, with trainers, rivals, and bosses appearing at fixed intervals.

## Gauntlet Variants

| Mode | Description |
|---|---|
| **Gauntlet Journey** | Use your caught and hatched Pokémon. Fusions available with ΩGOLD. Final wave: 90. |
| **Gauntlet Rogue** | Draft 2 random starters. Reroll with ΩGOLD. Final wave: 90. |
| **Gauntlet Nuzlight** | Journey-style with lighter Nuzlocke rules — fainted Pokémon are benched. No shop. |
| **Gauntlet Nuzlocke** | Journey-style with full Nuzlocke rules — fainted Pokémon are gone permanently. |
| **Gauntlet Nuzlight Rogue** | Rogue starters + Nuzlight rules. |
| **Gauntlet Nuzlocke Rogue** | Rogue starters + full Nuzlocke rules. |
| **The Void** (Nightmare) | The ultimate Gauntlet challenge. 500 waves, draft starters, shifting rules as you progress. |

## Unlocking Journey Mode

Gauntlet Journey becomes available after **catching 15 Pokémon**. After catching your first Pokémon, visit the Shop and pick up the **Starter Catch Quest** to officially start the unlock chain.

## Key Differences from Chaos Mode

- No path selection — waves are fixed and sequential.
- Shorter runs overall (most end at wave 90).
- Better for players who prefer a consistent, predictable structure.
- Rivals appear at set wave intervals rather than as path nodes.

## Tips

- Gauntlet Rogue is a great way to experience fresh team compositions without needing a large Pokédex.
- Nuzlight is a good middle ground if full Nuzlocke feels too punishing.
- The Void (Nightmare mode) is the hardest content in the game — don't attempt it until you're comfortable with the base game.
MD,
            ],
        ];

        foreach ($articles as $data) {
            WikiArticle::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        WikiArticle::reindexAll();

        $this->command->info('GameModeArticleSeeder: Chaos Mode and Gauntlet Mode articles seeded.');
    }
}
