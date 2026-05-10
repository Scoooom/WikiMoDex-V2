// PokeAPI setup
var P = null
if (typeof Pokedex !== 'undefined') {
  P = new Pokedex.Pokedex()
} else {
  console.error("PokeAPI didn't connect")
}

// RNG init (required by phaser-rand.js)
special_key = "x0i2O7WRiANTqPmZ"
egg_seed = 1073741824
rdg.init_builder()
rdg.sow()

// Load species data
var starterNames = []
var speciesEnumRaw = []
var species = []
var starter_species = []
var loadedSpecies = false

fetch('pokerogue_data/starterNames.txt').then(r => r.text()).then(t => {
  starterNames = t.split('\n').map(s => s.trim()).filter(Boolean)
  rdg.starterNames = starterNames
  loadedSpecies = true
})
fetch('pokerogue_data/species-enums.txt').then(r => r.text()).then(t => {
  speciesEnumRaw = t.split('\n').map(s => s.trim()).filter(Boolean)
})

// Image caches
var legendaryImgCache = {}
var rusImgCache = {}

function getLegendaryImgSrc(name) {
  var idx = LEGENDARIES.indexOf(name)
  if (idx < 0) return null
  return 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/' + LEGENDARY_IDS[idx].replace('ho-oh','250').replace('zacian','888').replace('zamazenta','889') + '.png'
}

function normaliseSpeciesId(sid) {
  var s = sid.replace(/_/g, '-')
  var regions = ['alola-', 'galar-', 'hisui-', 'paldea-']
  for (var i = 0; i < regions.length; i++) {
    if (s.indexOf(regions[i]) === 0) { s = s.slice(regions[i].length); break }
  }
  var overrides = {
    'nidoran-f':    'nidoran-f',
    'nidoran-m':    'nidoran-m',
    'zygarde':      'zygarde-50',
    'farfetchd':'farfetch-d','sirfetchd':'sirfetch-d','oricorio':'oricorio-baile',
    'indeedee':'indeedee-male','pumpkaboo':'pumpkaboo-average','gourgeist':'gourgeist-average',
    'basculin':'basculin-red-striped','meowstic':'meowstic-male','aegislash':'aegislash-shield',
    'mimikyu':'mimikyu-disguised','minior':'minior-red-meteor','wishiwashi':'wishiwashi-solo',
    'lycanroc':'lycanroc-midday','toxtricity':'toxtricity-amped','eiscue':'eiscue-ice',
    'morpeko':'morpeko-full-belly','urshifu':'urshifu-single-strike','calyrex':'calyrex',
    'zacian':'zacian-hero','zamazenta':'zamazenta-hero','keldeo':'keldeo-ordinary',
    'shaymin':'shaymin-land','giratina':'giratina-altered','tornadus':'tornadus-incarnate',
    'thundurus':'thundurus-incarnate','landorus':'landorus-incarnate','enamorus':'enamorus-incarnate',
    'deoxys':'deoxys-normal','wormadam':'wormadam-plant','darmanitan':'darmanitan-standard',
    'meloetta':'meloetta-aria','eternal-floette':'floette','bloodmoon-ursaluna':'ursaluna-bloodmoon',
  }
  if (overrides[s]) return overrides[s]
  s = s.replace(/-(f|m|male|female)$/, '')
  return s
}

function getDisplayName(sid) {
  return sid.replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

function getRusSpriteUrl(sid) {
  if (rusImgCache[sid]) return rusImgCache[sid]
  if (!P) return null
  var apiId = normaliseSpeciesId(sid)
  P.getPokemon(apiId).then(r => {
    if (r.sprites.front_default) rusImgCache[sid] = r.sprites.front_default
    renderSidebar()
  }).catch(() => {})
  return null
}

// Calendar state
var currentMonth, currentYear, selectedDay = null

function init() {
  var now = new Date()
  currentMonth = now.getMonth() + 1
  currentYear = now.getFullYear()

  document.getElementById('btn-prev-year').onclick  = () => { currentYear--;  renderCal() }
  document.getElementById('btn-prev-month').onclick = () => { currentMonth--; if (currentMonth < 1) { currentMonth = 12; currentYear-- }; renderCal() }
  document.getElementById('btn-today').onclick      = () => { currentMonth = new Date().getMonth()+1; currentYear = new Date().getFullYear(); renderCal() }
  document.getElementById('btn-next-month').onclick = () => { currentMonth++; if (currentMonth > 12) { currentMonth = 1; currentYear++ }; renderCal() }
  document.getElementById('btn-next-year').onclick  = () => { currentYear++;  renderCal() }

  renderCal()
}

var months = ['January','February','March','April','May','June','July','August','September','October','November','December']

function getDaysInMonth(month, year) {
  return new Date(year, month, 0).getDate()
}
function getFirstDayOfWeek(month, year) {
  return new Date(year, month - 1, 1).getDay()
}

function renderCal() {
  document.getElementById('month-label').textContent = months[currentMonth - 1] + ' ' + currentYear

  var grid = document.getElementById('cal-grid')
  grid.innerHTML = ''

  var firstDow = getFirstDayOfWeek(currentMonth, currentYear)
  var daysInMonth = getDaysInMonth(currentMonth, currentYear)
  var today = new Date()

  // Empty cells before first day
  for (var e = 0; e < firstDow; e++) {
    var empty = document.createElement('div')
    empty.className = 'cal-day empty'
    grid.appendChild(empty)
  }

  for (var d = 1; d <= daysInMonth; d++) {
    var cell = document.createElement('div')
    cell.className = 'cal-day'
    var isToday = d === today.getDate() && currentMonth === today.getMonth()+1 && currentYear === today.getFullYear()
    if (isToday) cell.classList.add('today')

    // Build the Day object phaser-rand.js expects
    var dayObj = makeDayObj(d, currentMonth, currentYear)
    setDailyLegend(dayObj)
    var rus = guessPokerus(new Date(dayObj.date.getTime()), false, true)

    var header = document.createElement('div')
    header.className = 'day-header'

    var num = document.createElement('span')
    num.className = 'day-num'
    num.textContent = d

    var legName = document.createElement('span')
    legName.className = 'day-legend-name'
    legName.textContent = dayObj.mon || ''

    header.appendChild(num)
    header.appendChild(legName)
    cell.appendChild(header)

    // Sprite
    var spriteWrap = document.createElement('div')
    spriteWrap.className = 'day-sprite'
    var img = document.createElement('img')
    img.alt = dayObj.mon || ''
    var legIdx = LEGENDARIES.indexOf(dayObj.mon)
    if (legIdx >= 0) {
      img.src = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/' + getLegendaryDexId(LEGENDARY_IDS[legIdx]) + '.png'
    }
    spriteWrap.appendChild(img)
    cell.appendChild(spriteWrap)

    // Click
    ;(function(dayO, rusArr, dateD, isT) {
      cell.addEventListener('click', function() {
        document.querySelectorAll('.cal-day.selected').forEach(el => el.classList.remove('selected'))
        cell.classList.add('selected')
        showSidebar(dayO, rusArr, dateD)
      })
    })(dayObj, rus, d, isToday)

    grid.appendChild(cell)
  }
}

function getLegendaryDexId(apiSlug) {
  // Map API slug to a numeric dex ID for the raw sprite URL
  var slugToId = {
    'mewtwo':150,'lugia':249,'ho-oh':250,'kyogre':382,'groudon':383,'rayquaza':384,
    'dialga':483,'palkia':484,'giratina-altered':487,'arceus':493,'reshiram':643,
    'zekrom':644,'kyurem':646,'xerneas':716,'yveltal':717,'zygarde-50':718,
    'necrozma':800,'zacian':888,'zamazenta':889,'calyrex':898,'koraidon':1007,'miraidon':1008,
    'terapagos':1024,
  }
  return slugToId[apiSlug] || apiSlug
}

function makeDayObj(d, month, year) {
  var date = new Date(year, month - 1, d + (new Date().getHours() - new Date().getUTCHours() < 0 ? 0 : 1),
    new Date().getHours() - new Date().getUTCHours() + (new Date().getHours() < new Date().getUTCHours() ? 24 : 0))
  return {
    year: year, month: month, day: d - 1,
    date: date, mon: '', rus: [],
    getDate: function() { return this.date.getDate() },
    getMonth: function() { return this.date.getMonth() + 1 },
    getYear: function() { return this.date.getFullYear() },
  }
}

function showSidebar(dayObj, rus, d) {
  var sidebar = document.getElementById('sidebar')
  sidebar.classList.remove('hidden')

  var dateStr = months[dayObj.date.getUTCMonth()] + ' ' + dayObj.date.getUTCDate() + ', ' + dayObj.date.getUTCFullYear()
  var h = dayObj.date.getHours()
  var subStr = '(' + (dayObj.date.getMonth()+1) + '/' + dayObj.date.getDate() + '/' + dayObj.date.getFullYear() +
    ' @ ' + (h > 12 ? h - 12 : h) + ':00 ' + (h >= 12 ? 'PM' : 'AM') + ')'

  document.getElementById('sidebar-date').textContent = dateStr
  document.getElementById('sidebar-sub').textContent = subStr
  document.getElementById('sidebar-legend-name').textContent = dayObj.mon || ''

  var legIdx = LEGENDARIES.indexOf(dayObj.mon)
  var spriteEl = document.getElementById('sidebar-sprite')
  if (legIdx >= 0) {
    spriteEl.src = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/' + getLegendaryDexId(LEGENDARY_IDS[legIdx]) + '.png'
    spriteEl.style.display = ''
  } else {
    spriteEl.style.display = 'none'
  }

  var rusGrid = document.getElementById('sidebar-rus-grid')
  rusGrid.innerHTML = ''
  rus.forEach(function(sid) {
    var cell = document.createElement('div')
    cell.className = 'rus-cell'
    var img = document.createElement('img')
    img.src = ''
    img.alt = getDisplayName(sid)
    if (P) {
      P.getPokemon(normaliseSpeciesId(sid)).then(function(r) {
        if (r.sprites.front_default) img.src = r.sprites.front_default
      }).catch(function() {})
    }
    var name = document.createElement('div')
    name.className = 'rus-name'
    name.textContent = getDisplayName(sid)
    cell.appendChild(img)
    cell.appendChild(name)
    rusGrid.appendChild(cell)
  })
}

function renderSidebar() {
  // Called when rus images load — nothing to do since we set src directly
}

init()
