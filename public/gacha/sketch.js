function setup() {
  createCanvas(windowWidth-1, windowHeight-1);
  resetMonth()
  basicFillColor = color(255)
  basicSelectColor = color(218, 236, 247)
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

function pullImage(i) {
  P.getPokemon(LEGENDARY_IDS[i]).then(function(r) {
    console.log(i, r.sprites.front_default)
    LegendaryImages[i] = loadImage(r.sprites.front_default)
  })
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

LOG = []
function draw() {
  background(220);
  // Update sidebar scale
  sidebarSz = lerp(sidebarSz, sidebarTarg, 0.1)
  if (abs(sidebarSz - sidebarTarg) <= 0.001) {
    sidebarSz = sidebarTarg
  }
  // Reset log and cursor
  LOG = []
  cursor('auto')
  // Count number of rows
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
  // Draw calendar
  var lastcol = 0
  var r = 0
  for (var i = 0; i < m.length; i++) {
    if (lastcol > m[i].date.getUTCDay()) {
      r++
    }
    stroke(0)
    lastcol = m[i].date.getUTCDay()
    var X = scalar(lastcol, 7, 4, width - 4 - max(0, sidebarSz), 4)
    var Y = scalar(r, rows, 28, height - 28, 4)
    var W = scalarSize(7, 4, width - 4 - max(0, sidebarSz), 4)
    var H = scalarSize(rows, 28, height - 28, 4)
    fill(255)
    if (m[i].day == day() - 1 && m[i].month == month() && m[i].year == year()) {
      fill(218, 236, 247)
    }
    if (inRectArea(mouseX, mouseY, X, Y, W, H)) {
      //fill(184, 228, 230)
      //fill(218, 236, 247)
    }
    if (m[i].date.getUTCMonth() == 3 && m[i].date.getDate() == 1) {
      // April 1
      X += 2
      Y += 1
    }
    if (true && inRectArea(mouseX, mouseY, X, Y, W, H)) {
      cursor('pointer')
      rect(X-1, Y-1, W+2, H+2)
      rect(X, Y, W, H)
      if (click) {
        //console.log(m[i])
        if (sidebarTarg == 0) {
          // Sidebar is hidden; show current day
          toggleSidebar()
          sidebarCurrentDay = m[i].getDate()
          sidebarCurrentMonth = m[i].getMonth()
          sidebarCurrentYear = m[i].getYear()
          sidebarDisplayDate = months[m[i].date.getUTCMonth()] + " " + m[i].date.getUTCDate() + ", " + m[i].date.getUTCFullYear()
          sidebarDisplaySubtitle = "(" + (m[i].date.getMonth() + 1) + "/" + m[i].date.getDate() + "/" + m[i].date.getFullYear() + " @ " + (m[i].date.getHours() > 12 ? m[i].date.getHours() - 12 : m[i].date.getHours()) + ":00 " + (m[i].date.getHours() >= 12 ? "PM" : "AM") + ")"
          sidebar_leg = m[i].mon
          sidebar_rus = m[i].rus
        } else if (isCurrent(m[i])) {
          // Sidebar is showing this day already; close it
          toggleSidebar()
        } else {
          // Sidebar is showing a different day; change it to this one
          sidebarCurrentDay = m[i].getDate()
          sidebarCurrentMonth = m[i].getMonth()
          sidebarCurrentYear = m[i].getYear()
          sidebarDisplayDate = months[m[i].date.getUTCMonth()] + " " + m[i].date.getUTCDate() + ", " + m[i].date.getUTCFullYear()
          sidebarDisplaySubtitle = "(" + (m[i].date.getMonth() + 1) + "/" + m[i].date.getDate() + "/" + m[i].date.getFullYear() + " @ " + (m[i].date.getHours() > 12 ? m[i].date.getHours() - 12 : m[i].date.getHours()) + ":00 " + (m[i].date.getHours() >= 12 ? "PM" : "AM") + ")"
          sidebar_leg = m[i].mon
          sidebar_rus = m[i].rus
        }
      }
    } else {
      rect(X, Y, W, H)
    }
    if (m[i].date.getUTCMonth() == 3 && m[i].date.getDate() == 1) {
      // April 1
      X -= 2
      Y -= 1
    }
    fill(0)
    stroke(0)
    line(X, Y + 15, X + W, Y + 15)
    noStroke()
    if (m[i].day == day() - 1 && m[i].month == month() && m[i].year == year()) {
      fill(255, 0, 0)
    }
    rectText(m[i].date.getUTCDate(), X, Y, 15, 16)
    if (m[i].mon == undefined || m[i].mon == "") {
      setDailyLegend(m[i])
    }
    if (m[i].rus.length == 0) {
      m[i].rus = guessPokerus(m[i].date, false, true)
    }
    if (LegendaryImages[LEGENDARIES.indexOf(m[i].mon)] != undefined) {
      image(LegendaryImages[LEGENDARIES.indexOf(m[i].mon)], X + W/2 - min(W, H - 15) / 2, Y + 15 + (H-15)/2 - min(W, H - 15) / 2, min(W, H - 15), min(W, H - 15))
    } else {
      //rect(X + W/2 - min(W, H - 15) / 2, Y + 15 + (H-15)/2 - min(W, H - 15) / 2, min(W, H - 15), min(W, H - 15))
    }
    rectText(m[i].mon, X + 15, Y, W - 15, 16)
  }
  // Draw buttons at bottom right
  var btns = 6
  fill(255)
  stroke(0)
  rect(4, height - 24, width - 8 - (btns * 24), 20)
  rectB(width - 24*6, height - 24, 20, 20, ___,
       prevYear)
  rectB(width - 24*5, height - 24, 20, 20, ___,
       prevMonth)
  rectB(width - 24*4, height - 24, 44, 20, ___,
       resetMonth)
  rectB(width - 24*2, height - 24, 20, 20, ___,
       nextMonth)
  rectB(width - 24*1, height - 24, 20, 20, ___,
       nextYear)
  // Draw button labels
  fill(0)
  noStroke()
  rectText(months[currentMonth - 1] + " " + currentYear, 4, height - 24, width - 8 - (btns * 24), 20)
  rectText("<<",    width - 24*6, height - 24, 20, 20)
  rectText("<",     width - 24*5, height - 24, 20, 20)
  rectText("Today", width - 24*4, height - 24, 44, 20)
  rectText(">",     width - 24*2, height - 24, 20, 20)
  rectText(">>",    width - 24*1, height - 24, 20, 20)
  // Draw weekday labels
  for (var i = 0; i < 7; i++) {
    var X = scalar(i, 7, 4, width - 4 - max(0, sidebarSz), 4)
    var W = scalarSize(7, 4, width - 4 - max(0, sidebarSz), 4)
    fill(255)
    stroke(0)
    rect(X, 4, W, 20)
    fill(0)
    noStroke()
    rectText(days[i], X, 4, W, 20)
  }
  // Draw sidebar, if visible
  if (true) {
    fill(255)
    stroke(0)
    push()
    var X = width - sidebarSz + (sidebarSz < 100 ? (1 - sidebarSz/100) : 0)
    var Y = 4
    var W = sidebarDefaultSize - 4
    var H = height - 33
    beginClip()
    Rect(X - 1, Y - 1, W + 2, H + 2)
    endClip()
    Rect(X, Y, W, H)
    fill(0)
    noStroke()
    rectText(sidebarDisplayDate, X + 4, Y + 4, W - 8, 16)
    var W2 = textWidth(sidebarDisplaySubtitle) * 0.9
    rectText(sidebarDisplaySubtitle, X + W / 2 - W2/2, Y + 22, W2, 8)
    fill(255)
    stroke(0)
    Rect(X + 4, Y + 22 + 16, W - 8, sidebarDefaultSize - 12)
    if (LegendaryImages[LEGENDARIES.indexOf(sidebar_leg)] != undefined) {
      var X_1 = X + 4
      var Y_1 = Y + 38
      var W_1 = W - 8
      var H_1 = sidebarDefaultSize - 12
      image(LegendaryImages[LEGENDARIES.indexOf(sidebar_leg)], X_1 + W_1/2 - min(W_1, H_1 - 15) / 2, Y_1 + 15 + (H_1-15)/2 - min(W_1, H_1 - 15) / 2, min(W_1, H_1 - 15), min(W_1, H_1 - 15))
    }
    fill(0)
    var Y2 = Y + 16 + 16 + sidebarDefaultSize
    line(X, Y2, X + W, Y2)
    noStroke()
    // Draw sidebar's text here
    rectText("Today's Pokemon", X, Y2, W, 16)
    text(sidebar_leg == "" ? "[Legendary]" : sidebar_leg, X + 4, Y2 + 26 + 14*0)
    //text("Pokérus will return Soon™", X + 4, Y2 + 60)
    text(sidebar_rus[0] == undefined ? "[Rus 1]" : sidebar_rus[0], X + 4, Y2 + 30 + 14*1)
    text(sidebar_rus[1] == undefined ? "[Rus 2]" : sidebar_rus[1], X + 4, Y2 + 30 + 14*2)
    text(sidebar_rus[2] == undefined ? "[Rus 3]" : sidebar_rus[2], X + 4, Y2 + 30 + 14*3)
    text(sidebar_rus[3] == undefined ? "[Rus 4]" : sidebar_rus[3], X + 4, Y2 + 30 + 14*4)
    text(sidebar_rus[4] == undefined ? "[Rus 5]" : sidebar_rus[4], X + 4, Y2 + 30 + 14*5)
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
