/**
 * Charakterisierungstests für js/admin.js.
 *
 * Diese Tests laden den unveränderten Produktivcode js/admin.js per node:vm
 * mit seinem echten Dateinamen und einer minimalen, aber echten DOM/jQuery-
 * Grenze. Registrierte Handler werden über tatsächlich ausgelöste Events
 * (submit, keydown) getriggert; behauptet werden beobachtbare positive und
 * negative Ergebnisse. Es handelt sich um Charakterisierungstests des
 * bestehenden Verhaltens, nicht um einen TDD-Red-Green-Nachweis.
 */
import fs from 'node:fs'
import path from 'node:path'
import vm from 'node:vm'
import process from 'node:process'

const root = path.resolve(import.meta.dirname, '..')

class El
{
    constructor(tag, attrs = {})
    {
        this.tagName = tag.toUpperCase()
        this.attributes = { ...attrs }
        this.children = []
        this.parent = null
        this.listeners = new Map()
        this.removed = false
        this.focusCount = 0
        this.clickCount = 0
        this.classes = new Set(String(attrs.class ?? '').split(/\s+/).filter(Boolean))
    }

    appendChild(child)
    {
        child.parent = this
        this.children.push(child)
        return child
    }

    matchesSimple(simple)
    {
        const attrMatch = simple.match(/^([a-zA-Z]*)\[(\w+)="([^"]*)"\]$/)
        if (attrMatch)
        {
            const [, tag, name, value] = attrMatch
            return (!tag || this.tagName === tag.toUpperCase())
                && this.attributes[name] === value
        }
        if (simple.startsWith('.'))
        {
            return this.classes.has(simple.slice(1))
        }
        return this.tagName === simple.toUpperCase()
    }

    matches(selector)
    {
        return selector.split(',').some((part) => this.matchesSimple(part.trim()))
    }

    descendants()
    {
        const out = []
        const walk = (el) =>
        {
            for (const child of el.children)
            {
                out.push(child)
                walk(child)
            }
        }
        walk(this)
        return out
    }

    dispatch(type, event = {})
    {
        for (const fn of this.listeners.get(type) ?? [])
        {
            fn.call(this, { type, ...event })
        }
    }
}

function selectAll(rootEl, selector)
{
    return rootEl.descendants().filter((el) => el.matches(selector))
}

class JQ
{
    constructor(elements)
    {
        this.elements = elements
    }

    get length()
    {
        return this.elements.length
    }

    each(cb)
    {
        this.elements.forEach((el, i) => cb.call(el, i, el))
        return this
    }

    find(selector)
    {
        const found = this.elements.flatMap((el) => el.descendants().filter((d) => d.matches(selector)))
        return new JQ(found)
    }

    on(type, cb)
    {
        for (const el of this.elements)
        {
            if (!el.listeners.has(type))
            {
                el.listeners.set(type, [])
            }
            el.listeners.get(type).push(cb)
        }
        return this
    }

    ready(cb)
    {
        cb()
        return this
    }

    append(html)
    {
        const tagMatch = html.match(/^<(\w+)([^>]*)\s*\/?>$/)
        const attrs = {}
        for (const m of tagMatch[2].matchAll(/(\w+)="([^"]*)"/g))
        {
            attrs[m[1]] = m[2]
        }
        for (const el of this.elements)
        {
            el.appendChild(new El(tagMatch[1], attrs))
        }
        return this
    }

    removeClass(name)
    {
        for (const el of this.elements)
        {
            el.classes.delete(name)
        }
        return this
    }

    remove()
    {
        for (const el of this.elements)
        {
            el.removed = true
            if (el.parent)
            {
                el.parent.children = el.parent.children.filter((c) => c !== el)
            }
        }
        return this
    }

    attr(name)
    {
        return this.elements[0]?.attributes[name]
    }

    nextAll(selector)
    {
        const out = []
        for (const el of this.elements)
        {
            if (!el.parent)
            {
                continue
            }
            const siblings = el.parent.children
            const idx = siblings.indexOf(el)
            for (const sib of siblings.slice(idx + 1))
            {
                if (sib.matches(selector))
                {
                    out.push(sib)
                }
            }
        }
        return new JQ(out)
    }

    first()
    {
        return new JQ(this.elements.slice(0, 1))
    }

    is(selector)
    {
        return this.elements.length > 0 && this.elements[0].matches(selector)
    }

    focus()
    {
        for (const el of this.elements)
        {
            el.focusCount += 1
        }
        return this
    }

    click()
    {
        for (const el of this.elements)
        {
            el.clickCount += 1
            el.dispatch('click')
        }
        return this
    }
}

function makeJQuery(document)
{
    return function $(input)
    {
        if (input === document || input instanceof El)
        {
            return new JQ([input])
        }
        if (typeof input === 'string')
        {
            return new JQ(selectAll(document, input))
        }
        return new JQ([])
    }
}

function loadAdmin(buildDom, requestToken)
{
    const document = new El('#document')
    buildDom(document)
    const $ = makeJQuery(document)
    const window = {}
    const OC = { requestToken }
    const sandbox = { window, jQuery: $, document, OC }
    const filename = fs.realpathSync(path.join(root, 'js', 'admin.js'))
    const code = fs.readFileSync(filename, 'utf8')
    vm.runInNewContext(code, sandbox, { filename })
    return { window, $, document, OC }
}

let failures = 0
function check(name, condition)
{
    if (condition)
    {
        process.stdout.write(`ok - ${name}\n`)
    }
    else
    {
        failures += 1
        process.stderr.write(`FAIL - ${name}\n`)
    }
}

function buildFormWithToken()
{
    return (document) =>
    {
        const form = document.appendChild(new El('form'))
        const group = form.appendChild(new El('div', { class: 'form-group' }))
        group.appendChild(new El('input', { type: 'text', name: 'title' }))
        form.appendChild(new El('input', { type: 'hidden', name: 'requesttoken', value: 'existing' }))
    }
}

function buildFormWithoutToken()
{
    return (document) =>
    {
        const form = document.appendChild(new El('form'))
        const group = form.appendChild(new El('div', { class: 'form-group' }))
        group.appendChild(new El('input', { type: 'text', name: 'title' }))
    }
}

// 1. CSRF-Token wird in ein Formular ohne requesttoken eingefügt (positiv).
{
    const { document } = loadAdmin(buildFormWithoutToken(), 'tok-abc-123')
    const tokens = selectAll(document, 'input[name="requesttoken"]')
    check('Formular ohne requesttoken erhält genau ein verstecktes Token-Feld', tokens.length === 1)
    check('Token-Feld trägt den Wert von OC.requestToken', tokens[0]?.attributes.value === 'tok-abc-123')
    check('Token-Feld ist hidden', tokens[0]?.attributes.type === 'hidden')
}

// 2. Vorhandenes requesttoken-Feld wird nicht dupliziert (negativ).
{
    const { document } = loadAdmin(buildFormWithToken(), 'tok-abc-123')
    const tokens = selectAll(document, 'input[name="requesttoken"]')
    check('Formular mit vorhandenem requesttoken bleibt unverändert', tokens.length === 1 && tokens[0].attributes.value === 'existing')
}

// 3. Ohne OC.requestToken wird nichts eingefügt (negativ).
{
    const { document } = loadAdmin(buildFormWithoutToken(), '')
    check('Leerer OC.requestToken fügt kein Token-Feld hinzu', selectAll(document, 'input[name="requesttoken"]').length === 0)
}

// 4. Submit entfernt has-error und .error-message (positiv), unberührtes Formular bleibt heil (negativ).
{
    const { document } = loadAdmin((doc) =>
    {
        const form = doc.appendChild(new El('form'))
        form.appendChild(new El('div', { class: 'form-group has-error' }))
        form.appendChild(new El('span', { class: 'error-message' }))
        const clean = doc.appendChild(new El('form'))
        clean.appendChild(new El('div', { class: 'form-group' }))
    }, 'tok')
    const forms = selectAll(document, 'form')
    forms[0].dispatch('submit')
    check('Submit entfernt has-error von .form-group', !selectAll(document, '.form-group')[0].classes.has('has-error'))
    check('Submit entfernt .error-message-Elemente', selectAll(document, '.error-message').length === 0)
    forms[1].dispatch('submit')
    check('Submit auf fehlerfreiem Formular ändert nichts', selectAll(document, '.form-group').length === 2
        && selectAll(document, '.form-group')[1].classes.size === 1)
}

// 5. Enter in einem Input fokussiert das nächste Eingabefeld (positiv).
{
    const { document } = loadAdmin((doc) =>
    {
        const form = doc.appendChild(new El('form'))
        form.appendChild(new El('input', { type: 'text', name: 'a' }))
        form.appendChild(new El('input', { type: 'text', name: 'b' }))
    }, 'tok')
    const inputs = selectAll(document, 'input')
    inputs[0].dispatch('keydown', { key: 'Enter' })
    check('Enter fokussiert das nächste Eingabefeld', inputs[1].focusCount === 1 && inputs[0].focusCount === 0)
}

// 6. Enter vor einem Button fokussiert und klickt ihn (positiv).
{
    const { document } = loadAdmin((doc) =>
    {
        const form = doc.appendChild(new El('form'))
        form.appendChild(new El('input', { type: 'text', name: 'a' }))
        form.appendChild(new El('button', { type: 'submit' }))
    }, 'tok')
    const input = selectAll(document, 'input')[0]
    const button = selectAll(document, 'button')[0]
    input.dispatch('keydown', { key: 'Enter' })
    check('Enter vor Button klickt den Button', button.focusCount === 1 && button.clickCount === 1)
}

// 7. Enter bei type="textarea" und andere Tasten lösen keine Navigation aus (negativ).
{
    const { document } = loadAdmin((doc) =>
    {
        const form = doc.appendChild(new El('form'))
        form.appendChild(new El('input', { type: 'textarea', name: 'a' }))
        form.appendChild(new El('input', { type: 'text', name: 'b' }))
    }, 'tok')
    const inputs = selectAll(document, 'input')
    inputs[0].dispatch('keydown', { key: 'Enter' })
    inputs[0].dispatch('keydown', { key: 'Tab' })
    check('Enter bei type="textarea" und Nicht-Enter-Tasten fokussieren nichts', inputs[1].focusCount === 0)
}

// 8. Enter ohne nachfolgendes Element bleibt ohne Wirkung und ohne Fehler (negativ).
{
    const { document } = loadAdmin((doc) =>
    {
        doc.appendChild(new El('form')).appendChild(new El('input', { type: 'text', name: 'a' }))
    }, 'tok')
    const input = selectAll(document, 'input')[0]
    let threw = false
    try
    {
        input.dispatch('keydown', { key: 'Enter' })
    }
    catch
    {
        threw = true
    }
    check('Enter ohne Folgeelement wirft nicht und fokussiert nichts', !threw && input.focusCount === 0)
}

// 9. Öffentliche API ist exponiert und init ist idempotent (positiv + negativ).
{
    const { window, document } = loadAdmin(buildFormWithoutToken(), 'tok-xyz')
    check('window.FlzOrganizationAdmin.init ist exponiert', typeof window.FlzOrganizationAdmin?.init === 'function')
    window.FlzOrganizationAdmin.init()
    check('Erneutes init dupliziert das Token-Feld nicht', selectAll(document, 'input[name="requesttoken"]').length === 1)
}

if (failures > 0)
{
    throw new Error(`${failures} Verhaltenstest(s) für js/admin.js fehlgeschlagen`)
}
process.stdout.write('OK (js/admin.js Verhaltenstests)\n')
