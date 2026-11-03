/**
 * 本地存储工具
 * @method setStorage - 设置本地存储
 * @method getStorage - 获取本地存储
 * @method removeStorage - 删除本地存储
 * @method clearStorage - 清空本地存储
 */

// 设置存储
export const setStorage = (key, value) => {
  if (typeof value === 'object') {
    value = JSON.stringify(value)
  }
  localStorage.setItem(key, value)
}

// 获取存储
export const getStorage = (key) => {
  const value = localStorage.getItem(key)
  try {
    return JSON.parse(value)
  } catch (e) {
    return value
  }
}

// 删除存储
export const removeStorage = (key) => {
  localStorage.removeItem(key)
}

// 清空存储
export const clearStorage = () => {
  localStorage.clear()
}