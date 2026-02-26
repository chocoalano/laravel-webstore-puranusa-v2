export function useDashboard() {
    function formatIDR(n: number): string {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n)
    }

    function formatDate(d?: string): string {
        if (!d) return '—'
        try {
            return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(d))
        } catch {
            return d
        }
    }

    function copyToClipboard(text: string): void {
        window.navigator.clipboard.writeText(text)
    }

    return { formatIDR, formatDate, copyToClipboard }
}
