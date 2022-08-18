export const isUrl = (str) => {
    const url = /\b(https?|ftp|file):\/\/[-A-Za-z0-9+&@#\/%?=~_|!:,.;]*[-A-Za-z0-9+&@#/%=~_|]/i
    return url.test(str)
}
