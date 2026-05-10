function Year() {
  if (TODAY == undefined) return -1;
  return TODAY.getUTCFullYear()
}
function Month() {
  if (TODAY == undefined) return -1;
  return TODAY.getUTCMonth() + 1
}
function Day() {
  if (TODAY == undefined) return -1;
  return TODAY.getUTCDate()
}

function getDay(DATE_y, offset_m, d, offset2) {
  if (offset2 == undefined) offset2 = 0
  //console.log("getDay(" + DATE_y.getTime() + ", " + offset_m + ", " + d + ")")
  var DATE;
  var offset;
  if (d != undefined) {
    DATE = getDayUniversal(offset_m, d, DATE_y)
    offset = getCalendarOffset(DATE)
    //console.log("Date: ", DATE)
    //console.log("Offset: ", offset)
  } else {
    DATE = DATE_y
    offset = offset_m
    //console.log("Date: ", DATE)
    //console.log("Offset: ", offset)
  }
  var DATE2 = new Date(DATE.getTime() + offset*day_time + offset2*day_time)
  return [
    DATE.getDay(),
    getLegendaryGachaSpeciesForTimestamp(DATE2),
    DATE.getMonth(),
    DATE.getDate(),
    DATE.getFullYear(),
    guessPokerus(DATE2),
    getLegendaryGachaCycle(DATE2),
    DATE.getTime(),
    DATE2.getTime()
  ]
}

function getDayUniversal(m, d, y) {
  return new Date(y, m, d)
}
function getDayUniversalWithOffset(m, d, y) {
  var D = new Date(y, m, d)
  var offset = getCalendarOffset(D)
  return new Date(D.getTime() + offset * day_time)
}

function getCalendarOffset(date) {
  return date.getMonth() != date.getUTCMonth() ? 1 : 0;
}

gamelog = []

calendar_box = 80
calendar = []
function generateCalendar(months) {
  gamelog = ""
  var startingDate;
  var loops = 5000
  calendar = []
  var offset = 0
  if (yearmode) {
    startingDate = new Date(displayYear, 0, 1)
    months = 12
  } else {
    if (months == undefined || months < 1) months = 1
    startingDate = new Date(displayYear, displayMonth, 1)
    offset = getCalendarOffset(startingDate)
  }
  gamelog = months + ", " + startingDate.getTime() + ", " + offset
  var startMonth = startingDate.getMonth()
  var current_day = startingDate.getTime()
  var dayCounter = 1
  while (months > 0) {
    while (startingDate.getMonth() == startMonth && loops > 0) {
      calendar.push(getDay(displayYear, displayMonth-1, dayCounter, offset))
      //gamelog += "\n" + calendar[calendar.length - 1].join(",")
      //o = o; // Cause a crash so that I can see output

      // Add one day
      startingDate = new Date(startingDate.getTime() + 86400000)
      dayCounter++
      loops-- // avoid infinite loop crashing the program
    }
    startMonth++
    while (startMonth >= 12) startMonth -= 12
    months--
  }
  return calendar
}

loadedImages = false
legendaryImages = {}

extradays = 0

loadedImage_backupflag = false

function storeLegendary(r) {
  legendaryImages[LEGENDARIES[LEGENDARY_IDS.indexOf(r.name)]] = loadImage(r.sprites.front_default)
}

function prepareCalendar() {
  TODAY = new Date()
  if (override_time) {
    TODAY = new Date(new Date().getTime() + new Date().getTimezoneOffset() * 60000 + test_zone*3600000 - new Date().getHours()*3600000 + test_hour*3600000  - new Date().getMinutes()*60000 + test_mins*60000)
  }
  TODAY = new Date(TODAY.getTime() + extradays*day_time)
  //TODAY = new Date(new Date().getTime() + day_time/24*4)
  if (!noImage && !loadedImages) {
    loadedImages = true
    legendaryImages = {}
    for (var i = 0; i < LEGENDARIES.length; i++) {
      //console.log("P.getPokemon(" + LEGENDARY_IDS[i] + ").then(function(r) {legendaryImages[" + LEGENDARIES[i] + "] = loadImage(r.sprites.front_default)})")
      P.getPokemon(LEGENDARY_IDS[i]).then(storeLegendary)
    }
    generateCalendar()
  } else {
    loadedImages = true
    if (loadedImage_backupflag) {
      // don't spam console please
    } else {
      loadedImage_backupflag = true
      legendaryImages = {}
      //legendaryImages.Mewtwo = loadImage("/Legendaries/mewtwo_sprite.png")
      //legendaryImages.Lugia = loadImage("/Legendaries/lugia_sprite.png")
    }
    generateCalendar()
  }
}