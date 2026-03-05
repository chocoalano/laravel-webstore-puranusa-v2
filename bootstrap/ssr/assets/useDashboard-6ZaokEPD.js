import { useToast } from "@nuxt/ui/runtime/composables/useToast.js";
function useDashboard() {
  const toast = useToast();
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
  async function copyToClipboard(text) {
    const referralCode = text.trim();
    if (!referralCode) {
      toast.add({
        title: "Kode referral tidak tersedia",
        description: "Tidak ada kode referral yang bisa disalin.",
        color: "warning"
      });
      return;
    }
    const referralUrl = `${window.location.origin}/register?referral_code=${encodeURIComponent(referralCode)}`;
    let copied = false;
    try {
      await window.navigator.clipboard.writeText(referralUrl);
      copied = true;
    } catch {
      const textarea = document.createElement("textarea");
      textarea.value = referralUrl;
      textarea.setAttribute("readonly", "");
      textarea.style.position = "fixed";
      textarea.style.opacity = "0";
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      copied = document.execCommand("copy");
      document.body.removeChild(textarea);
    }
    if (copied) {
      toast.add({
        title: "Link referral disalin",
        description: "Tautan pendaftaran berhasil disalin ke clipboard.",
        color: "success"
      });
      return;
    }
    toast.add({
      title: "Gagal menyalin link",
      description: "Coba lagi dalam beberapa saat.",
      color: "error"
    });
  }
  return { formatIDR, formatDate, copyToClipboard };
}
export {
  useDashboard as u
};
