import fs from 'node:fs'
import path from 'node:path'
import process from 'node:process'

const root = path.resolve(import.meta.dirname, '..')
const runtimeJs = path.join(root, 'js')
if (fs.existsSync(runtimeJs)) {
  const content = fs.readdirSync(runtimeJs, { recursive: true })
    .filter((file) => file.endsWith('.js') || file.endsWith('.mjs'))
    .map((file) => fs.readFileSync(path.join(runtimeJs, file), 'utf8'))
    .join('\n')
  if (content.includes('window.LocalBase')) {
    throw new Error('flz_organization must not reintroduce the LocalBase browser global')
  }
}

process.stdout.write('OK (no LocalBase browser runtime dependency)\n')

// Verhaltenstests (Charakterisierungstests) für js/admin.js: lädt den
// unveränderten Produktivcode per node:vm und löst die registrierten
// Handler über echte Events aus.
await import('./js-admin-behavior.test.mjs')
