calendar = []
// Gets all the legendaries for the selected month.
function generateCalendar2(months, offset) {
  var startingDate;
  var loops = 5000
  calendar = []
  if (months == undefined || months < 1) months = 1
  startingDate = new Date(displayYear, displayMonth, 1)
  var startMonth = startingDate.getMonth()
  var current_day = startingDate.getTime()
  while (months > 0) {
    while (startingDate.getMonth() == startMonth && loops > 0) {
      calendar.push([startingDate.getDay(), getLegendaryGachaSpeciesForTimestamp(startingDate), startingDate.getMonth(), startingDate.getDate(), startingDate.getFullYear()])

      // Add one day
      startingDate = new Date(startingDate.getTime() + 86400000)
      loops-- // avoid infinite loop crashing the program
    }
    startMonth++
    while (startMonth >= 12) startMonth -= 12
    months--
  }
  return calendar
}

function getLegendaryGachaSpeciesForTimestamp(timestamp) {
  const legendarySpecies = LEGENDARIES.slice() // duplicates legendary list
  var ret;

  // 86400000 is the number of miliseconds in one day
  const timeDate = new Date(timestamp);
  const dayTimestamp = timeDate.getTime(); // Timestamp of current week
  const offset = floor(floor(dayTimestamp / 86400000) / legendarySpecies.length); // Cycle number
  const index = floor(dayTimestamp / 86400000) % legendarySpecies.length; // Index within cycle

  rdg.executeWithSeedOffset(function() {
    ret = rdg.shuffle(legendarySpecies)[index];
  }, offset, "1073741824");

  return ret;
}