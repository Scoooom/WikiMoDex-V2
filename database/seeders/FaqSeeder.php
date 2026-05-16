<?php

namespace Database\Seeders;

use App\Models\FaqEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        FaqEntry::truncate();

        $groups = [
            ['group' => 'Getting Started',          'group_order' => 1],
            ['group' => 'Champions & Skill Trees',  'group_order' => 2],
            ['group' => 'Glitch & Smitty Forms',    'group_order' => 3],
            ['group' => 'Eggs & Gacha',             'group_order' => 4],
            ['group' => 'Omega System & Progression','group_order' => 5],
            ['group' => 'Saving & Technical',       'group_order' => 6],
        ];

        $entries = [

            // ── Getting Started ──────────────────────────────────────────

            [
                'group'            => 'Getting Started',
                'order'            => 1,
                'question'         => 'How do I unlock Journey Mode?',
                'answer_html'      => '<ol>
<li>Catch your first Pokémon — this unlocks the <strong>Starter Catch Quest</strong> in the Shop</li>
<li>Go to the Title Screen → Shop — the quest will appear there<br><small>Shop items are random, reroll if you don\'t see it right away</small></li>
<li>Complete the quest objective (catch 15 total Pokémon)</li>
<li>Journey Mode unlocks — you can now pick from your full collection at the start of each run</li>
</ol>',
                'answer_plain'     => "1. Catch your first Pokémon — this unlocks the Starter Catch Quest in the Shop\n2. Go to Title Screen → Shop (reroll if you don't see it)\n3. Complete the quest: catch 15 total Pokémon\n4. Journey Mode unlocks — pick from your full collection each run",
                'open_by_default'  => true,
            ],

            [
                'group'            => 'Getting Started',
                'order'            => 2,
                'question'         => 'What\'s the difference between Gauntlet and Chaos modes?',
                'answer_html'      => '<ul>
<li><strong>Gauntlet</strong> — wave-based runs with a fixed endpoint. Classic PokéRogue-style structure.</li>
<li><strong>Chaos</strong> — path-based runs inspired by Slay the Spire. You choose your route through a branching map instead of fighting waves linearly.</li>
</ul>
<p>Each category has variants: Nuzlight, Nuzlocke, Rogue (Draft), and Void (Nightmare) versions exist across most modes.</p>',
                'answer_plain'     => "Gauntlet — wave-based runs with a fixed endpoint, classic PokéRogue structure.\nChaos — path-based runs (like Slay the Spire); you choose your route through a branching map.\n\nEach has variants: Nuzlight, Nuzlocke, Rogue (Draft), and Void (Nightmare).",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Getting Started',
                'order'            => 3,
                'question'         => 'How does the point system work when picking starters?',
                'answer_html'      => '<p>Each Pokémon has a <strong>point value</strong> based on its power level. Your team can have up to 6 members but the total cost cannot exceed <strong>10 points</strong> by default.</p>
<p>Omega Items can raise this limit. The <em>Lower Starter Points</em> challenge reduces it further. In Rogue (Draft) mode you receive 2 random Pokémon instead of choosing freely.</p>',
                'answer_plain'     => "Each Pokémon has a point value. Your team (up to 6) can't exceed 10 points total by default.\nOmega Items can raise this limit. In Rogue (Draft) mode you get 2 random Pokémon instead of choosing.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Getting Started',
                'order'            => 4,
                'question'         => 'The same tutorial keeps popping up — why?',
                'answer_html'      => '<p>Tutorials must be <em>completed</em>, not skipped. If you press B or back out early they\'ll reappear. Press A or the Right Arrow / Right Gamepad button to work through the full tutorial.</p>',
                'answer_plain'     => "Tutorials must be completed, not skipped. Pressing B or backing out early causes them to reappear. Press A or Right Arrow to work through the full tutorial.",
                'open_by_default'  => false,
            ],

            // ── Champions & Skill Trees ──────────────────────────────────

            [
                'group'            => 'Champions & Skill Trees',
                'order'            => 1,
                'question'         => 'What is a Champion?',
                'answer_html'      => '<p>Champions are the playable characters of PokéVoid V2. Each has a <strong>type specialisation</strong>, a personal <strong>Skill Tree</strong>, and unique <strong>Signature Pokémon</strong>. Most Champions are locked inside the Void and must be freed before you can play as them.</p>
<ul>
<li><strong>Apollo / Diana</strong> — available from the start; receive two random types each run</li>
<li><strong>Type Champions</strong> (Brock, Misty, etc.) — fixed types; can only use Pokémon matching their affinity</li>
</ul>',
                'answer_plain'     => "Champions are the playable characters of PokéVoid V2. Each has a type specialisation, Skill Tree, and Signature Pokémon.\n\nApollo / Diana — available from the start, get two random types each run.\nType Champions (Brock, Misty, etc.) — fixed types, can only use Pokémon matching their affinity.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Champions & Skill Trees',
                'order'            => 2,
                'question'         => 'How do I unlock a locked Champion?',
                'answer_html'      => '<p>Each Champion requires a specific number of <strong>Essences</strong> to free from the Void. Essences drop from defeating Pokémon of matching types.</p>
<ul>
<li><strong>Brock</strong> — 75 Ground + 75 Rock Essences</li>
<li><strong>Misty</strong> — 200 Water Essences</li>
</ul>
<p>Once freed, a Champion starts with an empty Pokémon collection and must be built up from scratch.</p>',
                'answer_plain'     => "Champions require Essences to unlock — they drop from defeating Pokémon of matching types.\n\nBrock — 75 Ground + 75 Rock Essences\nMisty — 200 Water Essences\n\nFreed Champions start with an empty Pokémon collection.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Champions & Skill Trees',
                'order'            => 3,
                'question'         => 'How do I level up my Skill Tree?',
                'answer_html'      => '<p>Champions gain XP through play. Each level unlocks a new Skill Tree node. Nodes cost <strong>Skill Points (SP)</strong> to activate — deeper nodes also require <strong>Tree Tokens (TK)</strong> to access.</p>
<p>You can open the Skill Tree <strong>mid-battle</strong> using the SKILL TREE command.</p>',
                'answer_plain'     => "Champions gain XP through play. Each level unlocks a Skill Tree node.\nNodes cost Skill Points (SP) to activate; deeper nodes also need Tree Tokens (TK).\nAccess the Skill Tree mid-battle via the SKILL TREE command.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Champions & Skill Trees',
                'order'            => 4,
                'question'         => 'What are Alt Builds?',
                'answer_html'      => '<p>Alt Builds are alternate forms of a Champion\'s Signature Pokémon — completely different stats, abilities, and sometimes types. They\'re unlocked through the Skill Tree and represent some of the most powerful forms in the game.</p>',
                'answer_plain'     => "Alt Builds are alternate forms of a Champion's Signature Pokémon with different stats, abilities, and sometimes types. Unlocked through the Skill Tree — some of the strongest forms in the game.",
                'open_by_default'  => false,
            ],

            // ── Glitch & Smitty Forms ────────────────────────────────────

            [
                'group'            => 'Glitch & Smitty Forms',
                'order'            => 1,
                'question'         => 'How do I unlock a Glitch Form?',
                'answer_html'      => '<ol>
<li>Defeat a Rival during a run — this unlocks a <strong>Glitch Quest</strong> for a specific Pokémon</li>
<li>Purchase the Quest from the OmegaShop (reroll if it doesn\'t appear)</li>
<li>Complete the Quest objective during a run (e.g. defeat 15 Pokémon with Ground moves as Charizard)</li>
<li>The Glitch Form is <strong>permanently unlocked</strong> for all future runs</li>
</ol>',
                'answer_plain'     => "1. Defeat a Rival — unlocks a Glitch Quest for a specific Pokémon\n2. Buy the Quest from the OmegaShop (reroll if needed)\n3. Complete the Quest objective during a run\n4. The Glitch Form is permanently unlocked for all future runs",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Glitch & Smitty Forms',
                'order'            => 2,
                'question'         => 'I unlocked a Glitch Form — how do I actually use it?',
                'answer_html'      => '<ol>
<li>In any future run, have the base Pokémon on your team and collect <strong>5 Glitch Pieces</strong></li>
<li>A <em>Glitch Form Change</em> item (e.g. Glitchi Glitchi Fruit) may appear as a reward</li>
<li>Use it like any form-change item — the Pokémon transforms into its Glitch Form</li>
</ol>',
                'answer_plain'     => "1. Have the base Pokémon on your team and collect 5 Glitch Pieces\n2. A Glitch Form Change item (e.g. Glitchi Glitchi Fruit) may appear as a reward\n3. Use it like any form-change item to transform the Pokémon",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Glitch & Smitty Forms',
                'order'            => 3,
                'question'         => 'How do Smitty Forms work?',
                'answer_html'      => '<p>Smitty Forms are created by applying <strong>exactly four Smitty Items</strong> to a base Pokémon in the correct combination. Some require a specific base Pokémon; others are universal (any Pokémon works).</p>
<p>When a new Smitty Form is unlocked, you receive a notification. Enter the form\'s console code to reveal the four required items. The Discord community maintains a list of known combinations.</p>',
                'answer_plain'     => "Apply exactly four Smitty Items in the correct combination to create a Smitty Form. Some need a specific base Pokémon; others work with any.\nWhen unlocked you get a notification — enter the console code to reveal the recipe. Known combinations are listed in the Discord.",
                'open_by_default'  => false,
            ],

            // ── Eggs & Gacha ─────────────────────────────────────────────

            [
                'group'            => 'Eggs & Gacha',
                'order'            => 1,
                'question'         => 'How do I use Egg Vouchers?',
                'answer_html'      => '<p>Open <strong>Menu → Egg Gacha</strong>, or use the <strong>EGGS</strong> command during battle. Select a machine and spend your vouchers.</p>
<ul>
<li><strong>Regular Voucher</strong> — ×1 or ×10 pulls (×10 costs 10 vouchers, guarantees 1 Rare egg)</li>
<li><strong>Voucher Plus</strong> — ×5 pulls</li>
<li><strong>Voucher Premium</strong> — ×10 pulls (guarantees 1 Rare egg)</li>
<li><strong>Voucher Gold</strong> — ×25 pulls (guarantees 1 Epic egg)</li>
</ul>',
                'answer_plain'     => "Open Menu → Egg Gacha, or use the EGGS command mid-battle.\n\nRegular Voucher — ×1 or ×10 pulls (×10 guarantees 1 Rare egg)\nVoucher Plus — ×5 pulls\nVoucher Premium — ×10 pulls (guarantees 1 Rare egg)\nVoucher Gold — ×25 pulls (guarantees 1 Epic egg)",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Eggs & Gacha',
                'order'            => 2,
                'question'         => 'What\'s the difference between the three gacha machines?',
                'answer_html'      => '<ul>
<li><strong>Legendary Up</strong> — doubled Legendary rate (0.78%), features a rotating daily Legendary. Best for targeting specific Legendaries.</li>
<li><strong>Egg Move Up</strong> — doubles the Rare Egg Move chance. A Legendary egg has a 1-in-3 shot at the rare move. Best for farming egg move unlocks.</li>
<li><strong>Shiny Up</strong> — doubles the shiny rate to 1 in 64. Best for hunting shinies from species you can\'t breed.</li>
</ul>
<p>See the <a href="/wiki:eggs-gacha.html">Eggs & Gacha wiki article</a> for full odds.</p>',
                'answer_plain'     => "Legendary Up — doubled Legendary rate (0.78%), rotating daily featured Legendary.\nEgg Move Up — doubles Rare Egg Move chance (1-in-3 on a Legendary egg).\nShiny Up — doubles shiny rate to 1 in 64.\n\nFull odds: /wiki:eggs-gacha.html",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Eggs & Gacha',
                'order'            => 3,
                'question'         => 'Where do hatched Pokémon go?',
                'answer_html'      => '<p>Hatched Pokémon are added to your <strong>starter pool</strong>, not your current party. After hatching you can optionally <strong>swap</strong> the new Pokémon into your active team — except your lead Pokémon, which cannot be swapped out.</p>',
                'answer_plain'     => "Hatched Pokémon go to your starter pool, not your current party.\nAfter hatching you can optionally swap the new Pokémon in — except your lead Pokémon, which can't be replaced.",
                'open_by_default'  => false,
            ],

            // ── Omega System & Progression ───────────────────────────────

            [
                'group'            => 'Omega System & Progression',
                'order'            => 1,
                'question'         => 'What is ΩGOLD and where do I get it?',
                'answer_html'      => '<p>ΩGOLD is a <strong>persistent currency</strong> that carries between runs. Earn it from completing Bounties, saving your game (the floppy disk icon rewards ΩGOLD), Smitom interactions, and special run-end rewards.</p>
<p>Spend it in the OmegaShop on ΩITEMS — permanent upgrades that apply to all future runs.</p>',
                'answer_plain'     => "ΩGOLD is a persistent currency that carries between runs.\nEarn it from: completing Bounties, saving your game (floppy disk icon), Smitom interactions, and run-end rewards.\nSpend it in the OmegaShop on permanent ΩITEM upgrades.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Omega System & Progression',
                'order'            => 2,
                'question'         => 'How do I remove a perma-item I don\'t want?',
                'answer_html'      => '<p>Go to <strong>Menu → Manage Data → Remove Items</strong> and select the items to remove.</p>',
                'answer_plain'     => "Menu → Manage Data → Remove Items, then select the items to remove.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Omega System & Progression',
                'order'            => 3,
                'question'         => 'What are Bounties and how do I activate them?',
                'answer_html'      => '<p>Bounties are reusable challenges that reward ΩGOLD and ΩITEMS. Activate them by entering a <strong>6-letter code</strong> in the console:</p>
<ul>
<li><strong>Daily Bounty</strong> — automatically placed in the console each day, just activate it</li>
<li><strong>Rival / Smitty / Quest Bounties</strong> — codes shared in the Smittyverse Discord</li>
</ul>',
                'answer_plain'     => "Bounties are reusable challenges that reward ΩGOLD and ΩITEMS. Activate with a 6-letter console code.\n\nDaily Bounty — auto-placed in the console each day\nRival / Smitty / Quest Bounties — codes shared in the Smittyverse Discord",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Omega System & Progression',
                'order'            => 4,
                'question'         => 'I just got a quest from beating a rival — how do I activate it?',
                'answer_html'      => '<p>Unlocked quests appear at <strong>Title Screen → Shop</strong>. Shop items are random — reroll if you don\'t see it immediately, it will appear eventually.</p>',
                'answer_plain'     => "Go to Title Screen → Shop. Shop items are random — reroll if you don't see it. It will appear eventually.",
                'open_by_default'  => false,
            ],

            // ── Saving & Technical ───────────────────────────────────────

            [
                'group'            => 'Saving & Technical',
                'order'            => 1,
                'question'         => 'How do I transfer my save to another device?',
                'answer_html'      => '<ol>
<li>Click the <strong>Floppy Disk icon</strong> (top right) to download your <code>.prsv</code> save file</li>
<li>Send the file to your other device</li>
<li>On the new device: Menu → Manage Data → Import Data</li>
<li>Select the save file and confirm the overwrite</li>
</ol>',
                'answer_plain'     => "1. Click the Floppy Disk icon (top right) to download your .prsv save\n2. Send it to your other device\n3. Menu → Manage Data → Import Data\n4. Select the file and confirm",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Saving & Technical',
                'order'            => 2,
                'question'         => 'My iPhone keeps refreshing / crashing — what can I do?',
                'answer_html'      => '<p>iPhone struggles with PokéVoid due to the volume of assets. A few things that help:</p>
<ul>
<li>Close all other apps before playing</li>
<li>Use <strong>Chrome</strong> instead of Safari</li>
<li>Export your save frequently so you don\'t lose progress</li>
</ul>
<p>Occasional crashes on iPhone are a known limitation.</p>',
                'answer_plain'     => "iPhone struggles with PokéVoid's asset volume. Tips:\n• Close all other apps\n• Use Chrome instead of Safari\n• Export your save frequently\n\nOccasional crashes are a known limitation even after all of the above.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Saving & Technical',
                'order'            => 3,
                'question'         => 'Do shinies have any advantage in PokéVoid?',
                'answer_html'      => '<p>No stat advantage — unlike PokéRogue, shinies don\'t boost your Pokémon\'s power. However, catching or hatching a shiny grants <strong>extra candy</strong>.</p>',
                'answer_plain'     => "No stat advantage. Unlike PokéRogue, shinies don't boost power. Catching or hatching a shiny does grant extra candy.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Saving & Technical',
                'order'            => 4,
                'question'         => 'How do I catch a trainer\'s Pokémon?',
                'answer_html'      => '<p>Throw a Poké Ball at it during battle — it costs money (shown near the Raccoon icon in blue). The cost scales with the wave but can be reduced with Omega Items.</p>
<p><small>Rival Pokémon cannot be caught.</small></p>',
                'answer_plain'     => "Throw a Poké Ball at the trainer's Pokémon during battle. It costs money (shown near the Raccoon icon). Cost scales with the wave, reducible with Omega Items.\n\nNote: rival Pokémon cannot be caught.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Saving & Technical',
                'order'            => 4,
                'question'         => 'How do I get a Trainer Card?',
                'answer_html'      => '<p>Log in with Discord on <a href="https://void.scooom.xyz">void.scooom.xyz</a>, then go to your profile from the nav bar. In the sidebar, find the <strong>Save File</strong> section — select your save file and click <strong>Upload Save File</strong>.</p><p>Once uploaded, buttons will appear to view your <strong>Trainer Card</strong> or copy a <strong>Share Image</strong> to your clipboard.</p>',
                'answer_plain'     => "Log in with Discord on <https://void.scooom.xyz> and go to your profile from the nav bar. In the sidebar, find the Save File section — select your save file and click Upload Save File. Once uploaded, you can view your Trainer Card or copy a Share Image to your clipboard.",
                'open_by_default'  => false,
            ],

            [
                'group'            => 'Saving & Technical',
                'order'            => 5,
                'question'         => 'My game froze / black screen — nothing is working!',
                'answer_html'      => '<p>Sounds like you found a bug — nice work! Please post in <a href="https://discord.com/channels/1339035316585107546/1351943292467544135">#bugs-or-issues</a> on Discord with:</p>
<ul>
<li>A description of what happened</li>
<li>A console log screenshot — press <strong>CTRL+SHIFT+J</strong> and screenshot the red area</li>
</ul>
<img src="https://i.imgur.com/3r1gYQi.png" alt="Console log example" style="max-width:300px">',
                'answer_plain'     => "Found a bug — nice work! Post in #bugs-or-issues on Discord:\nhttps://discord.com/channels/1339035316585107546/1351943292467544135\n\nInclude: a description of what happened + a console log screenshot (CTRL+SHIFT+J, screenshot the red area).",
                'open_by_default'  => false,
            ],
        ];

        // Attach group_order from the groups map
        $groupOrders = collect($groups)->pluck('group_order', 'group');

        foreach ($entries as $entry) {
            $entry['group_order'] = $groupOrders[$entry['group']];
            $entry['slug']        = FaqEntry::slugFor($entry['question']);
            FaqEntry::create($entry);
        }

        $this->command->info('FaqSeeder: ' . count($entries) . ' FAQ entries seeded.');
    }
}
