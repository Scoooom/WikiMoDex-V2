LegendaryData = [
  "Mewtwo", "mewtwo",
  "Lugia", "lugia",
  "Ho-oh", "ho-oh",
  "Kyogre", "kyogre",
  "Groudon", "groudon",
  "Rayquaza", "rayquaza",
  "Dialga", "dialga",
  "Palkia", "palkia",
  "Giratina", "giratina-altered",
  "Arceus", "arceus",
  "Reshiram", "reshiram",
  "Zekrom", "zekrom",
  "Kyurem", "kyurem",
  "Xerneas", "xerneas",
  "Yveltal", "yveltal",
  "Zygarde", "zygarde-50",
  "Necrozma", "necrozma",
  "Zacian", "zacian",
  "Zamazenta", "zamazenta",
  "Calyrex", "calyrex",
  "Koraidon", "koraidon",
  "Miraidon", "miraidon",
  "Terapagos", "terapagos",
]
LEGENDARIES = []
LEGENDARY_IDS = []
for (var i = 0; i < LegendaryData.length; i += 2) {
  LEGENDARIES[i / 2] = LegendaryData[i]
  LEGENDARY_IDS[i / 2] = LegendaryData[i + 1]
}