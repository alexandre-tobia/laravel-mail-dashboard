export function formatDate(iso: string | null): string {
    if (!iso) return ''

    return new Date(iso).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    })
}

export function timeAgo(iso: string | null): string {
    if (!iso) return ''

    const seconds = Math.round((Date.now() - new Date(iso).getTime()) / 1000)

    if (seconds < 60) return 'just now'

    const units: [Intl.RelativeTimeFormatUnit, number][] = [
        ['year', 31536000],
        ['month', 2592000],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ]

    const formatter = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' })

    for (const [unit, secondsInUnit] of units) {
        if (Math.abs(seconds) >= secondsInUnit) {
            return formatter.format(-Math.round(seconds / secondsInUnit), unit)
        }
    }

    return 'just now'
}

export function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}
