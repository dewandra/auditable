// utils/helpers.js

/**
 * Cek apakah string adalah JSON valid
 * @param {string} str
 * @returns {boolean}
 */
export function isJsonString(str) {
  try {
    JSON.parse(str);
    return true;
  } catch (e) {
    return false;
  }
}
