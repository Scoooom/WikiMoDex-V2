function now() {
  return new Date();
}

RegionTags = ["ALOLA_", "GALAR_", "HISUI_", "PALDEA_"]

function generateStarters(arrayIn, arrayOut, namesOut, region1, region2) {
  if (arrayIn == undefined) arrayIn = species_costs
  if (arrayIn == undefined) {
    console.error("Species costs aren't loaded yet! Wait for the file to be imported or input a custom file")
    return -1
  }
  if (arrayOut == undefined) arrayOut = rdg.speciesStarters
  if (namesOut == undefined) namesOut = rdg.starterNames
  if (region1 == undefined) region1 = rdg.regional
  if (region2 == undefined) region2 = rdg.rare_regional
  
  for (var i = 0; i < arrayIn.length; i++) {
    var d = species_costs[i].split("[Species.")[1].split("]: ")
    var isRegional = d[0].includes("ALOLA_") || d[0].includes("GALAR_") || d[0].includes("HISUI_") || d[0].includes("PALDEA_")
    var isRareRegional = d[0].includes("HISUI_")
    d[0] = d[0].toLowerCase()
    d[1] = d[1].substring(0, d[1].length - 1) * 1
    arrayOut[d[0]] = d[1]
    //namesOut.push(d[0])
    if (isRegional) region1.push(d[0])
    if (isRareRegional) region2.push(d[0])
  }
  console.debug("Successfully indexed " + namesOut.length + " starters")
  return 0
}

// Imitates the Random Data Generator from Phaser.js, and some utilities from Pokerogue.
rdg = {
  init_builder: function(seeds) {
    if (seeds === undefined) { seeds = [ (now() * random()).toString() ]; }
    this.c = 1 // Internal var
    this.s0 = 0 // Internal var
    this.s1 = 0 // Internal var
    this.s2 = 0 // Internal var
    this.n = 0 // Internal var
    this.signs = [-1, 1]
    
    if (seeds) {
      this.init(seeds)
    }
  },
  // Random number generator
  rnd: function() {
    var t = 2091639 * this.s0 + this.c * 2.3283064365386963e-10;
    this.c = t | 0;
    this.s0 = this.s1;
    this.s1 = this.s2;
    this.s2 = t - this.c;
    
    return this.s2;
  },
  // Creates a seed hash
  hash: function (data)
  {
    var h;
    var n = this.n;
    
    data = data.toString();
    
    for (var i = 0; i < data.length; i++) {
        n += data.charCodeAt(i);
        h = 0.02519603282416938 * n;
        n = h >>> 0;
        h -= n;
        h *= n;
        n = h >>> 0;
        h -= n;
        n += h * 0x100000000;// 2^32
    }

    this.n = n;

    return (n >>> 0) * (2.3283064365386963 * (10**-10));// 2^-32
  },
  // Initialize the random data generator
  init: function(seeds) {
    if (typeof seeds == 'string') {
      this.state(seeds)
    } else {
      this.sow(seeds)
    }
  },
  // Resets the generator's seed
  sow: function(seeds) {
    // Always reset to default seed
    this.n = 0xefc8249d;
    this.s0 = this.hash(' ');
    this.s1 = this.hash(' ');
    this.s2 = this.hash(' ');
    this.c = 1;
    
    if (!seeds) {
      return;
    }
    
    // Apply any seeds
    for (var i = 0; i < seeds.length && (seeds[i] != null); i++)
    {
      var seed = seeds[i];
      
      this.s0 -= this.hash(seed);
      this.s0 += ~~(this.s0 < 0);
      this.s1 -= this.hash(seed);
      this.s1 += ~~(this.s1 < 0);
      this.s2 -= this.hash(seed);
      this.s2 += ~~(this.s2 < 0);
    }
  },
  // Returns a random integer between 0 and 2³².
  integer: function() {
    return this.rnd() * (2**32)
  },
  // Returns a random real number between 0 and 1.
  frac: function() {
    return this.rnd() + (this.rnd() * 0x200000 | 0) * (1.1102230246251565 * (10 ** -16));
  },
  // Returns a random real number between 0 and 2³² by running both of the above functions.
  real: function ()
  {
      return this.integer() + this.frac();
  },
  // Returns a random integer within a range (inclusive).
  integerInRange: function (minV, maxV)
  {
      return floor(this.realInRange(0, maxV - minV + 1) + minV);
  },
  // Identical to the above; I don't know why they made it
  between: function (minV, maxV)
  {
      return floor(this.realInRange(0, maxV - minV + 1) + minV);
  },
  realInRange: function(minV, maxV) {
    return this.frac() * (maxV - minV) + minV
  },
  // Returns a random number between -1 and 1.
  normal: function() {
    return 1 - (2 * this.frac())
  },
  // "Returns a valid RFC4122 version4 ID hex string from https://gist.github.com/1308368"
  uuid: function() {
    var a = '';
    var b = '';
    
    // This FOR block runs all of the necessary code inside of its 3 sections.
    for (b = a = ''; a++ < 36; b += ~a % 5 | a * 3 & 4 ? (a ^ 15 ? 8 ^ this.frac() * (a ^ 20 ? 16 : 4) : 4).toString(16) : '-'){}
    
    return b;
  },
  // Returns a random element from within the given array.
  pick: function(array) {
    return array[this.integerInRange(0, array.length - 1)];
  },
  pick2: function(array, offset) {
    return array[this.integerInRange(0, array.length - 1) + offset];
  },
  // Returns a random sign for multiplication. (Basically, has a random chance to return -1, inverting the value you were multiplying by this one)
  sign: function() {
    return this.pick(this.signs);
  },
  // Picks a random element from an array. Favors entries earlier in the list.
  weightedPick: function(array) {
    return array[~~((this.frac() ** 2) * (array.length - 1) + 0.5)];
  },
  // Returns a random timestamp. If no values are given, returns a random timestamp between 2000 and 2020.
  timestamp: function(minV, maxV) {
    return this.realInRange(min || 946684800000, max || 1577862000000);
  },
  // Returns a random angle (in degrees) from -180 to 180.
  angle: function() {
    return this.integerInRange(-180, 180);
  },
  // Returns a random angle (in radians) from -pi to pi.
  rotation: function() {
    return this.integerInRange(-3.1415926, 3.1415926);
  },
  // Get / set the RNG state. Allows saving the current RNG state.
  state: function(st) {
    if (typeof state === 'string' && state.match(/^!rnd/))
      {
        state = state.split(',');
        this.c = parseFloat(state[1]);
        this.s0 = parseFloat(state[2]);
        this.s1 = parseFloat(state[3]);
        this.s2 = parseFloat(state[4]);
      }
      return [ '!rnd', this.c, this.s0, this.s1, this.s2 ].join(',');
  },
  // Shuffles the given array, using the current seed.
  shuffle: function(array) {
    var len = array.length - 1;
    for (var i = len; i > 0; i--)
    {
      var randomIndex = Math.floor(this.frac() * (i + 1));
      var itemAtIndex = array[randomIndex];

      array[randomIndex] = array[i];
      array[i] = itemAtIndex;
    }
    return array;
  },
  rngCounter: 0,
  rngOffset: 0,
  rngSeedOverride: "",
  rngSeed: "",
  executeWithSeedOffset: function(f, offset, seedOverride) {
    if (!f) {
      return;
    }
    const tempRngCounter = this.rngCounter;
    const tempRngOffset = this.rngOffset;
    const tempRngSeedOverride = this.rngSeedOverride;
    const state = this.state();
    this.sow([ this.shiftCharCodes(seedOverride || this.rngSeed, offset) ]);
    this.rngCounter = 0;
    this.rngOffset = offset;
    this.rngSeedOverride = seedOverride || "";
    var returnValue = f();
    this.state(state);
    this.rngCounter = tempRngCounter;
    this.rngOffset = tempRngOffset;
    this.rngSeedOverride = tempRngSeedOverride;
    return returnValue;
  },
  battle_resetSeed: function(waveIndex){
    const wave = waveIndex || 0;
    this.waveSeed =  this.shiftCharCodes(this.seed, wave);
    this.sow([ this.waveSeed ]);
    console.log("Wave Seed:", this.waveSeed, wave);
    this.rngCounter = 0;
  },
  shiftCharCodes: function(string, shiftCount) {
    if (!shiftCount) {
      shiftCount = 0;
    }

    let newStr = "";

    for (let i = 0; i < string.length; i++) {
      const charCode = string.charCodeAt(i);
      const newCharCode = charCode + shiftCount;
      newStr += String.fromCharCode(newCharCode);
    }

    return newStr;
  },
  randSeedInt: function(range, minV) {
    if (minV == undefined) minV = 0
    if (range <= 1) {
      return minV;
    }
    return this.integerInRange(minV, (range - 1) + minV);
  },
  speciesStarters: {},
  starterNames: [],
  regional: [],
  rare_regional: [],
  tiers: {
    common: 0,
    rare: 1,
    epic: 2,
    legendary: 3,
    manaphy: 4
  },
  /*
  Tiers:
   common
   rare
   epic
   legendary
   manaphy
  Machines:
   move
   legend
   shiny
  */
  guessPokeEgg: function(eggID, machine, storeloc, pityTrigger) {
    var R = this.executeWithSeedOffset(function() {
      var tier_num = floor(eggID / egg_seed)
      var tiers_list = ["common", "rare", "epic", "legendary", "manaphy"]
      var tier = tiers_list[tier_num]
      var speciesOverride = ""
      var pokemonSpecies = ""
      
      //console.log(eggID, machine, tier)

      /**
       * Manaphy eggs have a 1/8 chance of being Manaphy and 7/8 chance of being Phione
       * Legendary eggs pulled from the legendary gacha have a 50% of being converted into
       * the species that was the legendary focus at the time
       */
      if (tier == "manaphy") {
        const rand = randSeedInt(8);
        speciesOverride = (rand ? "phione" : "manaphy");
      } else if (tier == "legendary" && machine == "legend") {
        if (!randSeedInt(2)) {
          speciesOverride = "[Current gacha legendary]";
        }
      }

      if (speciesOverride) {
        pokemonSpecies = speciesOverride;
        console.log(speciesOverride, "Overridden")
        //ret = this.scene.addPlayerPokemon(pokemonSpecies, 1, undefined, undefined, undefined, false);
      } else {
        var minStarterValue;
        var maxStarterValue;

        switch (tier) {
        case "rare":
          minStarterValue = 4;
          maxStarterValue = 5;
          break;
        case "epic":
          minStarterValue = 6;
          maxStarterValue = 7;
          break;
        case "legendary":
          minStarterValue = 8;
          maxStarterValue = 9;
          break;
        default:
          minStarterValue = 1;
          maxStarterValue = 3;
          break;
        }

        var ignoredSpecies = ["phione", "manaphy", "eternatus"];
        /*
        let speciesPool = Object.keys(speciesStarters)
          .filter(s => speciesStarters[s] >= minStarterValue && speciesStarters[s] <= maxStarterValue)
          .map(s => parseInt(s) as Species)
          .filter(s => !pokemonPrevolutions.hasOwnProperty(s) && getPokemonSpecies(s).isObtainable() && ignoredSpecies.indexOf(s) === -1);
        */
        //console.debug("Getting species...")
        var speciesPool = []
        for (var idx = 0; idx < rdg.starterNames.length; idx++) {
          if (rdg.starterNames[idx] != undefined)
          if (rdg.speciesStarters[rdg.starterNames[idx]] != undefined) {
            //console.debug(rdg.speciesStarters)
            //console.debug(rdg.starterNames[idx])
            //console.debug(rdg.speciesStarters[rdg.starterNames[idx]])
            var nm = rdg.starterNames[idx]
            var val = rdg.speciesStarters[nm]
            if (val >= minStarterValue && val <= maxStarterValue && !ignoredSpecies.includes(nm)) {
              speciesPool.push(rdg.starterNames[idx])
              //console.debug("Added!")
            }
          }
        }
        //console.debug(speciesPool)
        var lockedPool = []
        // If this is the 10th egg without unlocking something new, attempt to force it.
        if (pityTrigger) {
          //lockedPool = speciesPool.filter(s => !this.scene.gameData.dexData[s].caughtAttr);
          if (lockedPool.length) { // Skip this if everything is unlocked
            //speciesPool = lockedPool;
          }
        }

        /**
         * Pokemon that are cheaper in their tier get a weight boost. Regionals get a weight penalty
         * 1 cost mons get 2x
         * 2 cost mons get 1.5x
         * 4, 6, 8 cost mons get 1.75x
         * 3, 5, 7, 9 cost mons get 1x
         * Alolan, Galarian, and Paldean mons get 0.5x
         * Hisui mons get 0.125x
         *
         * The total weight is also being calculated EACH time there is an egg hatch instead of being generated once
         * and being the same each time
         */
        // Total weight of pool
        var totalWeight = 0;
        // Weights
        var speciesWeights = [];
        // For each item 'speciesId' in speciesPool
        for (var i = 0; i < speciesPool.length; i++) {
          var speciesId = speciesPool[i]
          // do some math to determine the weights
          let weight = floor((((maxStarterValue - rdg.speciesStarters[speciesId]) / ((maxStarterValue - minStarterValue) + 1)) * 1.5 + 1) * 100);
          const species = speciesId;
          if (rdg.regional.includes(species)) {
            weight = floor(weight / (rdg.rare_regional.includes(species) ? 8 : 2));
          }
          speciesWeights.push(totalWeight + weight);
          totalWeight += weight;
        }

        let species;

        const rand = randSeedInt(totalWeight);
        for (let s = 0; s < speciesWeights.length; s++) {
          if (rand < speciesWeights[s]) {
            species = speciesPool[s];
            break;
          }
        }

        /*
        if (!!this.scene.gameData.dexData[species].caughtAttr) {
          this.scene.gameData.unlockPity[this.tiers[tier]] = Math.min(this.scene.gameData.unlockPity[this.tiers[tier]] + 1, 10);
        } else {
          this.scene.gameData.unlockPity[this.tiers[tier]] = 0;
        }
        */

        //console.log(species, speciesPool)
        pokemonSpecies = species;

        //ret = this.scene.addPlayerPokemon(pokemonSpecies, 1, undefined, undefined, undefined, false);
      }

      /**
       * Non Shiny gacha Pokemon have a 1/128 chance of being shiny
       * Shiny gacha Pokemon have a 1/64 chance of being shiny
       * IVs are rolled twice and the higher of each stat's IV is taken
       * The egg move gacha doubles the rate of rare egg moves but the base rates are
       * Common: 1/48
       * Rare: 1/24
       * Epic: 1/12
       * Legendary: 1/6
       */
      // We'll just skip all this for now
      /*
      ret.trySetShiny(this.egg.gachaType === GachaType.SHINY ? 1024 : 512);
      ret.variant = ret.shiny ? ret.generateVariant() : 0;

      const secondaryIvs = Utils.getIvsFromId(Utils.randSeedInt(4294967295));

      for (let s = 0; s < ret.ivs.length; s++) {
        ret.ivs[s] = max(ret.ivs[s], secondaryIvs[s]);
      }
      */

      var baseChance = machine == "move" ? 3 : 6;
      var eggMoveIndex = randSeedInt(baseChance * (2 ** (3 - tier_num))) ? rdg.randSeedInt(3) : 3;
      
      //console.log(pokemonSpecies, eggMoveIndex)
      if (storeloc != undefined) {
        storeloc.push(pokemonSpecies)
      }
      return pokemonSpecies

    }, eggID, egg_seed.toString());
    return R;
  }
}
  
function getFusedSpeciesName(speciesAName, speciesBName) {
  var fragAPattern = /([a-z]{2}.*?[aeiou(?:y$)\-\']+)(.*?)$/i;
  var fragBPattern = /([a-z]{2}.*?[aeiou(?:y$)\-\'])(.*?)$/i;

  var [ speciesAPrefixMatch, speciesBPrefixMatch ] = [ speciesAName, speciesBName ].map(n => /^(?:[^ ]+) /.exec(n));
  var [ speciesAPrefix, speciesBPrefix ] = [ speciesAPrefixMatch, speciesBPrefixMatch ].map(m => m ? m[0] : "");

  if (speciesAPrefix) {
    speciesAName = speciesAName.slice(speciesAPrefix.length);
  }
  if (speciesBPrefix) {
    speciesBName = speciesBName.slice(speciesBPrefix.length);
  }

  var [ speciesASuffixMatch, speciesBSuffixMatch ] = [ speciesAName, speciesBName ].map(n => / (?:[^ ]+)$/.exec(n));
  var [ speciesASuffix, speciesBSuffix ] = [ speciesASuffixMatch, speciesBSuffixMatch ].map(m => m ? m[0] : "");

  if (speciesASuffix) {
    speciesAName = speciesAName.slice(0, -speciesASuffix.length);
  }
  if (speciesBSuffix) {
    speciesBName = speciesBName.slice(0, -speciesBSuffix.length);
  }

  var splitNameA = speciesAName.split(/ /g);
  var splitNameB = speciesBName.split(/ /g);

  var fragAMatch = fragAPattern.exec(speciesAName);
  var fragBMatch = fragBPattern.exec(speciesBName);

  var fragA;
  var fragB;

  fragA = splitNameA.length === 1 ? (fragAMatch ? fragAMatch[1] : speciesAName) : splitNameA[splitNameA.length - 1];

  if (splitNameB.length === 1) {
    if (fragBMatch) {
      var lastCharA = fragA.slice(fragA.length - 1);
      var prevCharB = fragBMatch[1].slice(fragBMatch.length - 1);
      fragB = (/[\-']/.test(prevCharB) ? prevCharB : "") + fragBMatch[2] || prevCharB;
      if (lastCharA === fragB[0]) {
        if (/[aiu]/.test(lastCharA)) {
          fragB = fragB.slice(1);
        } else {
          var newCharMatch = new RegExp(`[^${lastCharA}]`).exec(fragB);
          if (newCharMatch?.index > 0) {
            fragB = fragB.slice(newCharMatch.index);
          }
        }
      }
    } else {
      fragB = speciesBName;
    }
  } else {
    fragB = splitNameB[splitNameB.length - 1];
  }

  if (splitNameA.length > 1) {
    fragA = `${splitNameA.slice(0, splitNameA.length - 1).join(" ")} ${fragA}`;
  }

  fragB = `${fragB.slice(0, 1).toLowerCase()}${fragB.slice(1)}`;

  return `${speciesAPrefix || speciesBPrefix}${fragA}${fragB}${speciesBSuffix || speciesASuffix}`;
}

function randSeedInt(range, minV) {
  if (minV == undefined) minV = 0
  if (range <= 1) {
    return minV;
  }
  return rdg.integerInRange(minV, (range - 1) + minV);
}

LOG = ""

day_time = 86400000

function getLegendaryGachaSpeciesForTimestamp(timestamp) {
  const legendarySpecies = LEGENDARIES.slice() // duplicates legendary list
  var ret;

  // 86400000 is the number of miliseconds in one day
  const timeDate = new Date(timestamp);
  const dayTimestamp = timeDate.getTime(); // Timestamp of current week
  const offset = floor(floor(dayTimestamp / day_time) / legendarySpecies.length); // Cycle number
  const index = floor(dayTimestamp / day_time) % legendarySpecies.length; // Index within cycle

  rdg.executeWithSeedOffset(function() {
    ret = rdg.shuffle(legendarySpecies)[index];
  }, offset, egg_seed.toString());
  
  LOG = ret;

  return ret;
}
function getLegendaryGachaCycle(timestamp) {
  const legendarySpecies = LEGENDARIES.slice() // duplicates legendary list

  // 86400000 is the number of miliseconds in one day
  const timeDate = new Date(timestamp);
  const dayTimestamp = timeDate.getTime(); // Timestamp of current week
  const offset = floor(floor(dayTimestamp / day_time) / legendarySpecies.length); // Cycle number
  //const index = floor(dayTimestamp / day_time) % legendarySpecies.length; // Index within cycle

  return offset;
}

function randSeedItem(items, add) {
  if (add == undefined) add = 0
  return items.length == 1 ? items[0] : rdg.pick2(items, add)
}

function getGen(species) {
  if (starterNames.includes(species)) {
    var S = starterNames.indexOf(species)
    if (S < starterNames.indexOf('chikorita')) {
      return 1
    }
    if (S < starterNames.indexOf('treecko')) {
      return 2
    }
    if (S < starterNames.indexOf('turtwig')) {
      return 3
    }
    if (S < starterNames.indexOf('snivy')) {
      return 4
    }
    if (S < starterNames.indexOf('chespin')) {
      return 5
    }
    if (S < starterNames.indexOf('rowlet')) {
      return 6
    }
    if (S < starterNames.indexOf('grookey')) {
      return 7
    }
    if (S < starterNames.indexOf('sprigatito')) {
      return 8
    }
    return 9
  }
  return 0
}

genSpeciesMerged = []
genSpeciesNamesMerged = []
for (var i = 0; i < genSpecies.length; i++) {
  genSpeciesMerged = genSpeciesMerged.concat(genSpecies[i])
}
for (var i = 0; i < genSpeciesByName.length; i++) {
  genSpeciesNamesMerged = genSpeciesNamesMerged.concat(genSpeciesByName[i].map(v => "(" + (i+1 < 10 ? "0" : "") + (i+1) + ") " + v))
}
function generateSpecies() {
  var randomSpeciesId = randSeedItem(genSpeciesMerged)
  var speciesID = getPokemonSpecies(randomSpeciesId)
  var pokerusCursor = this.genSpecies[speciesGen(randomSpeciesId) - 1].indexOf(randomSpeciesId)
  return [randomSpeciesId, speciesID, pokerusCursor]
}

function speciesGen(S) {
  for (var i = 0; i < genSpecies.length; i++) {
    if (genSpecies[i].includes(S)) {
      return i + 1;
    }
  }
  return 0;
}

function getPokemonSpecies(speciesID) {
  if (species > 2000) {
    return species.find((s, i) => i === speciesID)
  }
  return species[speciesID - 1]
}

todayOffset = 0
function guessPokerus(date, prnt, doSkip, count) {
  if (!doSkip) date.setUTCHours(0, 0, 0, 0)
  offset = 0
  if (offset == undefined) offset = 0
  if ((new Date().getTimezoneOffset())/60 < 0) {
    //offset = 1
  }
  date = new Date(date.getTime() + offset*day_time)
  if (count == undefined) count = 5
  var pokerusGens = []
  var output = []
  rdg.executeWithSeedOffset(function() {
    for (var c = 0; c < count; c++) {
      let randomSpeciesId, speciesID, pokerusCursor;
      let dupe = false;
      do {
        dupe = false;
        var out = generateSpecies();
        randomSpeciesId = out[0]
        speciesID = out[1]
        pokerusCursor = out[2]
        for (let pc = 0; pc < c; pc++) {
          if (pokerusGens[pc] === speciesGen(speciesID) -1 && pokerusGens[pc] === pokerusCursor || output[pc] == randomSpeciesId) {
            dupe = true;
            break;
          }
        }
      } while (dupe)
        
      pokerusGens.push(speciesGen(speciesID) - 1);
      output.push(randomSpeciesId)
    }
    if (prnt) console.debug(output, pokerusGens)
  }, 0, date.getTime().toString())
  return output
}

function pkrs_getPokemonSpecies(id) {
  if (typeof id == 'object') {
    id = id[floor(random() * id.length)]
  }
  return starterNames[id - 1]
}