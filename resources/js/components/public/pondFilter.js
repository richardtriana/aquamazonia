// Filtro de estanques por código (T7, E17) o por número (81).
// Si algún estanque coincide exacto, se muestra solo ese; si no, los que lo contienen.
const normalize = (value) => String(value || "").toUpperCase().replace(/[\s-]/g, "");
const digitsOf = (value) => value.replace(/\D/g, "");

export function filterPonds(siembras, text) {
  const t = normalize(text);
  if (!t) return siembras;

  const withLetters = /[A-Z]/.test(t);
  const key = withLetters ? t : digitsOf(t);
  if (!key) return siembras;

  const codeOf = (s) => (withLetters ? normalize(s.contenedor) : digitsOf(normalize(s.contenedor)));

  const exact = siembras.filter((s) => codeOf(s) === key);
  if (exact.length) return exact;

  // Sin exacto: los que contienen lo escrito (sirve también para «TILAPIA», «ALEVINOS»)
  return siembras.filter((s) => codeOf(s).includes(key));
}
