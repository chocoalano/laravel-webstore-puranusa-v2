function useDashboard() {
  function formatIDR(n) {
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);
  }
  function formatDate(d) {
    if (!d) return "—";
    try {
      return new Intl.DateTimeFormat("id-ID", { dateStyle: "medium" }).format(new Date(d));
    } catch {
      return d;
    }
  }
  function copyToClipboard(text) {
    window.navigator.clipboard.writeText(text);
  }
  return { formatIDR, formatDate, copyToClipboard };
}
export {
  useDashboard as u
};
