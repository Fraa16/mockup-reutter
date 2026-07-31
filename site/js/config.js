/**
 * Site content configuration.
 * Values marked "placeholder" were invented by the design mockup and were
 * never verified against real numbers — swap them for the real ones
 * (Google rating/review count, years in business, review quotes) before
 * launch.
 */
const SITE_CONFIG = {
  // placeholder — replace with the real figure
  yearsInBusiness: 25,
  // placeholder — replace with the current Google rating
  googleRating: '4,9',
  // placeholder — replace with the current Google review count
  reviewCount: 87,

  // Interactive vehicle hotspot — 7 zones, positioned as % of the image frame
  spots: [
    {
      x: 38, y: 36, num: '01', tag: 'Exterieur', label: 'Lack & Politur',
      title: 'Lackaufbereitung & Politur',
      lead: 'Mehrstufige Politur nach Lackdickenmessung. Wir schleifen nur so viel weg, wie nötig ist — und versiegeln danach, damit das Ergebnis nicht nach der ersten Wäsche verschwindet.',
      bullets: ['Lackdickenmessung vor jedem Schliff', 'Kratzer und Hologramme raus, nicht überdeckt', 'Versiegelung oder Keramikschutz zum Abschluss']
    },
    {
      x: 63, y: 25, num: '02', tag: 'Karosserie', label: 'Dellen & Hagel',
      title: 'Dellenbeseitigung & Hagelschaden',
      lead: 'Ausbeulen ohne Lackieren. Der Originallack bleibt, wo er ist — das ist der Unterschied zwischen einer Reparatur und einem Wertverlust im Fahrzeugbrief.',
      bullets: ['Smart Repair von innen, ohne Spachtel und Füller', 'Hagelschaden komplett, Panel für Panel', 'Einzelne Parkdellen meist am selben Tag']
    },
    {
      x: 17, y: 52, num: '03', tag: 'Exterieur', label: 'Lackierarbeiten',
      title: 'Lackierarbeiten',
      lead: 'Farbtonbestimmung, Beilackierung, Teillackierung. Ziel ist ein Übergang, den man auch bei Sonne von der Seite nicht findet.',
      bullets: ['Farbtonauslesung und Musterblech vor dem Auftrag', 'Teil- und Beilackierung statt ganzer Seite', 'Kunststoff-, Stoßfänger- und Anbauteile']
    },
    {
      x: 30, y: 77, num: '04', tag: 'Exterieur', label: 'Felgen & Reifen',
      title: 'Felgen- & Reifenpflege',
      lead: 'Felgen werden von innen gereinigt, nicht nur abgespült. Die Reifenflanke bleibt danach matt statt speckig glänzend.',
      bullets: ['Säurefreie Reinigung, Felgenbett inklusive', 'Bordsteinschäden auf Anfrage', 'Versiegelung gegen eingebrannten Bremsstaub']
    },
    {
      x: 51, y: 17, num: '05', tag: 'Interieur', label: 'Innenraum',
      title: 'Innenraumaufbereitung',
      lead: 'Sitze, Teppich, Himmel, Schächte und Fugen. Polster werden nass extrahiert, nicht nur abgesaugt und eingesprüht.',
      bullets: ['Sprühextraktion für Polster und Teppich', 'Kunststoff matt aufgefrischt, ohne Glanzfilm', 'Scheiben innen streifenfrei, auch die Kanten']
    },
    {
      x: 74, y: 45, num: '06', tag: 'Interieur', label: 'Lederreparatur',
      title: 'Lederreparatur',
      lead: 'Risse, Brandlöcher, Scheuerstellen, Farbabrieb. Wir reparieren die Stelle und färben die Fläche — nicht das ganze Fahrzeug.',
      bullets: ['Risse und Brandlöcher füllen und strukturieren', 'Farbabrieb an der Sitzwange nachfärben', 'Lenkrad, Schaltsack und Türgriffe']
    },
    {
      x: 86, y: 67, num: '07', tag: 'Interieur', label: 'Ozonbehandlung',
      title: 'Ozonbehandlung',
      lead: 'Gegen Rauch, Tiergeruch und Schimmel. Ozon zerlegt die Geruchsmoleküle, es überdeckt sie nicht mit Duftbaum.',
      bullets: ['Rauch- und Tiergeruch dauerhaft entfernen', 'Lüftungs- und Klimasystem mitbehandelt', 'Fahrzeug danach 60 Minuten gesperrt']
    }
  ],

  // Vorher/Nachher cases — placeholder captions until real before/after photos exist
  cases: [
    { name: 'Hagelschaden Dach', meta: '3 Tage', note: 'Über 40 Dellen auf Dach und Motorhaube, komplett gedrückt. Kein Lackauftrag, Originallack erhalten.' },
    { name: 'Lack & Politur', meta: '1 Tag', note: 'Waschkratzer und Hologramme über die ganze Flanke. Zweistufige Politur, danach versiegelt.' },
    { name: 'Leder Fahrersitz', meta: '2 Tage', note: 'Durchgescheuerte Sitzwange, repariert, strukturiert und im Originalfarbton nachgefärbt.' }
  ],

  // Request form — step 2 service chips
  chips: ['Exterieur-Pflege', 'Interieur-Pflege', 'Dellen / Hagelschaden', 'Lackierarbeiten', 'Lederreparatur', 'Ozonbehandlung', 'Felgenaufbereitung', 'Weiß ich noch nicht'],

  formSteps: ['Fahrzeug', 'Leistung', 'Kontakt'],
  formHints: ['Schritt 1 von 3', 'Schritt 2 von 3 · Mehrfachauswahl möglich', 'Letzter Schritt'],

  beforeAfterStart: 52
};
