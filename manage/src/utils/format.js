/**
 * 金额单位换算：分 -> 元
 *
 * @param {number|string} fen 以「分」为单位的金额
 * @returns {string} 保留两位小数的元字符串
 */
export const formatFen = (fen) => (Number(fen) / 100).toFixed(2);
