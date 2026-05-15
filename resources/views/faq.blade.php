@extends('layouts.app')
@section('title', 'FAQ — PokéVoid Wiki')

@section('content')
<div class="container">
    <div class="section-header mt-2 mb-3">
        <span class="section-title">Frequently Asked Questions</span>
    </div>

    @php
    $groups = [
        'Getting Started' => [
            [
                'How do I unlock Journey Mode?',
                '<ol>
                    <li>Catch your first Pokémon — this unlocks the <strong>Starter Catch Quest</strong> in the Shop</li>
                    <li>Go to the Title Screen → Shop — the quest will appear there<br><small>Shop items are random, reroll if you don\'t see it right away</small></li>
                    <li>Complete the quest objective (catch 15 total Pokémon)</li>
                    <li>Journey Mode unlocks — you can now pick from your full collection at the start of each run</li>
                </ol>',
                true,
            ],
            [
                'What\'s the difference between Gauntlet and Chaos modes?',
                '<ul>
                    <li><strong>Gauntlet</strong> — wave-based runs with a fixed endpoint. Classic PokéRogue-style structure.</li>
                    <li><strong>Chaos</strong> — path-based runs inspired by Slay the Spire. You choose your route through a branching map instead of fighting waves linearly.</li>
                </ul>
                <p>Each category has variants: Nuzlight, Nuzlocke, Rogue (Draft), and Void (Nightmare) versions exist across most modes.</p>',
                false,
            ],
            [
                'How does the point system work when picking starters?',
                '<p>Each Pokémon has a <strong>point value</strong> based on its power level. Your team can have up to 6 members but the total cost cannot exceed <strong>10 points</strong> by default.</p>
                <p>Omega Items can raise this limit. The <em>Lower Starter Points</em> challenge reduces it further. In Rogue (Draft) mode you receive 2 random Pokémon instead of choosing freely.</p>',
                false,
            ],
            [
                'The same tutorial keeps popping up — why?',
                '<p>Tutorials must be <em>completed</em>, not skipped. If you press B or back out early they\'ll reappear. Press A or the Right Arrow / Right Gamepad button to work through the full tutorial.</p>',
                false,
            ],
        ],
        'Champions & Skill Trees' => [
            [
                'What is a Champion?',
                '<p>Champions are the playable characters of PokéVoid V2. Each has a <strong>type specialisation</strong>, a personal <strong>Skill Tree</strong>, and unique <strong>Signature Pokémon</strong>. Most Champions are locked inside the Void and must be freed before you can play as them.</p>
                <ul>
                    <li><strong>Apollo / Diana</strong> — available from the start; receive two random types each run</li>
                    <li><strong>Type Champions</strong> (Brock, Misty, etc.) — fixed types; can only use Pokémon matching their affinity</li>
                </ul>',
                false,
            ],
            [
                'How do I unlock a locked Champion?',
                '<p>Each Champion requires a specific number of <strong>Essences</strong> to free from the Void. Essences drop from defeating Pokémon of matching types.</p>
                <ul>
                    <li><strong>Brock</strong> — 75 Ground + 75 Rock Essences</li>
                    <li><strong>Misty</strong> — 200 Water Essences</li>
                </ul>
                <p>Once freed, a Champion starts with an empty Pokémon collection and must be built up from scratch.</p>',
                false,
            ],
            [
                'How do I level up my Skill Tree?',
                '<p>Champions gain XP through play. Each level unlocks a new Skill Tree node. Nodes cost <strong>Skill Points (SP)</strong> to activate — deeper nodes also require <strong>Tree Tokens (TK)</strong> to access.</p>
                <p>You can open the Skill Tree <strong>mid-battle</strong> using the SKILL TREE command.</p>',
                false,
            ],
            [
                'What are Alt Builds?',
                '<p>Alt Builds are alternate forms of a Champion\'s Signature Pokémon — completely different stats, abilities, and sometimes types. They\'re unlocked through the Skill Tree and represent some of the most powerful forms in the game.</p>',
                false,
            ],
        ],
        'Glitch & Smitty Forms' => [
            [
                'How do I unlock a Glitch Form?',
                '<ol>
                    <li>Defeat a Rival during a run — this unlocks a <strong>Glitch Quest</strong> for a specific Pokémon</li>
                    <li>Purchase the Quest from the OmegaShop (reroll if it doesn\'t appear)</li>
                    <li>Complete the Quest objective during a run (e.g. defeat 15 Pokémon with Ground moves as Charizard)</li>
                    <li>The Glitch Form is <strong>permanently unlocked</strong> for all future runs</li>
                </ol>',
                false,
            ],
            [
                'I unlocked a Glitch Form — how do I actually use it?',
                '<ol>
                    <li>In any future run, have the base Pokémon on your team and collect <strong>5 Glitch Pieces</strong></li>
                    <li>A <em>Glitch Form Change</em> item (e.g. Glitchi Glitchi Fruit) may appear as a reward</li>
                    <li>Use it like any form-change item — the Pokémon transforms into its Glitch Form</li>
                </ol>',
                false,
            ],
            [
                'How do Smitty Forms work?',
                '<p>Smitty Forms are created by applying <strong>exactly four Smitty Items</strong> to a base Pokémon in the correct combination. Some require a specific base Pokémon; others are universal (any Pokémon works).</p>
                <p>When a new Smitty Form is unlocked, you receive a notification. Enter the form\'s console code to reveal the four required items. The Discord community maintains a list of known combinations.</p>',
                false,
            ],
        ],
        'Eggs & Gacha' => [
            [
                'How do I use Egg Vouchers?',
                '<p>Open <strong>Menu → Egg Gacha</strong>, or use the <strong>EGGS</strong> command during battle (V2). Select a machine and spend your vouchers.</p>
                <ul>
                    <li><strong>Regular Voucher</strong> — ×1 or ×10 pulls (×10 costs 10 vouchers, guarantees 1 Rare egg)</li>
                    <li><strong>Voucher Plus</strong> — ×5 pulls</li>
                    <li><strong>Voucher Premium</strong> — ×10 pulls (guarantees 1 Rare egg)</li>
                    <li><strong>Voucher Gold</strong> — ×25 pulls (guarantees 1 Epic egg)</li>
                </ul>',
                false,
            ],
            [
                'What\'s the difference between the three gacha machines?',
                '<ul>
                    <li><strong>Legendary Up</strong> — doubled Legendary rate (0.78%), features a rotating daily Legendary. Best for targeting specific Legendaries.</li>
                    <li><strong>Egg Move Up</strong> — doubles the Rare Egg Move chance. A Legendary egg has a 1-in-3 shot at the rare move. Best for farming egg move unlocks.</li>
                    <li><strong>Shiny Up</strong> — doubles the shiny rate to 1 in 64. Best for hunting shinies from species you can\'t breed.</li>
                </ul>
                <p>See the <a href="/wiki:eggs-gacha.html">Eggs & Gacha wiki article</a> for full odds.</p>',
                false,
            ],
            [
                'Where do hatched Pokémon go?',
                '<p>Hatched Pokémon are added to your <strong>starter pool</strong>, not your current party. After hatching you can optionally <strong>swap</strong> the new Pokémon into your active team — except your lead Pokémon, which cannot be swapped out.</p>',
                false,
            ],
        ],
        'Omega System & Progression' => [
            [
                'What is ΩGOLD and where do I get it?',
                '<p>ΩGOLD is a <strong>persistent currency</strong> that carries between runs. Earn it from completing Bounties, saving your game (the floppy disk icon rewards ΩGOLD), Smitom interactions, and special run-end rewards.</p>
                <p>Spend it in the OmegaShop on ΩITEMS — permanent upgrades that apply to all future runs.</p>',
                false,
            ],
            [
                'How do I remove a perma-item I don\'t want?',
                '<p>Go to <strong>Menu → Manage Data → Remove Items</strong> and select the items to remove.</p>',
                false,
            ],
            [
                'What are Bounties and how do I activate them?',
                '<p>Bounties are reusable challenges that reward ΩGOLD and ΩITEMS. Activate them by entering a <strong>6-letter code</strong> in the console:</p>
                <ul>
                    <li><strong>Daily Bounty</strong> — automatically placed in the console each day, just activate it</li>
                    <li><strong>Rival / Smitty / Quest Bounties</strong> — codes shared in the Smittyverse Discord</li>
                </ul>',
                false,
            ],
        ],
        'Saving & Technical' => [
            [
                'How do I transfer my save to another device?',
                '<ol>
                    <li>Click the <strong>Floppy Disk icon</strong> (top right) to download your <code>.prsv</code> save file</li>
                    <li>Send the file to your other device</li>
                    <li>On the new device: Menu → Manage Data → Import Data</li>
                    <li>Select the save file and confirm the overwrite</li>
                </ol>',
                false,
            ],
            [
                'My iPhone keeps refreshing / crashing — what can I do?',
                '<p>iPhone struggles with PokéVoid due to the volume of assets. A few things that help:</p>
                <ul>
                    <li>Close all other apps before playing</li>
                    <li>Use <strong>Chrome</strong> instead of Safari</li>
                    <li>Export your save frequently so you don\'t lose progress</li>
                </ul>
                <p>Occasional crashes on iPhone are a known limitation — it can still happen even after all of the above.</p>',
                false,
            ],
            [
                'Do shinies have any advantage in PokéVoid?',
                '<p>No stat advantage — unlike PokéRogue, shinies don\'t boost your Pokémon\'s power. However, catching or hatching a shiny grants <strong>extra candy</strong>.</p>',
                false,
            ],
            [
                'How do I catch a trainer\'s Pokémon?',
                '<p>Throw a Poké Ball at it during battle — it costs money (the amount is shown near the Raccoon icon in blue). The cost scales with the wave but can be reduced with Omega Items.</p>
                <p><small>Rival Pokémon cannot be caught.</small></p>',
                false,
            ],
            [
                'My game froze / black screen — nothing is working!',
                '<p>Sounds like you found a bug — nice work! Please post in <a href="https://discord.com/channels/1339035316585107546/1351943292467544135">#bugs-or-issues</a> on Discord with:</p>
                <ul>
                    <li>A description of what happened</li>
                    <li>A console log screenshot — press <strong>CTRL+SHIFT+J</strong> and screenshot the red area</li>
                </ul>
                <img src="https://i.imgur.com/3r1gYQi.png" alt="Console log example" style="max-width:300px">',
                false,
            ],
        ],
    ];
    @endphp

    @foreach($groups as $groupName => $faqs)
    <div class="faq-group">
        <div class="faq-group-label">{{ $groupName }}</div>
        @foreach($faqs as $faq)
        @php $faqId = \Illuminate\Support\Str::slug($faq[0]); @endphp
        <div class="faq-item {{ $faq[2] ? 'open' : '' }}" id="faq-{{ $faqId }}">
            <button class="faq-question" onclick="toggleFaqById('{{ $faqId }}')">
                {{ $faq[0] }}
                <span class="faq-chevron">▾</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {!! $faq[1] !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

<script>
function toggleFaqById(id) {
    const item = document.getElementById('faq-' + id);
    if (!item) return;
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
}

// Open item if URL hash matches
if (window.location.hash) {
    const id = window.location.hash.slice(1);
    const el = document.getElementById('faq-' + id);
    if (el) {
        el.classList.add('open');
        setTimeout(() => {
            const navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h') || '54');
            const top = el.getBoundingClientRect().top + window.scrollY - navH - 24;
            window.scrollTo({ top, behavior: 'instant' });
        }, 50);
    }
}
</script>
@endsection
