const Str = require("@supercharge/strings");
/**
 * Translate all top-level keys of the given `object` to camelCase.
 *
 * @param {Object} object
 *
 * @returns {Object}
 */
function camelCaseKeys (object) {
  return Object
    .entries(object)
    .reduce((carry, [key, value]) => {
      carry[Str(key).camel().get()] = value

      return carry
    }, {})
}

module.exports = {
  camelCaseKeys
}
