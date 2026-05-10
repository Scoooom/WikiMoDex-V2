<?php

namespace Database\Seeders;

use App\Models\WikiArticle;
use Illuminate\Database\Seeder;

class WikiSeeder extends Seeder
{
    public function run(): void
    {
        WikiArticle::truncate();

        $articles = [

            // ── GETTING STARTED ──────────────────────────────────────────

            [
                'slug'     => 'introduction',
                'title'    => 'Introduction to PokéVoid',
                'category' => 'Getting Started',
                'order'    => 1,
                'content'  => <<<MD
# Introduction to PokéVoid

PokéVoid is a battle-focused Pokémon fangame built on a roguelite framework. Every run is different — biomes are randomised, items are unpredictable, and the deeper you go, the more powerful (and dangerous) everything becomes.

> PokéVoid is not monetized and claims no ownership of Pokémon or its copyrighted assets. It is a fan project in active development.

## What Makes PokéVoid Different?

While it shares DNA with PokéRogue — the roguelike project that inspired it — PokéVoid takes a darker, more chaotic direction:

- **Glitch & Smitty Forms** — Over 150 new Pokémon transformations with unique abilities, types, and stats
- **Corruption mechanics** — Rivals echo from The Void as corrupted entities
- **Chaos Modes** — Path-based roguelite runs inspired by Slay the Spire and Inscryption
- **Champions** — Choose a Champion with unique typing, a personal skill tree, and signature Pokémon
- **Omega System** — Persistent upgrades (ΩITEMS) and currency (ΩGOLD) that carry across runs
- **Move Upgrades** — Upgrade almost any move's power, priority, effects, and more
- **Quests & Bounties** — Reusable challenges that reward permanent progression

## Game Structure

Each run is a series of battles across randomly generated biomes. After every battle you choose from three random items. Trainers appear periodically, and powerful multi-form bosses guard key milestones.

Runs have a **wave-based structure** — every 10 waves brings a milestone, with gym-style trainers at wave 20 within each block and boss encounters at wave 10 multiples. The game ends (in classic modes) when you lose all your Pokémon, or after defeating the final boss.

## Getting Started

1. **Choose a game mode** from the title screen. New players should start with **Gauntlet Journey** (Classic) or **Gauntlet Nuzlight** for a lighter challenge.
2. **Select your starters** — each Pokémon has a point value, and your party total cannot exceed **10 points** with up to 6 members.
3. **Fight through biomes**, picking items after each battle to build a powerful team synergy.
4. **Unlock new content** — defeating rivals unlocks Glitch Quests, completing runs unlocks new game modes and Omega Items.

## Tips for New Players

- Hardware Acceleration should be **on** in your browser for best performance (Chrome Settings → System).
- **Export your save often** using the floppy disk icon — local browser data can be lost.
- The shop inventory **refreshes with each purchase** — buy what you need first before picking your random item.
- Press **R** at any time during a battle to view your current run details.
MD,
            ],

            [
                'slug'     => 'starter-selection',
                'title'    => 'Starter Selection',
                'category' => 'Getting Started',
                'order'    => 2,
                'content'  => <<<MD
# Starter Selection

The Starter Select screen is where you build your initial party before each run. Understanding the system is key to a strong start.

## Point System

Each Pokémon has a **point value** (roughly tied to its power level). Your party can have up to **6 members**, but the combined total cost cannot exceed **10 points** by default.

- Omega Items can raise the starter point limit
- The **Lower Starter Points** challenge reduces your allowance further
- The **Gauntlet Rogue** (Draft) mode gives you 2 random Pokémon instead of free choice

## IVs and Starters

A Pokémon's IVs in your starter pool are the **best IVs across all copies** you've ever caught or hatched. Catching many of the same species improves your starter's stats permanently.

## Abilities, Genders, and Forms

Depending on what you've unlocked, you may be able to select:
- **Ability** — including Hidden Abilities if unlocked
- **Gender** — if you've caught both
- **Form** — including Glitch and Smitty forms you've unlocked

## Journey Mode Unlock

**Journey Mode** becomes available after catching **15 Pokémon**. It lets you build your team from your full Pokémon collection, including fusions. To officially start the unlock quest, get the **Starter Catch Quest** from the Shop after catching your first Pokémon.

## Champion-Specific Rules

Each Champion has restrictions on which Pokémon they can use:
- **Apollo & Diana** — can use any Pokémon, but have randomised types each run
- **Type Champions** (e.g. Brock) — can only use Pokémon matching their type affinity, and only those you've caught while playing as them
- **Freed Champions** start with an empty collection and must rebuild it

## Daily Pokerus

Each day, **3 random starters** get a purple border indicating they have Pokerus. Adding these to your party may reveal special benefits — check their summary screen.
MD,
            ],

            [
                'slug'     => 'battle-basics',
                'title'    => 'Battle Basics',
                'category' => 'Getting Started',
                'order'    => 3,
                'content'  => <<<MD
# Battle Basics

PokéVoid uses standard Pokémon battle mechanics as its foundation, with several important additions and modifications.

## Stat Changes

Stat changes **persist across battles** as long as your Pokémon aren't recalled. This means setting up with Swords Dance or Calm Mind carries over to the next fight. However:

- Pokémon are **recalled before trainer battles** and when entering a new biome
- Hold **C or Shift** during battle to view stat changes on field Pokémon
- Hold **V** to peek at a revealed enemy moveset (only moves you've already seen that battle)

## Multi-Form Bosses

Boss Pokémon in PokéVoid often have **multiple forms**. When their HP drops low, they transform and **fully heal**, resetting their stat changes. Plan accordingly — raw burst damage and critical hits become more valuable against bosses.

## The Shop

After every battle you're presented with **3 random items** to choose from. Only pick **one** — the selection triggers progression to the next battle. You can also **purchase consumable items** with money before making your pick. The shop's available items expand the further you progress.

> **Important:** The shop inventory changes with each purchase. Buy what you need before picking your free item, as that locks in the next battle.

## New Battle Commands (V2)

Six additional commands are available during battle:

| Command | Function |
|---------|----------|
| **TEAM** | View your party's stats, abilities, and moves |
| **SKILL TREE** | Access your Champion's skill tree mid-battle |
| **CHECK** | Inspect any Pokémon — abilities, forms, stats, movesets |
| **EGGS** | Access the egg gacha |
| **SHOP** | Quick shop access |
| **MAP** | View your Chaos path (Chaos modes only) |

## Trainer Encounters

Most trainers will offer to **sell you their Pokémon** during battle (Rivals excluded). Purchased Pokémon are permanently added to your starter collection. The cost scales with the wave but can be reduced with Omega Items.

## Catching Fusions

Fusion Pokémon appear in the wild with a base **1/14 encounter rate**, improvable with Omega Items. Caught fusions are permanently added to your collection. Using them in Journey Mode costs ΩGOLD.
MD,
            ],

            // ── CHAMPIONS ────────────────────────────────────────────────

            [
                'slug'     => 'champions-overview',
                'title'    => 'Champions Overview',
                'category' => 'Champions',
                'order'    => 1,
                'content'  => <<<MD
# Champions

Champions are the playable characters of PokéVoid V2. Each Champion has a unique personality, type specialisation, signature Pokémon, and their own Skill Tree. Choosing the right Champion for your playstyle is one of the most important decisions in each run.

## Champion Types

### Apollo — Champion of Sun
Apollo channels radiant power as one of the last lights pushing back the void's endless night. He has **randomised types** at the start of each run, giving him maximum flexibility at the cost of unpredictability. His radiant power may be the difference between survival and oblivion.

### Diana — Champion of Moon
Diana weaves moonlit threads to track the void's movements and guide others to safety. Like Apollo, she has **randomised types** each run and can use any Pokémon regardless of type.

### Type Champions (Brock, Misty, Lt. Surge, and others)
Most Champions have **fixed type specialisations** — their entire Skill Tree, Pokémon pool, and identity revolves around their assigned types. For example:
- **Brock** specialises in Ground and Rock types
- **Misty** specialises in Water types
- **Lt. Surge** specialises in Electric types

Type Champions can **only use Pokémon matching their affinity**, and only those you've caught while playing as that specific Champion. The more you play a Champion, the bigger their pool grows.

## Freeing Champions

Most Champions are **locked inside the Void's corruption** and must be freed before you can play as them. Freeing a Champion requires collecting specific **Essences** (see Essence System). Freed Champions start with an empty Pokémon collection and must rebuild from scratch.

## Signature Pokémon

Each Champion has **Signature Pokémon** — special starters unique to them. Their icons are marked with a chaos indicator. Signatures can grow into **Alt Builds**: alternate forms with different stats, abilities, and even types — essentially a whole new Pokémon entirely.

> Legendary Alt Builds are possible and represent some of the most powerful forms available.
MD,
            ],

            [
                'slug'     => 'skill-trees',
                'title'    => 'Skill Trees',
                'category' => 'Champions',
                'order'    => 2,
                'content'  => <<<MD
# Skill Trees

Each Champion has a personal Skill Tree — a branching web of upgrades that can be unlocked during runs to build permanent power and unique playstyles.

## Skill Points

**Skill Points (SP)** are the currency for purchasing Skill Tree nodes. You earn them from battles, rewards, and special events during a run. Nodes have a cost shown in SP, and some are free.

## Tree Tokens

**Tree Tokens (TK)** are used to **level up your Skill Tree**. Each level increases the maximum depth of the tree, revealing more powerful skills. You need tokens to progress deep into a Champion's tree.

## Purchasing Nodes

- You can only purchase nodes whose **parent nodes are already unlocked**
- Nodes have a **rarity tier** — common nodes are cheap, while rare and master-tier nodes require significant investment
- You can access the Skill Tree **mid-battle** via the SKILL TREE command

## Champion XP & Levelling

Champions gain XP through play. Higher Champion levels unlock more powerful nodes and increase the breadth of the Skill Tree available to you.

## Type Synergy

For type-specialised Champions, many nodes directly enhance their type affinity:
- Type-specific TMs and XMs (eXtended Moves)
- Stat boosters for Pokémon of the Champion's type
- Special abilities that trigger on type-specific conditions

## Glitch & Smitty Essences in the Skill Tree

Some of the most powerful Skill Tree nodes require special essences:
- **Glitch Essence** — collected by defeating Glitch Pokémon. Unlocks Master+ rarity Corrupted Glitch Forms and powerful Legendary Pokémon.
- **Smitty Essence** — collected by facing the Corruption itself. Unlocks Master+ rarity Forbidden Smitty Abilities and broken Alt Builds.

## Apollo & Diana — Randomised Type Synergy

Apollo and Diana receive **two random types** at the start of every run. Their Skill Tree nodes reflect these assigned types, meaning each run with them is a fresh strategic puzzle.
MD,
            ],

            // ── GAME MODES ────────────────────────────────────────────────

            [
                'slug'     => 'game-modes-overview',
                'title'    => 'Game Modes Overview',
                'category' => 'Game Modes',
                'order'    => 1,
                'content'  => <<<MD
# Game Modes Overview

PokéVoid features a large variety of game modes, ranging from beginner-friendly runs to brutal endurance challenges and chaotic path-based roguelikes.

## Gauntlet Modes (Classic-style)

Gauntlet modes are wave-based runs with a set endpoint. They follow PokéRogue's original battle structure, remixed with PokéVoid's unique mechanics.

| Mode | Description |
|------|-------------|
| **Gauntlet Journey** | The standard mode. Pick your team and fight through waves. Unlocked from the start. |
| **Gauntlet Nuzlight** | No healing items in the shop. Fainted Pokémon revive at wave 10 milestones. Great for beginners wanting a challenge. |
| **Gauntlet Nuzlocke** | Traditional Nuzlocke rules — fainted Pokémon are **permanently lost**. |
| **Gauntlet Rogue** | Draft mode. You're given 2 random Pokémon to start. Reroll using ΩGOLD. |
| **Gauntlet Nuzlight Rogue** | Nuzlight rules combined with Draft mode. |
| **Gauntlet Nuzlocke Rogue** | Nuzlocke rules combined with Draft mode. |

## The Void

**The Void** (Nightmare mode) is unlocked after winning a Classic run. It's the hardest mode — the source of all corruption. Stronger enemies, unique encounters, and the true face of the void await.

## Chaos Modes

Chaos modes take inspiration from path-based roguelikes like Slay the Spire. Instead of a linear wave structure, you **choose your path** through a branching map.

Each Chaos mode has three variants:
- **Standard** — full-length run
- **Short** — condensed run
- **FTL (Faster Than Light)** — the fastest variant, jumping straight into chaos

| Base Mode | Description |
|-----------|-------------|
| **Chaos Journey** | Path-based Journey mode |
| **Chaos Rogue** | Path-based Draft mode |
| **Chaos Void** | Path-based Void challenge |
| **Chaos Rogue Void** | Draft + Void mechanics on a chaos map |
| **Chaos Infinite** | Endless chaos — no endpoint |
| **Chaos Infinite Rogue** | Endless chaos with Draft starting rules |
| **Chaos Nuzlight / Nuzlocke** | Chaos maps with Nuzlight or Nuzlocke rules |

## Other Modes

- **Endless** — No endpoint, enemies scale infinitely
- **Endless (Spliced)** — Endless mode but all wild Pokémon are fusions
- **Daily Run** — A fixed seed run refreshed every day. Starts at level 20.
- **Challenge** — Custom challenge runs with selectable modifiers

## Unlocking Modes

Modes are unlocked through progression:
- The Void unlocks after your first Classic victory
- Chaos modes unlock as you clear harder content
- Unlocking a new mode also unlocks new items, abilities, and shop options
MD,
            ],

            [
                'slug'     => 'challenge-modifiers',
                'title'    => 'Challenge Modifiers',
                'category' => 'Game Modes',
                'order'    => 2,
                'content'  => <<<MD
# Challenge Modifiers

Challenge Mode lets you apply modifiers to a run to increase difficulty and customise your experience. Challenges affect starter selection, battles, and progression.

## Available Challenges

### Single Generation
Restricts your team to Pokémon from a single Pokémon generation (Gen 1–9). Elite Four encounters at waves 182–190 adjust to match your generation.

### Single Type
Restricts your entire team to Pokémon that share a specific type. Some Pokémon have override rules (e.g., Castform is always Normal type for this challenge).

### Fresh Start
Resets all your starter advantages — abilities default to Ability 1, no passive, Hardy nature, IVs set to 10 across the board, no shinies, and only level 1–5 moves. Selectable Pokémon are limited to the default starter pool.

### Inverse Battle
All type effectiveness is flipped. Resistances become weaknesses and vice versa. Immunities become double weaknesses (×2), and super effective hits become double resistances (×0.5).

### Lower Max Starter Cost
Reduces the maximum total cost for your starter team, forcing you to use lower-value Pokémon.

### Lower Starter Points
Reduces the total point allowance for your team, making it harder to field powerful Pokémon.

## Combining Challenges

Multiple challenges can be active simultaneously in Challenge Mode, letting you stack modifiers for extreme difficulty runs.
MD,
            ],

            // ── GLITCH SYSTEM ─────────────────────────────────────────────

            [
                'slug'     => 'glitch-system',
                'title'    => 'The Glitch System',
                'category' => 'Glitch System',
                'order'    => 1,
                'content'  => <<<MD
# The Glitch System

The Glitch System is PokéVoid's core progression mechanic. It allows you to transform ordinary Pokémon into powerful corrupted entities — and eventually unlock permanent new forms.

## Glitch Pieces

**Glitch Pieces** are the foundational resource of the system. They drop from battles and can be found as items. You need a minimum number of Glitch Pieces to access different Glitch Items:

- **2+ Glitch Pieces** — unlocks standard Glitch Items (ability swappers, type changers, stat swaps, AnyTMs)
- **5+ Glitch Pieces** — unlocks Glitch Form Change items

The maximum number of Glitch Pieces you can hold is fixed by default but can be raised with Omega Items.

## Glitch Items

Glitch Items permanently modify a Pokémon in your current run:

| Item | Effect | Cost |
|------|--------|------|
| **Ability Switcher** | Swap a Pokémon's ability with one of its alternatives | 1 Glitch Piece |
| **Ability Item** | Change a Pokémon's ability to any other ability | 1 Glitch Piece |
| **Primary Type Switcher** | Change a Pokémon's primary type | 1 Glitch Piece |
| **Secondary Type Switcher** | Change a Pokémon's secondary type | 1 Glitch Piece |
| **Type Switcher** | Change both types simultaneously | 1 Glitch Piece |
| **Stat Switcher** | Swap two of a Pokémon's base stats (e.g. ATK ↔ DEF) | 1 Glitch Piece |
| **AnyTM** | Teach a move to any Pokémon regardless of compatibility | 1 Glitch Piece |
| **Release Powerups** | Release a Pokémon to grant its typing, moves, abilities, or a stat boost to another. Alternatively spend 5 Essences from a Pokémon instead of releasing it. | — |

## Glitch Forms

Glitch Forms are **permanent new Pokémon forms** unlocked through the Rival Quest system (see Rivals & Quests). Once unlocked, a Glitch Form is available to use in all future runs as a starter form.

Glitch Forms are not simply stat boosts — they come with:
- New or altered types
- New or changed abilities (including unique Glitch abilities)
- Redistributed base stats
- A unique appearance

There are over 150 Glitch and Smitty forms in the game, with names like **Tartauros, Zamowak, Hellchar, Necromew, Diablotar**, and many more.

## Mod Glitch Forms

The WikiMoDex community can submit their own **Mod Glitch Forms** via the create form. These are community-made Pokémon forms that appear in the game's gallery and can be viewed here on the wiki.
MD,
            ],

            [
                'slug'     => 'move-upgrades',
                'title'    => 'Move Upgrades',
                'category' => 'Glitch System',
                'order'    => 2,
                'content'  => <<<MD
# Move Upgrades

Move Upgrades are one of PokéVoid's most powerful and chaotic systems. They allow you to fundamentally alter any move's behaviour — turning a weak attack into a game-breaking weapon.

## How It Works

During a run, you may be offered a Move Upgrade as a reward. Each upgrade targets a specific move and modifies one aspect of it. The catch: **the upgrade applies to ALL copies of that move** — your Pokémon, enemy Pokémon, and everything in between.

## What Can Be Upgraded

Each move can have upgrades across multiple paths:

- **Power** — increase base damage
- **Priority** — raise or lower the move's priority tier
- **Effect** — change or add a secondary effect
- **Effect Chance** — increase the probability of a secondary effect triggering
- **Multi-Hit** — make the move hit multiple times
- **And more** — upgrades cover a wide range of modifications

## Upgrade Tiers

Each upgrade starts at **Tier 1 (weak)** and can be upgraded further. By continuously upgrading the same move, you can elevate even the weakest move to legendary power.

## Important Rules

- A move can only have **one effect chance** — upgrades either directly set it or inherit the previous value
- **The newest upgrade takes priority** if two upgrades conflict
- Some upgrade combinations are known to break the game — refresh and choose a different combination if you encounter issues
- Moves can only receive **one upgrade** from any given category at a time

## Game Breaking Combos

Since you can upgrade almost any move, some combinations will be extraordinarily powerful. This is by design — PokéVoid embraces chaos. If you find a broken combination, report it in the Discord so it can be catalogued.
MD,
            ],

            // ── SMITTY POKÉMON ────────────────────────────────────────────

            [
                'slug'     => 'smitty-forms',
                'title'    => 'Smitty Forms',
                'category' => 'Smitty Pokémon',
                'order'    => 1,
                'content'  => <<<MD
# Smitty Forms

Smitty Forms are a second category of special Pokémon transformation — distinct from Glitch Forms. Where Glitch Forms are unlocked through Rival Quests, Smitty Forms are created using specific combinations of **Smitty Items**.

## How Smitty Forms Work

To create a Smitty Form, you need **exactly four Smitty Items** applied to a base Pokémon in the correct combination. The combination identifies the target form.

- Some forms require a **specific base Pokémon** (e.g., Rotom → Smitom)
- Others are **Universal** — any Pokémon can be used as the base (e.g., any Pokémon → Picklisk)
- All Smitty Forms gain a **random passive ability**

## Discovering Recipes

Smitty Form combinations are secret by default. To discover a recipe:

1. Unlock a Smitty Form (shown in a tutorial notification)
2. Find the form's **console code** — this identifies the specific Smitty Form
3. Enter the code in the console to reveal the **four required Smitty Items**

> The combination is intentionally hidden — discovering recipes is part of the experience. The Discord community maintains lists of known combinations.

## Smitom

**Smitom** is a special Rotom in Smitty Form — the game's mysterious guide character. When an exclamation mark appears over Smitom, interact with him for potential rewards. He also rewards you for saving your game.

## Smitty Items

Smitty Items are special form-change items with names drawn from the game's lore. Some known Smitty Items include:
- Smitty Glitch
- Smitty Heart
- Smitty Humor
- Glitchi Glitchi Fruit
- Glitch Command Seal
- Glitch Mod Soul
- Glitch Shout
- Glitch Master Parts

These items appear as rewards and in shops during runs.

## The WikiMoDex & Smitty Forms

The [Smitty Forms gallery](/gallerySmitty.html) on WikiMoDex lists all known Smitty Forms, including their required items and base Pokémon where applicable.
MD,
            ],

            // ── RIVALS & QUESTS ───────────────────────────────────────────

            [
                'slug'     => 'rivals',
                'title'    => 'Rivals',
                'category' => 'Rivals & Quests',
                'order'    => 1,
                'content'  => <<<MD
# Rivals

Every run in PokéVoid features a **random Rival** — a trainer with a themed team and a signature Pokémon. Rivals are drawn from a large pool of iconic Pokémon trainers.

## Rival Pools

Rivals are drawn from several pools:

- **Rivals** — Blue, Red
- **Champions** — Lance, Cynthia, Steven, Alder, Iris
- **Evil Team Admins** — Giovanni, Cyrus, Ghetsis, Archie, Maxie, Lysandre, Guzma, Rose
- **Gym Leaders** — Brock, Misty, Lt. Surge, Blaine, Sabrina, Roxie, Allister, Norman
- **Other Trainers** — Larry, Wallace, Lusamine, Nemona, Hau

In Nightmare Mode (The Void), any rival from any pool can appear.

## Rival Encounters

Your rival appears multiple times across a run (6 encounters total), growing stronger each time:
- Encounters 1–3 use standard battle music
- Encounters 4–5 use the second rival battle theme
- Encounter 6 (the final battle) uses the most intense theme

## Corrupted Rivals

After defeating a rival, their echo lingers in The Void as a **Corrupted Rival** — a twisted version of that trainer with a Glitch Form team. These are not the trainers you remember:

> *"Corrupted entities twisted by the void, their true form lies in wait for you in the void. Save them."*

## Defeating Rivals → Glitch Quests

Defeating a Rival unlocks **Glitch Quests** for new Glitch Forms. These quests are then purchasable from the OmegaShop and, when completed, **permanently unlock** a Glitch Form for a specific Pokémon.

## Rival Bounties

Active Rival Bounties are run-specific challenges tied to your current rival. Up to **3 Rival Bounties** can be active at once. Completing them rewards ΩGOLD and ΩITEMS.

## The Final Rival Battle (Finn / Ivy)

In Classic modes, your permanent rival is **Finn** (male) or **Ivy** (female). They appear at fixed waves as your run progresses, with escalating party sizes and increasing money multipliers. The final encounter (Wave 190-equivalent) is a boss fight.
MD,
            ],

            [
                'slug'     => 'quests',
                'title'    => 'Quests & Bounties',
                'category' => 'Rivals & Quests',
                'order'    => 2,
                'content'  => <<<MD
# Quests & Bounties

PokéVoid's Quest and Bounty system provides structured challenges that reward permanent progression, ΩGOLD, and new Pokémon forms.

## Quest Types

### Glitch Quests
Unlocked by defeating Rivals. Purchase them from the OmegaShop. Complete the in-run objective to **permanently unlock a Glitch Form** for a specific Pokémon.

Example quests involve conditions like:
- Defeat a Pokémon using a specific type of move
- Win a battle under specific conditions
- Use a Pokémon's signature move a number of times

There are **over 60 unique Glitch Quests**, each tied to a specific Pokémon (Tauros, Kecleon, Gliscor, Marowak, Noivern, Feraligatr, Charizard, Venusaur, Blastoise, Gengar, and many more).

### Bounties

Bounties are **reusable challenges** that reward ΩGOLD and ΩITEMS upon completion. They are accessed by entering **6-letter codes** in the console.

- **Rival Bounties** — tied to your current rival (up to 3 active)
- **Smitty Bounties** — tied to Smitty Pokémon encounters (up to 3 active)
- **Quest Bounties** — general objective-based bounties (up to 5 active)

#### Daily Bounty
A new Daily Bounty is available every 24 hours. The code is automatically placed in the console — just activate it. Completing it rewards ΩGOLD and/or ΩITEMS.

> Bounty codes are shared in the **Smittyverse Discord** server. Check the Discord regularly for fresh codes.

## Quest States

Quests track their state across your account:
- **Inactive** — not yet started
- **Active** — purchased or activated, objective pending
- **Completed** — objective met; reward claimed

Completed quests (Glitch Quests especially) permanently unlock their content — you never need to complete them again.
MD,
            ],

            // ── OMEGA FEATURES ────────────────────────────────────────────

            [
                'slug'     => 'omega-features',
                'title'    => 'Omega Features (ΩGOLD & ΩITEMS)',
                'category' => 'Omega Features',
                'order'    => 1,
                'content'  => <<<MD
# Omega Features

The Omega System is PokéVoid's persistent progression layer. While your Pokémon collection and run history persist between runs in a standard roguelite fashion, the Omega System adds a layer of **permanent power upgrades** that carry into every future run.

## ΩGOLD (OmegaMoney)

ΩGOLD is a persistent currency earned between runs. It does not reset when you start a new run.

**Sources of ΩGOLD:**
- Completing Bounties
- Special rewards and run endings
- Saving your game (the floppy disk icon in the top-right rewards ΩGOLD and protects your data)
- Smitom interactions

**Uses of ΩGOLD:**
- Purchasing ΩITEMS from the OmegaShop
- Rerolling Rogue (Draft) mode starting Pokémon
- Paying the Fusion Tax (using Fusion Pokémon in Journey mode)

## ΩITEMS (OmegaItems)

ΩITEMS are long-lasting upgrades purchased with ΩGOLD from the OmegaShop. They have two duration types:

- **Usage-based** — quantity decreases after each use (e.g. "catch rate boost activates 3 times")
- **Wave-based** — quantity decreases after every 50 waves

### Available ΩITEM Categories

| Category | Examples |
|----------|---------|
| **Shiny Rate** | Improved shiny encounter chances |
| **Fusion Rate** | Better fusion encounter rate |
| **Catch Rate** | Improved catch rates |
| **Reroll Cost** | Cheaper rerolls in Draft mode |
| **Shop Rewards** | More items shown per shop visit |
| **Revive Items** | More revive items available |
| **Starting Resources** | More money, balls, or Glitch Pieces at run start |
| **Trainer Costs** | Cheaper Pokémon snatching from trainers |
| **Tera Duration** | Longer Terastallisation effects |
| **Stat Boost Duration** | Longer stat boost persistence |
| **Fusion Costs** | Cheaper fusion use in Journey mode |
| **Starter Point Limit** | Increased starter team point allowance |
| **Glitch Pieces** | More pieces per pickup, higher maximum cap |

> ΩITEMS are powerful but limited — manage them carefully across runs.

## Removing ΩITEMS

You can remove an ΩITEM via the menu (Manage Data → Remove ΩITEM) if you want to free up a slot or avoid an unwanted effect.
MD,
            ],

            [
                'slug'     => 'essences',
                'title'    => 'Essences',
                'category' => 'Omega Features',
                'order'    => 2,
                'content'  => <<<MD
# Essences

Essences are a resource introduced in PokéVoid V2. They are collected during runs and used for two main purposes: **levelling up Champions** and **purchasing Champion Skill Tree nodes**.

## Collecting Essences

Essences drop when you defeat Pokémon. Each Essence matches the **type of the defeated Pokémon**. There are 18 standard Essence types corresponding to all 18 Pokémon types.

## Using Essences

- **Champion Levelling** — spend Essences matching your Champion's type affinity to level them up
- **Skill Tree Nodes** — some nodes require Essences of specific types in addition to Skill Points
- **Release Powerups** — as an alternative to releasing a Pokémon, you can spend **5 Essences** from that Pokémon to release its power

## Special Essences

Two special Essences exist outside the normal type system:

### Glitch Essence
Collected by defeating **Glitch Pokémon** (Pokémon in a Glitch Form). Glitch Essences unlock:
- Master+ rarity Corrupted Glitch Forms in the Skill Tree
- Powerful Legendary Pokémon skill nodes

### Smitty Essence
Collected by **facing the Corruption itself** (high-tier Void encounters). Smitty Essences unlock:
- Master+ rarity Forbidden Smitty Abilities
- Broken Alt Build skill nodes

These special essences represent the highest tier of progression in the game.
MD,
            ],

            // ── ITEMS & SHOP ──────────────────────────────────────────────

            [
                'slug'     => 'items-overview',
                'title'    => 'Items Overview',
                'category' => 'Items & Shop',
                'order'    => 1,
                'content'  => <<<MD
# Items

Items are one of the primary progression vectors within a run. After every battle, you choose one of three randomly offered items. Items range from single-use consumables to powerful passive bonuses that compound over a full run.

## Item Categories

### Consumables
Single-use items like healing potions, revives, and status cures. These can also be purchased from the shop during battle rewards.

### Held Items
Equipped to individual Pokémon. Effects generally **stack** in various ways with duplicates. Examples include damage boosters, speed items, and defensive tools drawn from mainline Pokémon games.

### Passive Run Items
Permanent for the duration of the run. These include things like catch rate boosts, money multipliers, and other run-wide effects.

### Form Change Items
Items that trigger specific Pokémon form changes (Mega Evolution, Dynamax, Smitty Forms, Glitch Forms).

#### Mega Evolution & Dynamax
To Mega Evolve or Dynamax a Pokémon you need the **Mega Bracelet** or **Dynamax Band** first. Once obtained, the appropriate form change item will begin appearing in rewards. After using it, the Bracelet or Band **breaks**.

### Glitch Items
Reality-altering items unlocked by collecting Glitch Pieces. See the [Glitch System](/wiki:glitch-system.html) article for full details.

## Party Abilities

A special high-cost item purchasable from the Shop. Applies a selected ability to **all Pokémon in your current party** for the duration of the run. Powerful examples include Speed Boost or Technician for every team member simultaneously.

## Shop Behaviour

The in-run shop refreshes its **entire inventory with each purchase**. This means:
- Buy what you need **before** picking your random item (picking the free item advances to the next battle)
- **FREE items** randomly appear in the shop's inventory — watch for them
- The variety and power of available items increases with your wave count
MD,
            ],

            [
                'slug'     => 'eggs-gacha',
                'title'    => 'Eggs & Gacha',
                'category' => 'Items & Shop',
                'order'    => 2,
                'content'  => <<<MD
# Eggs & the Gacha System

The Egg Gacha is a persistent system for collecting rare Pokémon — separate from wild encounters.

## Egg Vouchers

Eggs are obtained by redeeming **Egg Vouchers** at the Egg Gacha machine (Menu → Egg Gacha). Vouchers are earned from various sources throughout the game.

## Hatching Eggs

- Eggs hatch after a set number of battles — rarer eggs take longer
- Hatched Pokémon are **added to your starter pool**, not your current party
- Hatched Pokémon tend to have **superior IVs** compared to wild catches
- Some Pokémon can **only** be obtained from eggs, making the gacha essential for full collection

## The Three Machines

There are **three gacha machines**, each with different bonuses. Choose the one that best fits your current goals (rare Pokémon, better IVs, specific species).

## Egg Swap

After an egg hatches, you can optionally **swap** the new Pokémon for one of your current party members:
- Your **first (lead) Pokémon cannot be swapped**
- **Legendaries can only swap with legendaries**, Mega-evolvable, or Dynamax-capable Pokémon
- Use this to mid-run upgrade your team with a powerful hatched Pokémon

## Access During Battle

In V2, you can access the Egg Gacha **mid-battle** via the **EGGS** command — no need to wait until the next title screen visit.
MD,
            ],
        ];

        foreach ($articles as $article) {
            WikiArticle::create($article);
        }

        // Items reference article
        WikiArticle::create([
            'slug'     => 'items-reference',
            'title'    => 'Items Reference',
            'category' => 'Items & Shop',
            'order'    => 10,
            'content'  => <<<'MD'
# Items Reference

All items available in PokéVoid, organised by tier. Tiers determine how rare an item is and which tier of Poké Ball is needed to see them in rewards.

Items with a ★ symbol have conditional appearance — they only show up when relevant to your current party or game state.

---

## Common

The most frequently appearing items. Appear from the start of a run.

| Item | Description |
|------|-------------|
| **Glitch Piece** | A mysterious piece of glitch. Collect 2+ to unlock Glitch Items; 5+ for Glitch Form Change items. |
| **Move Upgrade** | Randomly upgrades one of your party's moves — power, priority, effect, chance, multi-hit, and more. |
| **AnyXM (Common)** | Teach a Common-tier move to any Pokémon, ignoring compatibility. |
| **AnyXM (Great)** | Teach a Great-tier move to any Pokémon. |
| **AnyXM (Ultra)** | Teach an Ultra-tier move to any Pokémon. |
| **TM (Common)** | Teach a Common-tier move to a compatible Pokémon. |
| **TM (Great)** | Teach a Great-tier move to a compatible Pokémon. |
| **Any Ability** | Give any ability to any Pokémon. ★ Requires Glitch Pieces. |
| **Any Passive Ability** | Set any ability as a passive on any Pokémon. ★ Requires Glitch Pieces. |
| **Ultra Ball** | Receive Ultra Balls. ★ Only appears if you have fewer than 10. |
| **Super Potion** | Restores HP for one Pokémon. ★ Only appears when party is injured. |

---

## Great

Uncommon items. More powerful consumables and Glitch tools begin appearing here.

| Item | Description |
|------|-------------|
| **Move Upgrade** | Higher-weighted move upgrade rolls. |
| **AnyXM (Great / Ultra)** | Any-compatibility TMs at Great and Ultra tier. |
| **Ability Switcher** | Swap a Pokémon's current ability with one of its other abilities. ★ Requires Glitch Pieces. |
| **Type Switcher** | Changes a Pokémon's primary and secondary types. ★ Requires Glitch Pieces. |
| **Primary Type Switcher** | Changes a Pokémon's primary type only. |
| **Secondary Type Switcher** | Changes a Pokémon's secondary type only. |
| **Stat Switcher** | Swap two of a Pokémon's base stats (e.g. ATK ↔ DEF). ★ Requires Glitch Pieces. |
| **Add Pokémon** | Adds a Pokémon to your party. ★ Requires Glitch Pieces. |
| **Ability Release** | Release a Pokémon to have another adopt its ability. ★ Party size > 1. |
| **Type Release** | Release a Pokémon to have another inherit its types. ★ Party size > 1. |
| **Move Release** | Release a Pokémon to have another inherit its moves. ★ Party size > 1. |
| **Essences** | Collect Essences — use 4x instead of releasing a Pokémon for Release Items, or exchange with the Collector. |
| **Glitch Piece** | Higher drop weight than Common tier. |
| **Temp Stat Booster** | Raises one stat by 1 stage for all party members for 5 battles. |
| **TM (Common / Great)** | Standard compatible TMs. |
| **Soothe Bell** | Increases friendship gain per victory by 50%. |
| **Full Heal** | Cures all status conditions for one Pokémon. ★ Only appears when a Pokémon has a status condition. |
| **Revive** | Revives one fainted Pokémon and restores 50% HP. ★ Only appears when a Pokémon has fainted. |
| **Max Revive** | Revives one fainted Pokémon fully. ★ Only appears when a Pokémon has fainted. |
| **Sacred Ash** | Revives all fainted Pokémon, fully restoring HP. ★ Only when half or more of party is fainted. |
| **Hyper Potion** | Restores a large amount of HP. ★ Appears when HP is low. |
| **Max Potion** | Restores full HP for one Pokémon. ★ Appears when HP is very low. |
| **Full Restore** | Fully heals HP and cures status for one Pokémon. ★ Conditional. |
| **Dire Hit** | Greatly raises the critical-hit ratio for one battle. |
| **Big Nugget** | Grants a large amount of money. ★ Not in final classic wave. |
| **Evolution Item** | Causes certain Pokémon to evolve. Scales with wave index. |
| **Memory Mushroom** | Recall one Pokémon's forgotten level-up move. ★ Only when a Pokémon has forgotten moves. |
| **Tera Shard** | Terastallizes the holder for up to 25 battles (35 with Tera Orb). |
| **Voucher** | Egg voucher. ★ Not in Daily mode; reduced chance after rerolls. |
| **ΩGOLD (Small)** | Grants a small amount of persistent OmegaMoney. ★ Requires Glitch Pieces. |
| **Selectable ΩGOLD** | Choose an ΩGOLD reward amount. |

---

## Ultra

Rare items. Powerful held items, form changes, and advanced Glitch tools.

| Item | Description |
|------|-------------|
| **Move Upgrade** | High-weight upgrade rolls. |
| **AnyXM (Great / Ultra)** | Any-compatibility TMs. |
| **Ability Switcher** | ★ Requires Glitch Pieces. |
| **Any Ability / Any Passive Ability** | ★ Requires Glitch Pieces. |
| **Type Switcher / Stat Switcher** | ★ Requires Glitch Pieces. |
| **Sacrifice Items** | Release a Pokémon to boost another's stats, types, moves, abilities, or passive. |
| **Add Pokémon** | Adds a Pokémon to the party. ★ Conditional on Glitch Pieces and mid-run state. |
| **Primary / Secondary Type Switcher** | |
| **Big Nugget** | Large money reward. |
| **PP Max** | Maximises the PP of one Pokémon's move. |
| **Mint** | Changes a Pokémon's nature (and permanently unlocks it for that starter). |
| **Rare Evolution Item** | Triggers rare evolutions. Scales with wave index. |
| **Form Change Item** | Triggers specific form changes (Mega, Smitty, Glitch). Scales with wave index. |
| **Amulet Coin** | Increases money rewards by 20%. ★ Not in final classic wave. |
| **Eviolite** | Boosts Defense and Sp. Def for Pokémon that can still evolve. ★ Requires unlock. |
| **Species Stat Booster** | Boosts a species-specific stat (e.g. Thick Club for Marowak). |
| **Leek** | Boosts Farfetch'd critical-hit ratio. ★ Only if you have Farfetch'd/Sirfetch'd. |
| **Toxic Orb** | Badly poisons the holder — pairs with Guts, Quick Feet, Poison Heal, etc. ★ Ability/move conditional. |
| **Flame Orb** | Burns the holder — pairs with Guts, Flare Boost, etc. ★ Ability/move conditional. |
| **Reviver Seed** | Automatically revives the holder for 50% HP after fainting from a direct hit. |
| **Attack Type Booster** | Increases the power of moves of a specific type by 20%. |
| **TM (Ultra)** | Compatible Ultra-tier TMs. |
| **Golden Punch** | Grants 50% of direct damage dealt as money. ★ Not in final classic wave. |
| **IV Scanner** | Reveals 2 IVs of a wild Pokémon per stack (best IVs shown first). ★ Not in final classic wave. |
| **EXP. All** | Non-participants receive 20% of a single participant's EXP. ★ Not in final classic wave. |
| **EXP. Balance** | Weighs EXP gains toward lower-level party members. ★ Not in final classic wave. |
| **Tera Orb** | Tera Shards last 10 more battles. Scales with wave index. |
| **Quick Claw** | 10% chance to move first regardless of speed (after priority). |
| **Voucher Plus** | Enhanced egg voucher. ★ Not in Daily mode; reduced chance after rerolls. |
| **Wide Lens** | Increases move accuracy. |
| **ΩGOLD (Medium)** | Selectable mid-tier ΩGOLD reward. ★ Requires Glitch Pieces. |
| **Champion Type Ball** | A Poké Ball matching your Champion's primary type. ★ Conditional on Champion type and ball count. |

---

## Rogue

Very rare items. Mostly passive combat items and powerful glitch tools.

| Item | Description |
|------|-------------|
| **Move Upgrade** | Rogue-tier weighted upgrade rolls. |
| **Rogue Ball** | High catch-rate Poké Ball. ★ Only if you have fewer than 10. |
| **Champion Type Ball (Random)** | A random type ball. ★ Conditional on total ball count. |
| **Relic Gold** | Very large money reward. ★ Not in final classic wave. |
| **Leftovers** | Heals 1/16 of max HP every turn. ★ Rare conditional drop. |
| **Shell Bell** | Heals 1/8 of damage dealt. ★ Rare conditional drop. |
| **DNA Splicers** | Fuse two Pokémon together. ★ Only in non-Spliced modes with unfused party members. |
| **Skill Points** | Gain Skill Points for your Champion's Skill Tree. |
| **Berry Pouch** | 10% chance a used berry won't be consumed. |
| **Grip Claw** | Extends the duration of binding moves. |
| **Scope Lens** | Boosts the holder's critical-hit ratio. |
| **Baton** | Pass effects when switching Pokémon; bypasses traps. |
| **Base Stat Booster** | Boosts a random base stat. |
| **AnyXM (Master)** | Teach a Master-tier move to any Pokémon. ★ Requires Glitch Pieces. |
| **AnyXM (Ultra)** | Any-compatible Ultra TM. ★ Requires Glitch Pieces. |
| **Focus Band** | 10% chance to survive a KO hit with 1 HP. |
| **King's Rock** | 10% chance for attack moves to cause flinching. |
| **Form Change Item** | Triggers form changes. |
| **Any Ability / Any Passive Ability** | ★ Requires Glitch Pieces. |
| **Party Ability** | Applies one ability to **all** current party members for the run. ★ Rare; requires Glitch Pieces. |
| **Primary / Secondary Type Switcher** | |
| **Map** | Lets you choose your destination at crossroads. ★ Chaos modes and before wave 90/100. |
| **Voucher Plus Plus** | Premium egg voucher. ★ Not in Daily/Endless modes; reduced chance after rerolls. |

---

## Master

The rarest in-run items. Game-changing permanent equipment.

| Item | Description |
|------|-------------|
| **Soul Dew** | Increases the influence of a Pokémon's nature on its stats by 10% (additive). |
| **Stat Release** | Release a Pokémon to boost another's specific stat by 15%. |
| **Rarer Candy** | Like Rare Candy but rarer. |
| **Shell Bell** | ★ Higher weight in Chaos modes. |
| **Leftovers** | ★ Higher weight in Chaos modes. |
| **Any Smitty Passive Ability** | Set a Smitty-exclusive passive ability on any Pokémon. ★ Requires Glitch Pieces. |
| **Any Smitty Ability** | Give a Smitty-exclusive ability to any Pokémon. ★ Requires Glitch Pieces. |
| **Candy Jar** | Increases levels gained from Rare Candy items by 1. ★ Not in final classic wave. |
| **ΩGOLD (Large/Huge)** | Selectable large ΩGOLD reward. ★ Requires Glitch Pieces. |
| **Mega Bracelet** | Makes Mega Stones available as future rewards. ★ Only if not already owned. |
| **Dynamax Band** | Makes Max Mushrooms available as future rewards. ★ Only if not already owned. |
| **Master Ball** | The ultimate Poké Ball. ★ Only if you have fewer than 5. |
| **Shiny Charm** | Dramatically increases shiny encounter rate. |
| **Healing Charm** | Increases HP restoration from moves and items by 10% (excludes Revives). |
| **Multi Lens** | Attacks hit one additional time at reduced power per stack. |
| **Voucher Premium** | Premium egg voucher. ★ Not in Daily/Endless modes. |
| **Mini Black Hole** | Every turn, the holder acquires one held item from the foe. ★ Requires The Void unlock. |
| **AnyXM (Luxury)** | Teach a Luxury-tier move to any Pokémon. ★ Requires Glitch Pieces + The Void Overtaken unlock. |
| **AnyXM (Master)** | Teach a Master-tier move to any Pokémon. ★ Requires Glitch Pieces. |
| **Party Ability** | Applies one ability to all current party members. ★ Requires Glitch Pieces. |

---

## ΩITEMS (OmegaItems)

Purchased from the OmegaShop with ΩGOLD. Persist across runs. See [Omega Features](/wiki:omega-features.html) for full details.

| Item | Description |
|------|-------------|
| **Glitch Piece Start Plus/EX/SMITTY** | Start each run with 2/3/4 Glitch Pieces. |
| **Glitch Piece Plus/EX/SMITTY** | Get 3–4 / 4 / 4–5 Glitch Pieces per pickup. |
| **Glitch Piece Max Plus/EX/SMITTY** | Increase maximum Glitch Piece cap by 1/2/3. |
| **Reroll Cost ×1/2/3** | Reduces shop reroll cost. |
| **Show Rewards ×1/2/3** | Shows more items in each shop visit. |
| **Fusion Increase ×1/2/3** | Increases fusion encounter rate. |
| **Catch Rate ×1/2/3** | Improves catch rate. |
| **Trainer Snatch Cost ×1/2/3** | Reduces cost of buying trainer Pokémon. |
| **More Revive ×1/2/3** | More revive items available in shop. |
| **Start Ball ×1/2/3** | Start each run with extra Poké Balls. |
| **Start Money ×1/2/3** | Start each run with ₽1500 / ₽2000 / ₽3000. |
| **Post Battle Money ×1/2/3** | Earn more money after each battle. |
| **Better Luck ×2/3** | Improves luck-based item rolls. |
| **Cheaper Fusions ×1/2/3** | Reduces ΩGOLD cost of using Fusion Pokémon in Journey mode. |
| **Starter Point Limit Inc ×1/2/3** | Increases starter team point allowance. |
| **Longer Tera ×1/2/3** | Tera Shards last longer. |
| **Longer Stat Boosts ×1/2/3** | Stat boosts from items last longer. |
| **Free Reroll** | Grants one free reroll per run. |
| **Metronome Levelup** | Trigger a level-up via Metronome. |
| **New Round Tera** | Gain a Tera Shard at the start of each new biome. |
| **Run Anything ×2** | Removes restrictions on which Pokémon you can run. |
| **Shiny ×1/2/3** | Increases shiny rate. |
| **Transfer Tera** | Carry your Tera type between runs. |
| **Party Ability** | Applies one ability to all current party members (persistent Ω version). |
MD,
        ]);

        $this->command->info('WikiSeeder: items-reference article added.');
    }
}
