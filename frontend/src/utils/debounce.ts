export function debounce<T extends (...args: any[]) => any>(fn: T, delay: number) {
  let timeoutId: ReturnType<typeof setTimeout> | null = null
  return function (this: any, ...args: Parameters<T>) {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      fn.apply(this, args)
    }, delay)
  }
}
