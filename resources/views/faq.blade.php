@extends('layouts.app')

@section('content')
<div class="mt-4">
    <div class="accordion" id="faqAccordion">

        @php
        $faqs = [
            ['How do I unlock Journey Mode?', '<ol><li>Catch 1 pokemon - this unlocks the Starter Catch Quest in the SHOP</li><li>Go to Title Screen -> Shop -> the Quest will appear there<br><small>Note ---> Shop Items are random -- you can reroll if you do not see it (it will appear eventually)</small></li><li>Complete the Quest to get Journey Mode.</li></ol>', true],
            ['How can I use my account on different devices?', '<ol><li>First you must get your Save File by clicking on the Floppy Disk on the top right and downloading that PRSV file (that is your save)</li><li>Then send that file to the device you want to use</li><li>Open the Menu via ESC key or on phone the Menu button</li><li>Go to Manage Data -> Import Data -> Select the Save File you sent to yourself</li><li>Say yes to overwriting your data and refreshing page</li><li>Done!</li></ol>', false],
            ['How do I use egg vouchers?', '<ol><li>Open the Menu via ESC key or on phone the Menu button</li><li>Go to Egg Gacha and select it.</li><li>Use your egg voucher on any of the machines -- bam -- you will get eggs that hatch over time!</li></ol>', false],
            ['The same tutorial keeps popping up, why?', '<ol><li>Tutorials must be completed -- if they are not they will keep showing up</li><li>So press A or Right Arrow Key / Right Gamepad and finish it (rather than pressing B) and you are set!</li></ol>', false],
            ['I completed a quest, how do I get my pokemon to be the new form?', '<ol><li>For example purposes -- say you just unlocked Charizard\'s New Glitch Ground form Charisand</li><li>In any run moving forward --- if you have the Charizard on your team AND 5 GLITCH PIECES, you have a chance to get a glitchi glitchi fruit as a reward item</li><li>By using it (just as you would use any other form change item -- like a fire stone) - the Charizard would change forms / evolve to Charisand!</li><li>This applies to any new form you have obtained!</li></ol>', false],
            ['I have a permaItem I don\'t want, how do I get rid of it?', 'Go to Menu -> Manage Data -> Remove Items - choose the items you dont want', false],
            ['Do shinies do anything in PokeVoid?', 'No. Unlike in PokeRogue shiny Pokemon do not confer any advantage. However catching or hatching a shiny Pokemon grants extra candy!', false],
            ['Why don\'t shiny Pokemon do anything in PokeVoid?', 'I did not want to force players to use shinies -- so luck is already very good by default and can be improved by perma items you get at SHOP', false],
            ['My iPhone keeps refreshing randomly, why is that?', '<p>iPhone in testing can\'t handle PokeVoid well due to all the assets loaded into storage. You can try to close all other apps, use chrome, and pray -- as even then it can still crash just seemingly not as often.</p>', false],
            ['I just got a quest from beating a rival, how do I activate it?', '<p>You can find unlocked quests at Title Screen -> Shop. The Quest will appear there<br><small>Shop Items are random -- you can reroll if you do not see it (it will appear eventually)</small></p>', false],
            ['How do I catch trainer pokemon?', '<p>You can catch a trainer pokemon just as you would regular pokemon -- just throw a ball at it! However it costs money --- the amount is shown near the Raccoon Icon in blue.<br><small>PS: Rival pokemon can not be caught -- their bond can not be broken!</small></p>', false],
            ['My game froze / showing a black screen and nothing I do works!', '<p>Sounds like you found a game breaking bug --- good work! Please send a message over in <a href="https://discord.com/channels/1339035316585107546/1351943292467544135">#bugs-or-issues</a> detailing what happened, as well as console log of error (in your browser press CTRL+SHIFT+J and screenshot the red area).<br><img src="https://i.imgur.com/3r1gYQi.png"></p>', false],
        ];
        @endphp

        @foreach($faqs as $i => $faq)
        <div class="card">
            <div class="card-header" id="heading{{ $i }}">
                <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left" type="button"
                        data-toggle="collapse" data-target="#collapse{{ $i }}"
                        aria-expanded="{{ $faq[2] ? 'true' : 'false' }}"
                        aria-controls="collapse{{ $i }}">
                        {{ $faq[0] }}
                    </button>
                </h2>
            </div>
            <div id="collapse{{ $i }}"
                class="collapse {{ $faq[2] ? 'show' : '' }}"
                aria-labelledby="heading{{ $i }}"
                data-parent="#faqAccordion">
                <div class="card-body">
                    {!! $faq[1] !!}
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection
