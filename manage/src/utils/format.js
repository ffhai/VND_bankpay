/** 格式化整数金额，不做单位换算。 */
export const formatAmount = (amount) => String(Math.trunc(Number(amount) || 0));
