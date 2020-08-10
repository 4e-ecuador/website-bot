const translations = window.translations

/**
 * @returns String
 */
export function translate(key) {
    return translations[key] ? translations[key] : key
}

export function translatePlural(key, num) {
    let pluralKey
    switch (num) {
        case 0:
            pluralKey = key + '.0'
            break
        case 1:
            pluralKey = key + '.1'
            break
        default:
            pluralKey = key + '.other'
            break
    }

    return translations[pluralKey] ? translations[pluralKey] : key
}
