@extends('layouts.app')

@section('content')
<div class="container">
    <div class="section-header mt-2 mb-3">
        <span class="section-title">Frequently asked questions</span>
    </div>

    @php
    $faqs = [
        ['How do I unlock Journey Mode?', '<ol><li>Catch 1 Pokémon — this unlocks the Starter Catch Quest in the Shop</li><li>Go to Title Screen → Shop → the Quest will appear there<br><small>Shop items are random — reroll if you don\'t see it, it will appear eventually</small></li><li>Complete the Quest to unlock Journey Mode</li></ol>', true],
        ['How can I use my account on different devices?', '<ol><li>Click the Floppy Disk icon (top right) and download your .prsv save file</li><li>Send that file to your other device</li><li>Open Menu (ESC key, or Menu button on mobile) → Manage Data → Import Data</li><li>Select the save file you sent yourself</li><li>Confirm overwrite and page refresh — done!</li></ol>', false],
        ['How do I use egg vouchers?', '<ol><li>Open Menu (ESC key or Menu button on mobile)</li><li>Go to Egg Gacha and select it</li><li>Use your egg voucher on any machine — you\'ll get eggs that hatch over time!</li></ol>', false],
        ['The same tutorial keeps popping up, why?', '<ol><li>Tutorials must be <em>completed</em>, not skipped — if you press B instead of finishing them, they\'ll reappear</li><li>Press A or the Right Arrow / Right Gamepad button to work through it fully</li></ol>', false],
        ['I completed a quest — how do I get my Pokémon into its new form?', '<ol><li>Example: you just unlocked Charizard\'s glitch form "Charisand"</li><li>In any future run, if you have Charizard on your team AND 5 Glitch Pieces, you may receive a <em>Glitchi Glitchi Fruit</em> as a reward item</li><li>Use it like any form-change item (e.g. a Fire Stone) and Charizard will evolve to Charisand</li><li>This applies to any glitch form you\'ve unlocked!</li></ol>', false],
        ['I have a perma-item I don\'t want — how do I remove it?', '<p>Go to Menu → Manage Data → Remove Items, then choose the items you want to remove.</p>', false],
        ['Do shinies do anything in PokeVoid?', '<p>No. Unlike PokeRogue, shiny Pokémon don\'t grant any stat advantage. However, catching or hatching a shiny grants extra candy!</p>', false],
        ['Why don\'t shinies give an advantage?', '<p>The developer didn\'t want to force players to use shinies — luck is already strong by default and can be improved with perma-items from the Shop.</p>', false],
        ['My iPhone keeps refreshing randomly — why?', '<p>iPhone struggles with PokeVoid due to the volume of assets loaded into storage. Try closing all other apps, use Chrome instead of Safari, and cross your fingers — it can still crash occasionally even then.</p>', false],
        ['I just got a quest from beating a rival — how do I activate it?', '<p>Unlocked quests appear at Title Screen → Shop. Shop items are random — reroll if you don\'t see it immediately, it will appear eventually.</p>', false],
        ['How do I catch a trainer\'s Pokémon?', '<p>Throw a ball at it like any wild Pokémon — but it costs money (the amount is shown near the Raccoon icon in blue).<br><small>Note: rival Pokémon cannot be caught — their bond can\'t be broken!</small></p>', false],
        ['My game froze / black screen — nothing is working!', '<p>Sounds like you found a game-breaking bug — nice work! Please post in <a href="https://discord.com/channels/1339035316585107546/1351943292467544135">#bugs-or-issues</a> on Discord with a description of what happened, plus a console log screenshot (press CTRL+SHIFT+J in your browser and screenshot the red area).</p><img src="https://i.imgur.com/3r1gYQi.png" alt="Console log example" style="max-width:300px">', false],
    ];
    @endphp

    <div class="faq-list">
        @foreach($faqs as $i => $faq)
        <div class="faq-item {{ $faq[2] ? 'open' : '' }}" id="faq-{{ $i }}">
            <button class="faq-question" onclick="toggleFaq({{ $i }})">
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
</div>

<script>
function toggleFaq(id) {
    const item = document.getElementById('faq-' + id);
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
}
</script>
@endsection
