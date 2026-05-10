function setup() {
  createCanvas(windowWidth-1, windowHeight-1);
  resetMonth()
  basicFillColor = color(34, 25, 64)
  basicSelectColor = color(43, 32, 80)
  basicFunction = function() {}
  
  rdg.init_builder()
  rdg.sow()
  
  starterNames = loadStrings("pokerogue_data/starterNames.txt")
  speciesEnumRaw = loadStrings("pokerogue_data/species-enums.txt")
  species = []
  starter_species = []
  loadedSpecies = false
  if (pokeapi_loaded) {
    for (var i = 0; i < LEGENDARY_IDS.length; i++) {
      pullImage(i)
    }
  }
}
function reply(r) {
  console.log(r)
}
LegendaryImages = []
RusImages = {}

function pullImage(i) {
  P.getPokemon(LEGENDARY_IDS[i]).then(function(r) {
    console.log(i, r.sprites.front_default)
    LegendaryImages[i] = loadImage(r.sprites.front_default)
  })
}

function normaliseSpeciesId(sid) {
  // Convert underscores to hyphens
  var s = sid.replace(/_/g, '-')

  // Strip regional prefixes — use base mon for default sprite
  var regions = ['alola-', 'galar-', 'hisui-', 'paldea-']
  for (var i = 0; i < regions.length; i++) {
    if (s.indexOf(regions[i]) === 0) {
      s = s.slice(regions[i].length)
      break
    }
  }

  // Explicit overrides for mons that 404 on their bare name in PokeAPI
  // (mons that only exist under a specific form slug)
  var overrides = {
    'farfetchd':       'farfetch-d',
    'sirfetchd':       'sirfetch-d',
    'oricorio':        'oricorio-baile',
    'indeedee':        'indeedee-male',
    'pumpkaboo':       'pumpkaboo-average',
    'gourgeist':       'gourgeist-average',
    'basculin':        'basculin-red-striped',
    'meowstic':        'meowstic-male',
    'aegislash':       'aegislash-shield',
    'mimikyu':         'mimikyu-disguised',
    'minior':          'minior-red-meteor',
    'wishiwashi':      'wishiwashi-solo',
    'lycanroc':        'lycanroc-midday',
    'toxtricity':      'toxtricity-amped',
    'eiscue':          'eiscue-ice',
    'morpeko':         'morpeko-full-belly',
    'urshifu':         'urshifu-single-strike',
    'calyrex':         'calyrex',
    'zacian':          'zacian-hero',
    'zamazenta':       'zamazenta-hero',
    'eternatus':       'eternatus',
    'zarude':          'zarude',
    'keldeo':          'keldeo-ordinary',
    'shaymin':         'shaymin-land',
    'giratina':        'giratina-altered',
    'tornadus':        'tornadus-incarnate',
    'thundurus':       'thundurus-incarnate',
    'landorus':        'landorus-incarnate',
    'enamorus':        'enamorus-incarnate',
    'deoxys':          'deoxys-normal',
    'wormadam':        'wormadam-plant',
    'rotom':           'rotom',
    'castform':        'castform',
    'cherrim':         'cherrim',
    'darmanitan':      'darmanitan-standard',
    'meloetta':        'meloetta-aria',
    'kyurem':          'kyurem',
    'necrozma':        'necrozma',
    'silvally':        'silvally',
    'eternal-floette': 'floette',
    'brute-bonnet':    'brute-bonnet',
    'flutter-mane':    'flutter-mane',
    'great-tusk':      'great-tusk',
    'scream-tail':     'scream-tail',
    'sandy-shocks':    'sandy-shocks',
    'iron-treads':     'iron-treads',
    'iron-bundle':     'iron-bundle',
    'iron-hands':      'iron-hands',
    'iron-jugulis':    'iron-jugulis',
    'iron-moth':       'iron-moth',
    'iron-thorns':     'iron-thorns',
    'iron-valiant':    'iron-valiant',
    'iron-leaves':     'iron-leaves',
    'iron-boulder':    'iron-boulder',
    'iron-crown':      'iron-crown',
    'roaring-moon':    'roaring-moon',
    'walking-wake':    'walking-wake',
    'gouging-fire':    'gouging-fire',
    'raging-bolt':     'raging-bolt',
    'bloodmoon-ursaluna': 'ursaluna-bloodmoon',
    'chi-yu':          'chi-yu',
    'chien-pao':       'chien-pao',
    'ting-lu':         'ting-lu',
    'wo-chien':        'wo-chien',
  }
  if (overrides[s] !== undefined) return overrides[s]

  // Strip gender/form suffixes as last resort
  s = s.replace(/-(f|m|male|female)$/, '')

  return s
}

function getDisplayName(sid) {
  // Pretty display: underscores/hyphens to spaces, title case
  return sid.replace(/[-_]/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase() })
}

function pullRusImage(speciesId) {
  if (RusImages[speciesId] !== undefined) return
  RusImages[speciesId] = null
  if (pokeapi_loaded) {
    var apiId = normaliseSpeciesId(speciesId)
    P.getPokemon(apiId).then(function(r) {
      if (r.sprites.front_default) {
        RusImages[speciesId] = loadImage(r.sprites.front_default)
      }
    }).catch(function() {})
  }
}

function scanDays(c) {
  //if (c == undefined) c = 30
  if (c == undefined) c = LEGENDARIES.length * 2
  var legends = []
  var legendDays = []
  var D = new Date()
  D.setUTCHours(0,0,0)
  D.setUTCDate(day() + (new Date().getHours() < new Date().getUTCHours() ? 1 : 0))
  D = makeDate(year(), month(), day() - 1)
  var startingDay = floor(D.getTime() / 1000)
  var startingMon = getLegendaryGachaSpeciesForTimestamp(D)
  D = new Date(D.getTime() + day_time)
  var tomorrowTime = floor(D.getTime() / 1000)
  var tomorrowMon = getLegendaryGachaSpeciesForTimestamp(D)
  for (var i = 0; i < c; i++) {
    D = new Date(D.getTime() + day_time)
    var L = getLegendaryGachaSpeciesForTimestamp(D)
    if (!legends.includes(L)) {
      //console.log(L)
      legends.push(L)
      legendDays.push([L, floor(D.getTime() / 1000), i])
      //legendDays.push([L, D])
    }
  }
  legends.sort()
  legendDays.sort(function(a,b) {
    return legends.indexOf(a[0]) - legends.indexOf(b[0])
  })
  for (var i = 0; i < legendDays.length; i++) {
    legendDays[i] = "-# " + legendDays[i][0] + (startingMon == legendDays[i][0] || tomorrowMon == legendDays[i][0] ? " returns " + (legendDays[i][2] < 7 ? "" : "on ") : ": ") + "<t:" + legendDays[i][1] + ":" + (legendDays[i][2] < 7 ? "R" : "D") + ">" + (legendDays[i][2] == -1 ? "" : ", at <t:" + legendDays[i][1] + ":" + "t>")
    //legendDays[i] = legendDays[i][0] + ": " + (legendDays[i][1].getMonth()+1) + "/" + legendDays[i][1].getDate() + "/" + legendDays[i][1].getFullYear()
  }
  legendDays.unshift("Tomorrow's Legendary: " + tomorrowMon + " (<t:" + tomorrowTime + ":R>)")
  legendDays.unshift("Today's Legendary: " + startingMon)
  console.log(legendDays.join("\n"))
  return legendDays
}

sidebarSz = 0
sidebarTarg = 0
sidebarDefaultSize = 160

sidebarCurrentDay = 0
sidebarCurrentMonth = 0
sidebarCurrentYear = 0
sidebarDisplayDate = ""
sidebarDisplaySubtitle = ""
sidebar_leg = ""
sidebar_rus = ""

function isCurrent(D) {
  if (D.getDate() == sidebarCurrentDay && D.getMonth() == sidebarCurrentMonth && D.getYear() == sidebarCurrentYear) {
    return true
  }
  return false
}

function toggleSidebar() {
  if (sidebarTarg > 0) {
    sidebarTarg = 0
  } else {
    sidebarTarg = sidebarDefaultSize
  }
}

___ = undefined
DayTime = 1000 * 60 * 60 * 24 // number of ms in one day

function prevMonth() {
  currentMonth--
  if (currentMonth < 1) {
    currentMonth += 12
    currentYear--
  }
  m = generateMonth(currentMonth, currentYear)
}
function prevYear() {
  currentYear--
  m = generateMonth(currentMonth, currentYear)
}
function resetMonth() {
  currentMonth = month()
  currentYear = year()
  m = generateMonth(currentMonth, currentYear)
}
function nextMonth() {
  currentMonth++
  if (currentMonth > 12) {
    currentMonth -= 12
    currentYear++
  }
  m = generateMonth(currentMonth, currentYear)
}
function nextYear() {
  currentYear++
  m = generateMonth(currentMonth, currentYear)
}

function property(obj, prop, ifUndef) {
  if (obj == undefined) {
    return ifUndef
  }
  if (obj[prop] != undefined) {
    return obj[prop]
  }
  return ifUndef
}

function makeDate(Y, M, D) {
  return new Date(Y, M - 1, D + (new Date().getHours() - new Date().getUTCHours() < 0 ? 0 : 1), new Date().getHours() - new Date().getUTCHours() + (new Date().getHours() < new Date().getUTCHours() ? 24 : 0))
}

function Day(data) {
  this.year = property(data, "year", undefined)
  this.month = property(data, "month", undefined)
  this.day = property(data, "day", undefined)
  this.date = new Date(this.year, this.month - 1, this.day + (new Date().getHours() - new Date().getUTCHours() < 0 ? 0 : 1), new Date().getHours() - new Date().getUTCHours() + (new Date().getHours() < new Date().getUTCHours() ? 24 : 0))
  //console.log("Added date: " + (this.date.getUTCMonth()+1) + "/" + this.date.getUTCDate() + "/" + this.date.getUTCFullYear() + "   /   " + (this.date.getMonth()+1) + "/" + this.date.getDate() + "/" + this.date.getFullYear())
  this.mon = ""
  this.rus = []
  
  // Returns properties of the date, in your time zone, as a number
  this.getYear = function() {
    return this.date.getFullYear()
  }
  this.getMonth = function() {
    return this.date.getMonth() + 1
  }
  this.getDate = function() {
    return this.date.getDate()
  }
  this.getDay = function() {
    return this.date.getDay()
  }
  
  // Returns properties of the date as numbers or text
  this.getDisplayYear = function() {
    return this.date.getFullYear()
  }
  this.getDisplayMonth = function() {
    return months[this.date.getMonth()]
  }
  this.getDisplayDate = function() {
    return this.date.getDate()
  }
  this.getDisplayDay = function() {
    return days[this.date.getDay()]
  }
  this.resetToDay = function() {
    this.date = new Date(this.year, this.month - 1, this.day + 1, new Date().getHours() - new Date().getUTCHours() + (new Date().getHours() < new Date().getUTCHours() ? 24 : 0))
    this.updatevars()
  }
  
  return this;
}

months = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
]
days = [
  "Sunday",
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
]

function generateMonth(mo, yr) {
  var M = []
  var addone = false
  for (var i = 0; i < 35; i++) {
    var D = new Day({year: yr, month: mo, day: i})
    //console.log(mo, D.date.getMonth())
    if (mo == 1 + D.date.getUTCMonth()) {
      M.push(D)
      //console.log(D.date)
    } else if (mo < 1 + D.date.getUTCMonth()) {
      //M.push(D)
    }
  }
  return M
}

function scalar(value, count, minV, maxV, spacing) {
  return map(value, 0, count, 0, (maxV-minV) - spacing * (count - 1)) + minV + spacing * (value)
}
function scalarSize(count, minV, maxV, spacing) {
  return map(1, 0, count, 0, (maxV-minV)) - map(0, 0, count, 0, (maxV-minV)) - spacing
}

function rectText(t, x, y, w, h) {
  var TA = textAscent()
  var TD = textDescent()
  var T = textSize()
  if (textWidth(t) > w - 2) {
    push()
    //TA = textAscent(); TD = textDescent()
    textSize((w - 2) / textWidth(t) * T)
    text(t, x + w/2 - textWidth(t)/2, y + h/2 + TA/2 - TD/2)
    pop()
  } else {
    text(t, x + w/2 - textWidth(t)/2, y + h/2 + TA/2 - TD/2)
  }
}

function inArea(X, Y, x1, y1, x2, y2, inclusive) {
  if (inclusive) {
    return X >= x1 && X <= x2 && Y >= y1 && Y <= y2
  }
  return X > x1 && X < x2 && Y > y1 && Y < y2
}
function inRectArea(X, Y, x, y, w, h, inclusive) {
  return inArea(X, Y, x, y, x + w, y + h, inclusive)
}

click = false

// Colour palette matching site
var C_BG       = [19,  16,  34]
var C_CARD     = [34,  25,  64]
var C_CARD2    = [43,  32,  80]
var C_HOVER    = [58,  44,  100]
var C_BORDER   = [53,  40,  96]
var C_ACCENT   = [124, 92,  191]
var C_TEXT     = [237, 230, 255]
var C_MUTED    = [157, 143, 192]
var C_DIM      = [90,  72,  128]
var C_TODAY    = [168, 125, 232]

var CORNER = 8   // cell corner radius
var CORNER_SM = 5 // small corner radius

LOG = []
function draw() {
  background(C_BG[0], C_BG[1], C_BG[2])
  // Update sidebar scale
  sidebarSz = lerp(sidebarSz, sidebarTarg, 0.1)
  if (abs(sidebarSz - sidebarTarg) <= 0.001) {
    sidebarSz = sidebarTarg
  }
  LOG = []
  cursor('auto')

  // Count rows
  rows = 1
  var lastcol = -1
  for (var i = 0; i < m.length; i++) {
    LOG.push(m[i].date)
    if (lastcol > m[i].date.getUTCDay()) {
      rows++
      LOG.push("Newline (Rows: " + rows + ")")
    }
    lastcol = m[i].date.getUTCDay()
  }

  var PAD = 6       // padding between cells
  var TOP = 34      // weekday header height
  var BOT = 36      // bottom bar height
  var SB  = max(0, sidebarSz)

  // Draw calendar cells
  var lastcol = 0
  var r = 0
  for (var i = 0; i < m.length; i++) {
    if (lastcol > m[i].date.getUTCDay()) r++
    lastcol = m[i].date.getUTCDay()

    var X = scalar(lastcol, 7, PAD, width - PAD - SB, PAD)
    var Y = scalar(r, rows, TOP + PAD, height - BOT, PAD)
    var W = scalarSize(7, PAD, width - PAD - SB, PAD)
    var H = scalarSize(rows, TOP + PAD, height - BOT, PAD)

    var isToday = m[i].day == day() - 1 && m[i].month == month() && m[i].year == year()
    var isHover = inRectArea(mouseX, mouseY, X, Y, W, H)

    noStroke()
    if (isToday) {
      fill(C_CARD2[0], C_CARD2[1], C_CARD2[2])
      // Accent left border for today
      fill(C_ACCENT[0], C_ACCENT[1], C_ACCENT[2])
      rect(X, Y, 3, H, CORNER, 0, 0, CORNER)
      fill(C_CARD2[0], C_CARD2[1], C_CARD2[2])
      rect(X + 3, Y, W - 3, H, 0, CORNER, CORNER, 0)
    } else if (isHover) {
      fill(C_HOVER[0], C_HOVER[1], C_HOVER[2])
      rect(X, Y, W, H, CORNER)
      cursor('pointer')
    } else {
      fill(C_CARD[0], C_CARD[1], C_CARD[2])
      rect(X, Y, W, H, CORNER)
    }

    // Click handler
    if (isHover && click) {
      if (sidebarTarg == 0) {
        toggleSidebar()
        sidebarCurrentDay = m[i].getDate()
        sidebarCurrentMonth = m[i].getMonth()
        sidebarCurrentYear = m[i].getYear()
        sidebarDisplayDate = months[m[i].date.getUTCMonth()] + " " + m[i].date.getUTCDate() + ", " + m[i].date.getUTCFullYear()
        sidebarDisplaySubtitle = "(" + (m[i].date.getMonth() + 1) + "/" + m[i].date.getDate() + "/" + m[i].date.getFullYear() + " @ " + (m[i].date.getHours() > 12 ? m[i].date.getHours() - 12 : m[i].date.getHours()) + ":00 " + (m[i].date.getHours() >= 12 ? "PM" : "AM") + ")"
        sidebar_leg = m[i].mon
        sidebar_rus = m[i].rus
        sidebar_rus.forEach(function(id) { pullRusImage(id) })
      } else if (isCurrent(m[i])) {
        toggleSidebar()
      } else {
        sidebarCurrentDay = m[i].getDate()
        sidebarCurrentMonth = m[i].getMonth()
        sidebarCurrentYear = m[i].getYear()
        sidebarDisplayDate = months[m[i].date.getUTCMonth()] + " " + m[i].date.getUTCDate() + ", " + m[i].date.getUTCFullYear()
        sidebarDisplaySubtitle = "(" + (m[i].date.getMonth() + 1) + "/" + m[i].date.getDate() + "/" + m[i].date.getFullYear() + " @ " + (m[i].date.getHours() > 12 ? m[i].date.getHours() - 12 : m[i].date.getHours()) + ":00 " + (m[i].date.getHours() >= 12 ? "PM" : "AM") + ")"
        sidebar_leg = m[i].mon
        sidebar_rus = m[i].rus
        sidebar_rus.forEach(function(id) { pullRusImage(id) })
      }
    }

    // Thin separator line inside cell
    stroke(C_BORDER[0], C_BORDER[1], C_BORDER[2])
    strokeWeight(0.5)
    line(X + CORNER, Y + 16, X + W - CORNER, Y + 16)
    strokeWeight(1)
    noStroke()

    // Date number
    if (isToday) {
      fill(C_TODAY[0], C_TODAY[1], C_TODAY[2])
    } else {
      fill(C_MUTED[0], C_MUTED[1], C_MUTED[2])
    }
    textSize(11)
    rectText(m[i].date.getUTCDate(), X + 2, Y, 18, 16)
    textSize(12)

    // Legendary name
    fill(C_TEXT[0], C_TEXT[1], C_TEXT[2])
    if (m[i].mon == undefined || m[i].mon == "") setDailyLegend(m[i])
    if (m[i].rus.length == 0) m[i].rus = guessPokerus(m[i].date, false, true)

    textSize(10)
    rectText(m[i].mon, X + 20, Y, W - 22, 16)
    textSize(12)

    // Legendary sprite
    if (LegendaryImages[LEGENDARIES.indexOf(m[i].mon)] != undefined) {
      var sprSz = min(W - 4, H - 20)
      image(LegendaryImages[LEGENDARIES.indexOf(m[i].mon)],
        X + W/2 - sprSz/2, Y + 16 + (H - 16)/2 - sprSz/2, sprSz, sprSz)
    }
  }

  // Weekday header row
  for (var i = 0; i < 7; i++) {
    var X = scalar(i, 7, PAD, width - PAD - SB, PAD)
    var W = scalarSize(7, PAD, width - PAD - SB, PAD)
    noStroke()
    fill(C_CARD2[0], C_CARD2[1], C_CARD2[2])
    rect(X, PAD, W, TOP - PAD * 2, CORNER_SM)
    fill(C_MUTED[0], C_MUTED[1], C_MUTED[2])
    textSize(11)
    rectText(days[i], X, PAD, W, TOP - PAD * 2)
    textSize(12)
  }

  // Bottom bar
  var btns = 6
  var barY = height - BOT + PAD
  var barH = BOT - PAD * 2
  noStroke()
  fill(C_CARD[0], C_CARD[1], C_CARD[2])
  rect(PAD, barY, width - PAD * 2 - SB, barH, CORNER_SM)

  // Month label
  fill(C_TEXT[0], C_TEXT[1], C_TEXT[2])
  rectText(months[currentMonth - 1] + " " + currentYear, PAD + 8, barY, width - PAD * 2 - SB - btns * (barH + PAD) - 8, barH)

  // Nav buttons
  var btnW = barH
  var bx = width - PAD - SB - btns * (btnW + PAD)
  var btnDefs = [
    ["<<", prevYear],
    ["<",  prevMonth],
    ["Today", resetMonth],
    [">",  nextMonth],
    [">>", nextYear],
  ]
  // "Today" button is wider
  var btnXs = []
  var cx = width - PAD - SB
  cx -= btnW; btnXs.unshift([cx, btnW, ">>",    nextYear])
  cx -= PAD + btnW; btnXs.unshift([cx, btnW, ">",     nextMonth])
  cx -= PAD + btnW * 2.5; btnXs.unshift([cx, btnW * 2.5, "Today", resetMonth])
  cx -= PAD + btnW; btnXs.unshift([cx, btnW, "<",     prevMonth])
  cx -= PAD + btnW; btnXs.unshift([cx, btnW, "<<",    prevYear])

  for (var b = 0; b < btnXs.length; b++) {
    var bx = btnXs[b][0], bw = btnXs[b][1], blabel = btnXs[b][2], bfn = btnXs[b][3]
    var isHover = inRectArea(mouseX, mouseY, bx, barY, bw, barH)
    noStroke()
    fill(isHover ? C_ACCENT[0] : C_CARD2[0], isHover ? C_ACCENT[1] : C_CARD2[1], isHover ? C_ACCENT[2] : C_CARD2[2])
    rect(bx, barY, bw, barH, CORNER_SM)
    fill(isHover ? 255 : C_MUTED[0], isHover ? 255 : C_MUTED[1], isHover ? 255 : C_MUTED[2])
    textSize(11)
    rectText(blabel, bx, barY, bw, barH)
    textSize(12)
    if (isHover) {
      cursor('pointer')
      if (click) { click = false; bfn() }
    }
  }

  // Sidebar
  if (sidebarSz > 1) {
    var SX = width - sidebarSz + (sidebarSz < 100 ? (1 - sidebarSz/100) : 0)
    var SY = PAD
    var SW = sidebarDefaultSize - PAD
    var SH = height - BOT - PAD

    push()
    beginClip()
    Rect(SX, SY, SW + 2, SH + 2)
    endClip()

    // Sidebar background
    noStroke()
    fill(C_CARD2[0], C_CARD2[1], C_CARD2[2])
    rect(SX, SY, SW, SH, CORNER)

    // Date heading
    fill(C_TEXT[0], C_TEXT[1], C_TEXT[2])
    textSize(12)
    rectText(sidebarDisplayDate, SX + 4, SY + 4, SW - 8, 16)
    fill(C_DIM[0], C_DIM[1], C_DIM[2])
    textSize(9)
    rectText(sidebarDisplaySubtitle, SX + 4, SY + 20, SW - 8, 12)
    textSize(12)

    // Separator
    stroke(C_BORDER[0], C_BORDER[1], C_BORDER[2])
    strokeWeight(0.5)
    line(SX + 8, SY + 34, SX + SW - 8, SY + 34)
    strokeWeight(1)
    noStroke()

    // Legendary sprite box
    var sprBoxY = SY + 38
    var sprBoxH = sidebarDefaultSize - 12
    fill(C_CARD[0], C_CARD[1], C_CARD[2])
    rect(SX + 4, sprBoxY, SW - 8, sprBoxH, CORNER_SM)
    if (LegendaryImages[LEGENDARIES.indexOf(sidebar_leg)] != undefined) {
      var s = min(SW - 16, sprBoxH - 8)
      image(LegendaryImages[LEGENDARIES.indexOf(sidebar_leg)],
        SX + SW/2 - s/2, sprBoxY + sprBoxH/2 - s/2, s, s)
    }

    // Pokérus section
    var Y2 = sprBoxY + sprBoxH + 8
    stroke(C_BORDER[0], C_BORDER[1], C_BORDER[2])
    strokeWeight(0.5)
    line(SX + 8, Y2, SX + SW - 8, Y2)
    strokeWeight(1)
    noStroke()
    fill(C_MUTED[0], C_MUTED[1], C_MUTED[2])
    textSize(10)
    rectText("Pokérus", SX, Y2 + 2, SW, 14)
    textSize(12)

    var cols = 3
    var sprSz = (SW - 8) / cols
    var nameH = 13
    var cellH = sprSz + nameH + 2
    for (var ri = 0; ri < 5; ri++) {
      var rx = SX + 4 + (ri % cols) * sprSz
      var ry = Y2 + 18 + floor(ri / cols) * cellH
      var sid = sidebar_rus[ri]
      if (sid != undefined) {
        pullRusImage(sid)
        // Sprite background
        noStroke()
        fill(C_CARD[0], C_CARD[1], C_CARD[2])
        rect(rx + 1, ry + 1, sprSz - 2, sprSz - 2, CORNER_SM)
        if (RusImages[sid]) {
          image(RusImages[sid], rx + 1, ry + 1, sprSz - 2, sprSz - 2)
        }
        fill(C_DIM[0], C_DIM[1], C_DIM[2])
        textSize(9)
        rectText(getDisplayName(sid), rx, ry + sprSz, sprSz, nameH)
        textSize(12)
      }
    }
    pop()
  }
  click = false
}

function Rect(x, y, w, h) {
  if (w > 0 && h > 0) {
    rect(x, y, w, h)
  }
}
function rectB(x, y, w, h, r, f, fill1, fill2) {
  if (r == undefined) r = 0
  if (f == undefined) f = basicFunction
  if (fill1 == undefined) fill1 = basicFillColor
  if (fill2 == undefined) fill2 = basicSelectColor
  fill(fill1)
  if (inRectArea(mouseX, mouseY, x, y, w, h)) {
    fill(fill2)
    cursor('pointer')
    if (click) {
      click = false
      f()
    }
  }
  Rect(x, y, w, h, r)
}

function mousePressed() {
  click = true
}

function windowResized() {
  resizeCanvas(windowWidth - 1, windowHeight - 1)
}
