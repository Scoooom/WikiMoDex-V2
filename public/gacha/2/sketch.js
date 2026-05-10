function setup() {
  createCanvas(windowWidth - 1, windowHeight - 1);
  angleMode(DEGREES)
  export_canvas = createGraphics(700, 750)
  image_unavailable = createGraphics(100, 100)
  
  specialMsg = "New calendar: https://editor.p5js.org/RedstonewolfX/sketches/jF3kUNbY8"
  
  allowDownload = true
  
  showMiniSprites = true
  showall = false || noImage
  showCalendarCycles = false
  parallelSprites = true
  
  if (getItem("maintenance") == "true") {
    maintenanceMode = true
    console.debug("Enabled maintenance mode due to personal settings")
  }
  
  nospam = true
  
  if (maintenanceMode) {
    nospam = true
    showall = false
    noImage = true
  }
  
  showBottomBar = true
  autohideBar = false
  
  rdg.init_builder()
  rdg.sow()
  calendarW = 0
  calendarH = 0
  calendarRows = 6
  image_unavailable.background(220)
  image_unavailable2 = image_unavailable.get()
  
  pokeballImage = undefined
  pokeballLoadFunc = function() {
    image_unavailable.image(pokeballImage, 50 - pokeballImage.width/2, 50 - pokeballImage.height/2)
    image_unavailable2 = image_unavailable.get(25, 25, 50, 50)
  }
  if (!noImage) {
    P.getItem("poke-ball").then(function(r) {
      console.log(r)
      image_unavailable.clear()
      pokeballImage = loadImage(r.sprites.default, pokeballLoadFunc)
    })
  }
  
  override_time = false
  test_zone = 4 // Treat the calendar as being in this time zone
  test_hour = 18
  test_mins = 21
  
  speciesEnumRaw = loadStrings("species-enums.txt")
  species = []
  starter_species = []
  starterNames = loadStrings("starterNames.txt")
  loadedSpecies = false
  
  displayMonth = now().getMonth() + 1
  displayYear = 1900 + now().getYear()
  
  starterNames = loadStrings("starterNames.txt")
  mobileHelper = createSelect()
  
  imagecache = []
  imagecache_labels = []
  imagelimit = 155
  
  todayview = false
  
  if (deviceOrientation == "portrait" || deviceOrientation == "landscape") {
    mh = mobileHelper
    mh.option("Mobile helper", 0)
    mh.position(5, 5)
    mh.option("Settings", 11)
    mh.option("Save screenshot", 1)
    //mh.option("Today View", 10)
    mh.option("Toggle Pokérus", 8)
    mh.option("Jump to today", 2)
    mh.option("Next month", 3)
    mh.option("Previous month", 4)
    mh.option("Next year", 5)
    mh.option("Previous year", 6)
    mh.option("Toggle year mode", 7)
    //mh.option("Test Pokerus", 9)
    for (var i = 0; i < months.length; i++) {
      //mh.option("Jump to " + months[i] + " ", months[i])
    }
    update_picker = function() {
      var sel = mh.value()
      //console.log(sel)
      switch (sel) {
        case "1":
          takeScreenshot = true;
          break;
        case "2":
          displayMonth = now().getMonth() + 1
          displayYear = 1900 + now().getYear()
          generateCalendar()
          break;
        case "3":
          displayMonth++;
          while (displayMonth >= 12) {
            displayMonth -= 12
            displayYear++;
          }
          //console.log(displayMonth, displayYear)
          generateCalendar();
          break;
        case "4":
          displayMonth--;
          while (displayMonth <= 0) {
            displayMonth += 12
            displayYear--;
          }
          generateCalendar();
          break;
        case "5":
          displayYear++;
          generateCalendar();
          break;
        case "6":
          displayYear--;
          generateCalendar();
          break;
        case "7":
          yearmode = !yearmode
          wasYearmode = yearmode
          mh.enable("Next month")
          mh.enable("Previous month")
          mh.enable("Toggle Pokérus")
          if (yearmode) {
            mh.disable("Next month")
            mh.disable("Previous month")
            mh.disable("Toggle Pokérus")
            pkrs = false
          }
          generateCalendar()
          break;
        case "8":
          pkrs = !pkrs
          break;
        case "9":
          guessToday()
          break;
        case 10:
          todayview = !todayview
          break;
        case 11:
          settingsWindow = !settingsWindow
          break;
        case "January":
        case "February":
        case "March":
        case "April":
        case "May":
        case "June":
        case "July":
        case "August":
        case "September":
        case "October":
        case "November":
        case "December":
          displayMonth = months.indexOf(sel)
          updateCalendar()
      }
      mh.selected(0)
      //showBottomBar = false
    }
  } else {
    mobileHelper.hide()
  }
  todayLegend = getLegendaryGachaSpeciesForTimestamp(new Date())
  tomorrowLegend = getLegendaryGachaSpeciesForTimestamp(new Date().getTime() + day_time)
  todayRus = guessToday()
  tomorrowRus = undefined
  todayIndex = -1
  todayOffset = 0
}

pkrs = true

function generateUpcomingBlurb(d) {
  if (d == undefined) d = 30
  var out = []
  var cdate = new Date()
  cdate.setUTCHours(0,0,0,0)
  var cmonth = cdate.getUTCMonth()
  for (var i = 0; i < d; i++) {
    if (cmonth != cdate.getUTCMonth()) {
      out.push("")
      cmonth = cdate.getUTCMonth()
    }
    out.push("<t:" + floor(cdate.getTime()/1000) + ">: " + getLegendaryGachaSpeciesForTimestamp(cdate))
    cdate = new Date(cdate.getTime() + day_time)
  }
  out = out.join("\n")
  return out;
}
function generateAllBlurb() {
  var out = []
  var cdate = new Date()
  var todayDate = new Date()
  var t = getLegendaryGachaSpeciesForTimestamp(todayDate)
  var t2 = getLegendaryGachaSpeciesForTimestamp(new Date(new Date().getTime() + day_time))
  var legendList = LEGENDARIES.slice().sort()
  /*
  legendList.sort(function(a, b) {
    var A = (a == t ? 2 : (a == t2 ? 1 : 0))
    var B = (b == t ? 2 : (b == t2 ? 1 : 0))
    return B - A
  })
  */
  cdate.setUTCHours(0,0,0,0)
  todayDate.setUTCHours(0,0,0,0)
  // "Today! (Changes every day at <t:" + floor(todayDate.getTime()/1000) + ":t>)"
  // "Tomorrow, <t:" + floor(cdate.getTime()/1000) + ":D>"
  // "<t:" + floor(cdate.getTime()/1000) + (i > 7 ? ":D>" : ":R>")
  for (var i = 0; i < 100; i++) {
    var legend = getLegendaryGachaSpeciesForTimestamp(cdate)
    if (out[legendList.indexOf(legend)] == undefined && ((legend != t && legend != t2) || i > 2)) {
      out[legendList.indexOf(legend)] = legend + (legend == t || legend == t2 ? " returns " + (i > 7 ? "on " : "") : ": ") + "<t:" + floor(cdate.getTime()/1000) + (i > 7 ? ":D>" : ":R>")
    }
    cdate = new Date(cdate.getTime() + day_time)
  }
  //out.splice(2, 0, "")
  out.unshift(t2 + ": <t:" + floor((todayDate.getTime() + day_time)/1000) + ":R>")
  out.unshift(t + ": Today! (Changes every day at <t:" + floor(todayDate.getTime()/1000) + ":t>)")
  for (var i = 2; i < out.length; i++) {
    out[i] = "-# " + out[i]
  }
  out = out.join("\n")
  return out;
}

_ = null

function ts(y, mo, d, h, m, s) {
  if (y == undefined) y = year()
  if (typeof y == "object") {
    return "<t:" + floor(y.getTime() / 1000) + ">"
  }
  if (mo == undefined) mo = month() - 1
  if (d == undefined) d = day()
  if (h == undefined) h = hour()
  if (m == undefined) m = minute()
  if (s == undefined) s = second()
  return "<t:" + floor(new Date(y, mo, d, h, m, s).getTime() / 1000) + ">"
}

function boxText(m, x, y, w, h, alignleft) {
  push()
  var t = textSize()
  textSize(min(1, (w - 4) / textWidth(m)) * t) // Shrink text down if it's too wide
  text(m, x + (alignleft ? 4 : w/2 - textWidth(m)/2), y + h/2 + textAscent()/2 - textDescent()/2) // Draw centered text
  textSize(t) // Restore original text size
  pop()
}
function boxTextE(m, x, y, w, h, alignleft) {
  export_canvas.push()
  var t = export_canvas.textSize()
  export_canvas.textSize(min(1, (w - 4) / export_canvas.textWidth(m)) * t) // Shrink text down if it's too wide
  export_canvas.text(m, x + (alignleft ? 4 : w/2 - export_canvas.textWidth(m)/2), y + h/2 + export_canvas.textAscent()/2 - export_canvas.textDescent()/2) // Draw centered text
  export_canvas.textSize(t) // Restore original text size
  export_canvas.pop()
}

months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]

click = false
yearmode = false
wasYearmode = false

imageGrabQueue = [] // Requests to get images
imageActiveReqs = [] // Requests that are waiting for a return value
imageQueueSize = 5 // Max # of idle requests we can have at a time
queueLimit = 200

invalidNames = []
redirects = []
redirect_mappings = []

function getImagePixel(cacheIndex, x, y) {
  if (imagecache[cacheIndex].img.pixels == undefined || imagecache[cacheIndex].img.pixels[0] == undefined) {
    imagecache[cacheIndex].img.loadPixels()
    return "Loaded pixels, run again to get data"
  }
  var idx = (x + imagecache[cacheIndex].img.width * y) * 4
  return color(
    imagecache[cacheIndex].img.pixels[idx + 0],
    imagecache[cacheIndex].img.pixels[idx + 1],
    imagecache[cacheIndex].img.pixels[idx + 2],
    imagecache[cacheIndex].img.pixels[idx + 3]
  )
}
function autocrop(cacheIndex) {
  if (imagecache[cacheIndex].img.pixels == undefined || imagecache[cacheIndex].img.pixels[0] == undefined) {
    imagecache[cacheIndex].img.loadPixels()
    //return "Loaded pixels, run again to get data"
  }
  var I = imagecache[cacheIndex].img
  var lowestX = I.width + 1
  var lowestY = I.height + 1
  var highestX = -1
  var highestY = -1
  for (var X = 0; X < I.width; X++) {
    for (var Y = 0; Y < I.height; Y++) {
      if (I.pixels[(Y * I.width + X)*4 + 3] == 0) {
        // Blank pixel
      } else {
        lowestX = min(X, lowestX)
        lowestY = min(Y, lowestX)
        highestX = max(X, highestX)
        highestY = max(Y, highestY)
      }
    }
  }
  spriteScaleConfig[imagecache[cacheIndex].name] = [lowestX, lowestY, highestX, highestY]
  return [lowestX, lowestY, highestX, highestY]
}

function addToQueue(species, storeloc) {
  if (imageGrabQueue.length >= queueLimit) return;
  if (!imageGrabQueue.includes(species + "," + storeloc) && !invalidNames.includes(species)) {
    imageGrabQueue.push(species + "," + storeloc)
  }
  if (invalidNames.includes(species)) {
    imagecache[storeloc].status = "Invalid species"
  }
}

reply = function(r) {console.log(r)}

function doReq(idx, dat) {
  if (redirects.includes(dat[0])) {
    // Redirects is a list of sprite prompts that got changed to a different sprite
    //   (i.e. 'wishiwasi' --> 'wishiwashi-solo')
    // If we are attempting to get a sprite, but we already know that it won't work,
    //   we skip the initial search and go straight to the correct sprite name
    dat[0] = redirect_mappings[redirects.indexOf(dat[0])]
  }
  if (!noImage) {
    var r = P.getPokemon(dat[0])
    imageActiveReqs[idx] = [r, dat[0], dat[1] * 1]
    r.then(function(reply2) {
      imagecache[imageActiveReqs[idx][2]].status = "Success"
      imagecache[imageActiveReqs[idx][2]].img = loadImage(reply2.sprites.front_default)
      imageActiveReqs[idx] = undefined
    })
    r.catch(function(er) {
      //console.error("Couldn't get sprite for Pokemon: " + dat[0])
      var r2 = P.getPokemonSpeciesByName(dat[0])
      r2.then(function(reply3) {
        var r3 = P.getPokemon(reply3.varieties[0].pokemon.name)
        r3.then(function(reply4) {
          //console.debug("Successfully found alternative: " + dat[0] + " ---> " + reply3.varieties[0].pokemon.name)
          redirects.push(dat[0])
          redirect_mappings.push(reply3.varieties[0].pokemon.name)
          imagecache[imageActiveReqs[idx][2]].status = "Success"
          imagecache[imageActiveReqs[idx][2]].img = loadImage(reply4.sprites.front_default)
          imageActiveReqs[idx] = undefined
        })
        r3.catch(function(er3) {
        //console.error("Couldn't get sprite for Pokemon: " + reply3.varieties[0].pokemon.name)
          imagecache[imageActiveReqs[idx][2]].status = "NoImage"
          imagecache[imageActiveReqs[idx][2]].img = image_unavailable2
          invalidNames.push(imageActiveReqs[idx][1])
          imageActiveReqs[idx] = undefined
          if (!nospam) console.error("Unable to find species " + reply3.varieties[0].pokemon.name + " (" + dat[0] + ")")
        });
      })
      r2.catch(function(er2) {
        //console.error("Couldn't get sprite for Pokemon Species: " + dat[0])
        imagecache[imageActiveReqs[idx][2]].status = "NoImage"
        invalidNames.push(imageActiveReqs[idx][1])
        imagecache[imageActiveReqs[idx][2]].img = image_unavailable2
        imageActiveReqs[idx] = undefined
        if (!nospam) console.error("Unable to find species " + dat[0])
      });
    });
  } else {
    imageActiveReqs[idx] = ["[NO IMAGES]", dat[0], dat[1] * 1]
    imagecache[imageActiveReqs[idx][2]].status = "NoImage"
    imageActiveReqs[idx] = undefined
  }
}
function runQueue() {
  if (imageGrabQueue.length > 0)
  for (var i = 0; i < imageQueueSize; i++) {
    if (imageGrabQueue.length > 0)
    if (imageActiveReqs[i] == undefined) {
      imagecache[imageGrabQueue[0].split(",")[1] * 1].status = "Requesting"
      doReq(i, imageGrabQueue[0].split(","))
      imageGrabQueue.shift() // remove request from the queue
    }
  }
}

function guessToday() {
  return guessPokerus(new Date())
}

function pickrandom(a) {
  return a[floor(random(a.length))]
}

function getSprite(name, x, y, w, h) {
  if (typeof name != "string") return;
  if (noImage) {
    push()
    noFill()
    stroke(0)
    rect(x, y, w, h)
    pop()
    return;
  }
  name = name.replace(/_/g, "-")
  name = name[0].toUpperCase() + name.substring(1).toLowerCase()
  if (LEGENDARIES.includes(name)) {
    //return legendaryImages[name]
  }
  name = name.toLowerCase()
  // See if we have this image already
  if (imagecache_labels.includes(name)) {
    // This Pokemon was already loaded; display its sprite
    var I = imagecache_labels.indexOf(name)
    if (imagecache[I] != undefined) {
      if (imagecache[I].name == name) {
        imagecache[I].timer = 300
        if (imagecache[I].img != undefined) {
          var cropdata = [0, 0, imagecache[I].img.width, imagecache[I].img.height]
          if (spriteScaleConfig[imagecache[I].name] != undefined) {
            cropdata = spriteScaleConfig[imagecache[I].name]
            var C = crop([0, 0, imagecache[I].img.width, imagecache[I].img.height], cropdata)
            push()
            noFill()
            stroke(255, 0, 0)
            rect(C[0], C[1], map(C[2], 0, imagecache[I].img.width, 0, w), map(C[3], 0, imagecache[I].img.height, 0, h))
            translate(x + C[0], y + C[1])
            scale(w/imagecache[I].img.width, h/imagecache[I].img.height)
            image(imagecache[I].img, 0, 0)
            pop()
          }
          image(imagecache[I].img, x, y, w, h)
        } else {
          //image(image_unavailable2, x, y, w, h)
        }
      } else {
        //image(image_unavailable2, x, y, w, h)
      }
    } else {
      //image(image_unavailable2, x, y, w, h)
    }
  } else if (imagecache.length < imagelimit) {
    // This Pokemon isn't loaded, but there's space, so let's load it now
    var I = imagecache.length
    imagecache[I] = {}
    imagecache_labels[I] = name
    imagecache[I].name = name
    imagecache[I].img = undefined
    imagecache[I].timer = 500 // When the program has gone this many frames without using an image, it gets deleted
    imagecache[I].status = "Queued"
    name2 = name
    if (name2.includes("alola-")) {
      name2 = name2.substring(6) + "-alola"
    }
    if (name2.includes("galar-")) {
      name2 = name2.substring(6) + "-galar"
    }
    if (name2.includes("hisui-")) {
      name2 = name2.substring(6) + "-hisui"
    }
    if (name2.includes("paldea-")) {
      name2 = name2.substring(7) + "-paldea"
    }
    if (name2 == "tauros-paldea") name2 = pickrandom(["tauros-paldea-aqua-breed", "tauros-paldea-combat-breed", "tauros-paldea-blaze-breed"])
    if (name2 == "shaymin") name2 = "shaymin-land"
    if (name2 == "giratina") name2 = "giratina-altered"
    if (name2 == "deoxys") name2 = pickrandom(["deoxys-normal", "deoxys-attack", "deoxys-defense", "deoxys-speed"])
    if (name2 == "thundurus") name2 = "thundurus-incarnate"
    if (name2 == "tornadus") name2 = "tornadus-incarnate"
    if (name2 == "landorus") name2 = "landorus-incarnate"
    if (name2 == "enamorus") name2 = "enamorus-incarnate"
    if (name2 == "eternal-floette") name2 = "floette-eternal"
    if (name2 == "eiscue") name2 = "eiscue-ice"
    if (name2 == "bloodmoon-ursaluna") name2 = "ursaluna-bloodmoon"
    addToQueue(name2, I)
  }
}

lastcrop = ""

function lineRect(x, y, w, h, top, bottom, left, right) {
  if (typeof top == 'undefined') top = true
  if (typeof bottom == 'undefined') bottom = true
  if (typeof left == 'undefined') left = true
  if (typeof right == 'undefined') right = true
  if (top) {
    line(x, y, x + w, y)
  }
  if (bottom) {
    line(x, y + h, x + w, y + h)
  }
  if (left) {
    line(x, y, x, y + h)
  }
  if (right) {
    line(x + w, y, x + w, y + h)
  }
}

var rowsPerMonth = [6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6]

function draw() {
  background(220);
  prepareCalendar() // (via calendar-gen.js) When PokeAPI is loaded, fetch sprites for every legendary Pokemon and generate this month's calendar
  
  if (showMiniSprites)
    runQueue()
  
  var anyInProg = false
  var anyBlank = false
  for (var i = 0; i < imagecache.length; i++) {
    if (imagecache[i] != undefined) {
      if (imagecache[i].status == "Queued") {
        // This slot is waiting for its request to be handled
        anyInProg = true
      } else if (imagecache[i].status == "Requesting") {
        // This slot is waiting for a reply
        anyInProg = true
      } else if (imagecache[i] != undefined && imagecache.length == imagelimit) {
        imagecache[i].timer -= deltaTime
        if (imagecache[i].timer < 0) {
          imagecache[i] = undefined
          anyBlank = true
        }
      }
    } else {
      anyBlank = true
    }
  }
  if (anyBlank && !anyInProg) {
    for (var i = 0; i < imagecache.length; i++) {
      if (imagecache[i] == undefined) {
        imagecache.splice(i, 1)
        imagecache_labels.splice(i, 1)
        i--
      }
    }
  }
  
  if (mh.value() != 0) update_picker()
  
  if (wasYearmode != yearmode) {
    generateCalendar()
    wasYearmode = yearmode
  }
  
  if (deviceOrientation == "undefined") {
    mobileHelper.hide()
  } else {
    mobileHelper.show()
  }
  
  if (speciesEnumRaw.length > 0 && !loadedSpecies && starterNames.length > 0) {
    loadedSpecies = true
    var idx = 1
    for (var i = 0; i < speciesEnumRaw.length; i++) {
      if (speciesEnumRaw[i].includes(",") && !speciesEnumRaw[i].includes("  /**")) {
        idx++
        var dat = ""
        if (speciesEnumRaw[i].includes(" = ")) {
          idx = speciesEnumRaw[i].split(" = ")[1].split(",")[0] * 1
          dat = speciesEnumRaw[i].split(" = ")[0].substring(2).toLowerCase()
        } else {
          dat = speciesEnumRaw[i].split(',')[0].substring(2).toLowerCase()
        }
        species[idx] = dat
        if (starterNames.includes(dat)) {
          starter_species[idx] = dat
        }
      }
    }
    for (var i = 0; i < starter_species.length; i++) {
      if (starter_species[i] == undefined || starter_species[i] == null || starter_species[i] == "") {
        starter_species.splice(i, 1)
        i--
      }
    }
    console.debug("Loaded species")
  }
  
  cursor('auto')
  
  calendarW = (width - 50) / 7
  calendarH = (height - (showBottomBar ? 69 : 44) - (calendarRows - 1)*5) / calendarRows
  var Width = width/4
  if (height > width) Width = width/3
  if (height > width*1.3) Width = width/2
  var Height = (height - 20)/(12 / (width/Width))
  
  var mn = new Date().setUTCHours(0,0,0,0) + day_time
  
  timeToNextReset = new Date(mn - new Date())
  
  if (mouseY >= height - 35 && !showBottomBar && autohideBar) {
    showBottomBar = true
  }
  if (mouseY < height - 35 && showBottomBar && autohideBar) {
    showBottomBar = false
  }
  
  //rect(10, 10, width - 20, 18)
  if (todayview) {
    if (legendaryImages[todayLegend] != undefined) {
      image(legendaryImages[todayLegend], 5, 95)
      text("Today's legendary feature: " + todayLegend, 5, 55)
      var pk = guessPokerus(new Date())
      var pk2 = guessPokerus(new Date(new Date().getTime() + day_time))
      text("Today's Pokérus targets: " + pk.join(", "), 5, 73)
      text("Time until next reset: " + formatTime(timeToNextReset.getUTCHours(), timeToNextReset.getUTCMinutes(), timeToNextReset.getUTCSeconds()) + " (" + tomorrowLegend + ", " + pk2.join(", ") + ")", 5, 91)
      getSprite(pk[0], 105, 105, 60, 60)
      getSprite(pk[1], 165, 105, 60, 60)
      getSprite(pk[2], 135, 145, 60, 60)
    }
  } else if (yearmode) {
    if (calendar.length > 0) {
      offset_x = 0
      offset_y = 0
      var currentMonth = 0
      calendarRows = 6
      calendarX = calendar[0][0]
      calendarY = 0
      calendarW = (Width - 20 - 6*5) / 7
      calendarH = (Height - 20 - (calendarRows - 1)*5) / calendarRows
      if (false)
      for (var i = 0; i < calendar.length; i++) {
        if (currentMonth != calendar[i][2]) {
          rowsPerMonth[currentMonth] = calendarY + 1
          currentMonth = calendar[i][2]
          offset_x++
          calendarY = 0
          if (offset_x >= width/Width) {
            offset_x = 0
            offset_y++
          }
        } else if (calendarX == 6 && calendar[i][0] == 0) {
          calendarY++
        }
        calendarRows = rowsPerMonth[currentMonth]
        calendarH = (Height - 20 - (calendarRows - 1)*5) / calendarRows
        translate(offset_x * Width, offset_y * Height)
        calendarX = calendar[i][0]
        push()
        if (TODAY.getUTCMonth() == calendar[i][2] && TODAY.getUTCDate() == calendar[i][3] && TODAY.getUTCFullYear() == calendar[i][4]) fill(200, 200, 255)
        rect(10 + calendarX*(calendarW + 5), 10 + calendarY*(calendarH + 5), calendarW, calendarH)
        pop()
        if (legendaryImages[calendar[i][1]] != undefined) {
          var sz = min(calendarW, calendarH) - 4
          image(legendaryImages[calendar[i][1]], 10 + calendarX*(calendarW + 5) + calendarW/2 - sz/2, 10 + calendarY*(calendarH + 5) + calendarH/2 - sz/2, sz, sz)
        }
        push()
        noStroke()
        fill(255, 190)
        //rect(11 + calendarX*(calendarW + 5), 11 + calendarY*(calendarH + 5), 15, 18)
        pop()
        text(calendar[i][3], 11 + calendarX*(calendarW + 5), 20 + calendarY*(calendarH + 5))
        translate(offset_x * -Width, offset_y * -Height)
      }
      rowsPerMonth[currentMonth] = calendarY + 1
    }
  } else {
    for (var i = 0; i < 7; i++) {
      rect(10 + (calendarW + 5)*i, 10, calendarW, 24)
      boxText(daysOfWeek[i], 10 + (calendarW + 5)*i, 10, calendarW, 24)
    }
    if (calendar.length > 0) {
      calendarX = calendar[0][0]
      calendarY = 0
      for (var i = 0; i < calendar.length; i++) {
        if (calendarX == 6 && calendar[i][0] == 0) {
          calendarY++
        }
        calendarX = calendar[i][0]
      }
      calendarRows = calendarY + 1
      //calendarRows = 6
      calendarX = 0
      calendarY = 0
      calendarH = (height - (showBottomBar ? 69 : 44) - (calendarRows - 1)*5) / calendarRows
      calendarX = initCalendarX
      
      
      
      
      for (var i = 0; i < calendar.length; i++) {
        // Start calendar drawing loop
        var pokerusOffset = 0
        if (calendarX == 6 && calendar[i][0] == 0) {
          calendarY++
        }
        calendarX = calendar[i][0]
        var dayX = 10 + calendarX*(calendarW + 5)
        var dayY = 39 + calendarY*(calendarH + 5)
        push()
        if (TODAY.getUTCMonth() == calendar[i][2] && TODAY.getUTCDate() == calendar[i][3] && TODAY.getUTCFullYear() == calendar[i][4]) {
          todayIndex = i
          fill(200, 200, 255)
        }
        rect(dayX, dayY, calendarW, calendarH)
        pop()
        push()
        textSize(15)
        var ov = false
        var labelHeight = min(textLeading(), 18)
        var spriteAreaHeight = min(45, (calendarH - labelHeight)/(parallelSprites ? 5 : 3))
        var spriteHeight = min(spriteAreaHeight * (parallelSprites ? 1.3 : 1), calendarW/(parallelSprites ? 5 : 3))
        if (spriteHeight < 12) {
          spriteAreaHeight = min(30, (calendarH - labelHeight)/3)
          spriteHeight = min(spriteAreaHeight, calendarW/3)
          ov = true
        }
        if (!pkrs) {
          spriteAreaHeight = 0
          spriteHeight = 0
        }
        // Indicates where legendary's label goes
        line(dayX, dayY + labelHeight, dayX + calendarW, dayY + labelHeight)
        // Indicates where legendary's label goes
        //line(dayX, dayY + calendarH - spriteAreaHeight, dayX + calendarW, dayY + calendarH - spriteAreaHeight)
        var pk = calendar[i][5]
        if (pkrs) {
          if (parallelSprites && !ov) {
          var popout = 2
            for (var I = 4; I >= 0; I--) {
              getSprite(pk[I], dayX + spriteHeight*I + calendarW/2 - (spriteHeight*5)/2 - popout, min(dayY + calendarH - spriteHeight, dayY + calendarH - spriteAreaHeight/2 - spriteHeight/2) - popout, spriteHeight + popout*2, spriteHeight + popout*2)
            }
          } else {
            getSprite(pk[2], dayX + calendarW/4*1 - spriteHeight/2, dayY + calendarH - spriteHeight * 1.3, spriteHeight, spriteHeight)
            getSprite(pk[1], dayX + calendarW/4*2 - spriteHeight/2, dayY + calendarH - spriteHeight * 1.3, spriteHeight, spriteHeight)
            getSprite(pk[0], dayX + calendarW/4*3 - spriteHeight/2, dayY + calendarH - spriteHeight * 1.3, spriteHeight, spriteHeight)
            getSprite(pk[4], dayX + calendarW/2, dayY + calendarH - spriteHeight, spriteHeight, spriteHeight)
            getSprite(pk[3], dayX + calendarW/2 - spriteHeight, dayY + calendarH - spriteHeight, spriteHeight, spriteHeight)
          }
        }
        legendaryImageSize = min(calendarW, calendarH - labelHeight - spriteAreaHeight)
        //rect(10 + calendarX*(calendarW + 5) + (calendarW - spriteAreaWidth)/2 - legendaryImageSize/2, 39 + calendarY*(calendarH + 5) + (calendarH - bottomLabelHeight)/2 - legendaryImageSize/2, legendaryImageSize, legendaryImageSize)
        boxText(calendar[i][1], dayX + labelHeight, dayY, calendarW - labelHeight, labelHeight)
        pop()
        boxText(calendar[i][3], dayX, dayY, labelHeight, labelHeight)
        if (legendaryImages[calendar[i][1]] != undefined) {
          image(legendaryImages[calendar[i][1]], 2 + dayX + calendarW/2 - legendaryImageSize/2, 2 + (dayY + labelHeight) + (calendarH-labelHeight-spriteAreaHeight)/2 - legendaryImageSize/2, legendaryImageSize - 4, legendaryImageSize - 4)
        } else {
          push()
          noFill()
          stroke(0)
          rect(2 + dayX + calendarW/2 - legendaryImageSize/2, 2 + (dayY + labelHeight) + (calendarH-labelHeight-spriteAreaHeight)/2 - legendaryImageSize/2, legendaryImageSize - 4, legendaryImageSize - 4)
          pop()
          //text(calendar[i][1], 2 + dayX + calendarW/2 - legendaryImageSize/2 - textWidth(calendar[i][1])/2 + (legendaryImageSize - 4)/2, 2 + (dayY + labelHeight) + (calendarH-labelHeight-spriteAreaHeight)/2 - legendaryImageSize/2 + (legendaryImageSize - 4)/2)
        }
        
        if (inRectArea(dayX, dayY, calendarW, calendarH) || showall) {
          push()
          fill(255, 200)
          rect(dayX, dayY + labelHeight, calendarW, textLeading()*7 - labelHeight)
          pop()
          boxText("Pokérus", dayX, dayY + labelHeight + 1, calendarW, textLeading())
          var titleLineSpacing = calendarW/6
          line(dayX + titleLineSpacing, dayY + labelHeight + textLeading(), dayX + calendarW - titleLineSpacing, dayY + labelHeight + textLeading())
          for (var q = 0; q < pk.length; q++) {
            boxText(genSpeciesNamesMerged[genSpeciesMerged.indexOf(pk[q])].substring(5), dayX, dayY + labelHeight + 1 + textLeading() + (textLeading() - 1) * q, calendarW, textLeading())
          }
        }
        // End calendar drawing loop
      }
      
      
      
      
      var initCalendarX = calendarX
      calendarX++
      calendarX = initCalendarX
    }
    if (showBottomBar) {
      push()
      if (inRectArea(10, height - 25, (width - 20)/6, 20)) {
        cursor('pointer')
        if (click) {
          displayMonth--
          if (displayMonth == -1) {
            displayMonth = 11
            displayYear--
          }
          generateCalendar()
        }
        fill(200, 200, 255)
      }
      rect(10, height - 25, (width - 40)/6, 20)
      pop()
      push()
      if (inRectArea(width - (width - 45)/6 - 30, height - 25, (width - 45)/6, 20)) {
        cursor('pointer')
        if (click) {
          displayMonth++
          if (displayMonth == 12) {
            displayMonth = 0
            displayYear++
          }
          generateCalendar()
        }
        fill(200, 200, 255)
      }
      rect(width - (width - 45)/6 - 30, height - 25, (width - 45)/6, 20)
      pop()
      push()
      if (inRectArea(15 + (width - 45)/6, height - 25, (width - 45)/6, 20)) {
        cursor('pointer')
        if (click) {
          displayMonth = month() - 1
          displayYear = year()
          generateCalendar()
        }
        fill(200, 200, 255)
      }
      rect(15 + (width - 45)/6, height - 25, (width - 45)/6, 20)
      pop()
      push()
      if (inRectArea(20 + (width - 45)/3, height - 25, width - 35 - (width - 45)/3 - (width - 45)/6, 20)) {
        cursor('pointer')
        if (click) {
          takeScreenshot = true
        }
        fill(200, 200, 255)
      }
      rect(20 + (width - 45)/3, height - 25, width - 55 - (width - 45)/3 - (width - 45)/6, 20)
      pop()
      push()
      if (inRectArea(width - 25, height - 25, 20, 20)) {
        cursor('pointer')
        fill(200, 200, 255)
      }
      rect(width - 25, height - 25, 20, 20)
      pop()
      if (inRectArea(20 + (width - 40)/3, height - 25, width - 35 - (width - 40)/3 - (width - 40)/6, 20) && !click) {
        boxText("<< Prev", 10, height - 25, (width - 40)/6, 20)
        boxText("Next >>", width - (width - 40)/6 - 30, height - 25, (width - 40)/6, 20)
        boxText("Today", 15 + (width - 40)/6, height - 25, (width - 40)/6, 20)
        timedisplay = months[calendar[0][2]] + " " + calendar[0][4]
        if (override_time) {
          timedisplay += " - Displaying " + test_hour + ":" + (test_mins < 10 ? "0" : "") + test_mins + " (GMT" + (test_zone >= 0 ? "+" : "") + test_zone + ")"
        }
        //timedisplay = "CURRENTLY BEING WORKED ON"
        if (maintenanceMode) {
          timedisplay += " - Maintenance mode"
        } else if (noImage) {
          timedisplay += " - PokéAPI is down! No images :("
        }
        timedisplay += " - click here to save a photo"
        if (specialMsg != "") timedisplay = specialMsg
        boxText(timedisplay, 20 + (width - 40)/3, height - 25, width - 55 - (width - 40)/3 - (width - 20)/6, 20)
      } else if (takeScreenshot) {
        push()
        fill(220)
        noStroke()
        rect(-1, height - 27, width + 2, 30)
        pop()
        rect(width/3, height - 25, width/3, 20)
        boxText(months[calendar[0][2]] + " " + calendar[0][4], width/3, height - 25, width/3, 20)
      } else {
        boxText("<< Prev", 10, height - 25, (width - 45)/6, 20)
        boxText("Next >>", width - (width - 45)/6 - 30, height - 25, (width - 45)/6, 20)
        boxText("Today", 15 + (width - 40)/6, height - 25, (width - 40)/6, 20)
        timedisplay = months[calendar[0][2]] + " " + calendar[0][4]
        if (override_time) {
          timedisplay += " - Displaying " + test_hour + ":" + (test_mins < 10 ? "0" : "") + test_mins + " (GMT" + (test_zone >= 0 ? "+" : "") + test_zone + ")"
        }
        //timedisplay = "CURRENTLY BEING WORKED ON"
        if (maintenanceMode) {
          timedisplay += " - Maintenance mode"
        } else if (noImage) {
          timedisplay += " - PokéAPI is down! No images :("
        }
        if (specialMsg != "") timedisplay = specialMsg
        //timedisplay += " / offset: " + new Date().getDate() + " - " + todayIndex + " - 1 (" + (new Date().getMonth()+1) + "/" + new Date().getDate() + "/" + new Date().getFullYear() + ")"
        boxText(timedisplay, 20 + (width - 40)/3, height - 25, width - 55 - (width - 40)/3 - (width - 20)/6, 20)
      }
      if (takeScreenshot) {
        takeScreenshot = false
        buildScreenshot()
      }
      click = false
    }
  }
  //boxTest()
}


function crop(c1, c2) {
  var x1 = c1[0]
  var y1 = c1[1]
  var w1 = c1[2]
  var h1 = c1[3]
  var x2 = c2[0]
  var y2 = c2[1]
  var w2 = c2[2]
  var h2 = c2[3]
  var x3 = -(x2-(max(w2,h2)-w2)/2)*w1/max(w2,h2)
  var y3 = -(y2-(max(w2,h2)-h2)/2)*h1/max(w2,h2)
  var w3 = w1 * w1/max(w2,h2)
  var h3 = h1 * h1/max(w2,h2)
  return [x3, y3, w3, h3]
}

function boxTest() {
  push()
  noFill()
  var X = 15
  var Y = 15
  var W = 40
  var H = 40
  var X1 = 6
  var Y1 = 7
  var X2 = 31
  var Y2 = 28
  stroke(255, 0, 0)
  rect(X, Y, W, H)
  //stroke(0, 0, 255)
  //rect(X + X1, Y + Y1, X2 - X1, Y2 - Y1)
  stroke(0)
  translate(X, Y)
  var W2 = W / (X2-X1)
  var H2 = H / (Y2-Y1)
  translate(-X1 * W2, -Y1 * H2)
  scale(W2, H2)
  rect(0, 0, W, H)
  rect(X1, Y1, X2 - X1, Y2 - Y1)
  pop()
  push()
  stroke(0, 0, 255)
  noFill()
  rect(X - X1*W2, Y - Y1*H2, W * W2, H * H2)
  pop()
}

takeScreenshot = false

function buildScreenshot() {
  export_canvas.background(220)
  calendarW = (export_canvas.width - 50) / 7
  calendarH = (export_canvas.height - 69 - (calendarRows - 1)*5) / calendarRows
  var Width = export_canvas.width/4
  if (export_canvas.height > export_canvas.width) Width = export_canvas.width/3
  if (export_canvas.height > export_canvas.width*1.3) Width = export_canvas.width/2
  var Height = (export_canvas.height - 20)/(12 / (export_canvas.width/Width))
  for (var i = 0; i < 7; i++) {
    export_canvas.rect(10 + (calendarW + 5)*i, 10, calendarW, 24)
    boxTextE(daysOfWeek[i], 10 + (calendarW + 5)*i, 10, calendarW, 24)
  }
  if (calendar.length > 0) {
    calendarX = calendar[0][0]
    calendarY = 0
    for (var i = 0; i < calendar.length; i++) {
      if (calendarX == 6 && calendar[i][0] == 0) {
        calendarY++
      }
      calendarX = calendar[i][0]
    }
    calendarRows = calendarY + 1
    //calendarRows = 6
    calendarX = calendar[0][0]
    calendarY = 0
    calendarH = (export_canvas.height - 69 - (calendarRows - 1)*5) / calendarRows
    for (var i = 0; i < calendar.length; i++) {
      if (calendarX == 6 && calendar[i][0] == 0) {
        calendarY++
      }
      calendarX = calendar[i][0]
      export_canvas.push()
      if (false && TODAY.getUTCMonth() == calendar[i][2] && TODAY.getUTCDate() == calendar[i][3] && TODAY.getUTCFullYear() == calendar[i][4])
        export_canvas.fill(200, 200, 255)
      export_canvas.rect(10 + calendarX*(calendarW + 5), 39 + calendarY*(calendarH + 5), calendarW, calendarH)
      export_canvas.pop()
      if (legendaryImages[calendar[i][1]] != undefined) {
        var sz = min(calendarW, calendarH) - 4
        export_canvas.image(legendaryImages[calendar[i][1]], 8 + calendarX*(calendarW + 5) + calendarW - sz, 41 + calendarY*(calendarH + 5) + max(0, min(8, calendarH - sz - 20)), sz, sz)
      }
      export_canvas.push()
      export_canvas.noStroke()
      export_canvas.fill(255, 190)
      export_canvas.rect(11 + calendarX*(calendarW + 5), 40 + calendarY*(calendarH + 5), 15, 18)
      var s2 = min(20, calendarH * 0.3)
      export_canvas.rect(11 + calendarX*(calendarW + 5), 38 + calendarY*(calendarH + 5) + calendarH - s2, calendarW - 2, s2)
      export_canvas.pop()
      boxTextE(calendar[i][3], 11 + calendarX*(calendarW + 5), 40 + calendarY*(calendarH + 5), 15, 18)
      export_canvas.textSize(15)
      boxTextE(calendar[i][1], 11 + calendarX*(calendarW + 5), 38 + calendarY*(calendarH + 5) + calendarH - s2, calendarW - 2, s2)
      export_canvas.textSize(10)
      if (inRectArea(10 + calendarX*(calendarW + 5), 39 + calendarY*(calendarH + 5), calendarW, calendarH) && false) {
        var pk = guessPokerus(new Date(calendar[i][4], calendar[i][2], calendar[i][3]))
        pk[0] += "?"
        pk[1] += "?"
        pk[2] += "?"
        export_canvas.push()
        export_canvas.fill(255, 190)
        export_canvas.noStroke()
        export_canvas.rect(12 + calendarX*(calendarW + 5), 39 + calendarY*(calendarH + 5) + calendarH - s2 - export_canvas.textLeading()*3, max(export_canvas.textWidth(pk[0]), export_canvas.textWidth(pk[1]), export_canvas.textWidth(pk[2])), export_canvas.textLeading()*3)
        export_canvas.pop()
        export_canvas.text(pk[0], 12 + calendarX*(calendarW + 5), 39 + calendarY*(calendarH + 5) + calendarH - s2 - 5 - export_canvas.textLeading()*2)
        export_canvas.text(pk[1], 12 + calendarX*(calendarW + 5), 39 + calendarY*(calendarH + 5) + calendarH - s2 - 5 - export_canvas.textLeading())
        export_canvas.text(pk[2], 12 + calendarX*(calendarW + 5), 39 + calendarY*(calendarH + 5) + calendarH - s2 - 5)
      }
      export_canvas.textSize(12)
    }
  }
  export_canvas.rect(export_canvas.width/3, export_canvas.height - 25, export_canvas.width/3, 20)
  boxTextE(months[calendar[0][2]] + " " + calendar[0][4], export_canvas.width/3, export_canvas.height - 25, export_canvas.width/3, 20)
  save(export_canvas, months[calendar[0][2]] + " " + calendar[0][4] + " Gacha.png")
}

function formatTime(h, m, s) {
  return h + ":" + (m < 10 ? "0" : "") + m + ":" + (s < 10 ? "0" : "") + s
}

function inRectArea(x, y, w, h) {
  return (mouseX > x && mouseY > y && mouseX < x + w && mouseY < y + h)
}

function mousePressed() {
  click = true
}

function windowResized() {
  resizeCanvas(windowWidth - 1, windowHeight - 1);
}