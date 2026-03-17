import { createSSRApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "vue/server-renderer";
async function resolvePageComponent(path, pages) {
  for (const p of Array.isArray(path) ? path : [path]) {
    const page = pages[p];
    if (typeof page === "undefined") {
      continue;
    }
    return typeof page === "function" ? page() : page;
  }
  throw new Error(`Page not found: ${path}`);
}
createServer(
  (page) => createInertiaApp({
    page,
    render: renderToString,
    title: (title) => `${title} — ${"puranusa.id"}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, /* @__PURE__ */ Object.assign({ "./Pages/Article/Index.vue": () => import("./assets/Index-BFdfWLy8.js"), "./Pages/Article/Show.vue": () => import("./assets/Show-DJ6VLoBt.js"), "./Pages/Auth/Checkout/Index.vue": () => import("./assets/Index-BCKQwyVz.js"), "./Pages/Auth/Dashboard/Index.vue": () => import("./assets/Index-BP8kVpHJ.js"), "./Pages/Auth/Dashboard/partials/Addresses.vue": () => import("./assets/Addresses-Ck2Y0c0X.js"), "./Pages/Auth/Dashboard/partials/Bonus.vue": () => import("./assets/Bonus-zyHhdHD_.js"), "./Pages/Auth/Dashboard/partials/DashboardHome.vue": () => import("./assets/DashboardHome-KQ4t98Wt.js"), "./Pages/Auth/Dashboard/partials/DeleteAccount.vue": () => import("./assets/DeleteAccount-bU1gLfP_.js"), "./Pages/Auth/Dashboard/partials/FormAccount.vue": () => import("./assets/FormAccount-Cbv0co9Q.js"), "./Pages/Auth/Dashboard/partials/Lifetime.vue": () => import("./assets/Lifetime-7CB4Aneo.js"), "./Pages/Auth/Dashboard/partials/Mitra.vue": () => import("./assets/Mitra-C4xTaPsT.js"), "./Pages/Auth/Dashboard/partials/Network.vue": () => import("./assets/Network-BQh5QNec.js"), "./Pages/Auth/Dashboard/partials/Orders.vue": () => import("./assets/Orders-BGKWAUh7.js"), "./Pages/Auth/Dashboard/partials/Promo.vue": () => import("./assets/Promo-C4wHRI-n.js"), "./Pages/Auth/Dashboard/partials/Wallet.vue": () => import("./assets/Wallet-BFl3Uy4K.js"), "./Pages/Auth/Dashboard/partials/Zenner.vue": () => import("./assets/Zenner-DKRV35DM.js"), "./Pages/Auth/Login.vue": () => import("./assets/Login-DgPdsXac.js"), "./Pages/Auth/Register.vue": () => import("./assets/Register-DrF32nbx.js"), "./Pages/Auth/WaConfirmationPending.vue": () => import("./assets/WaConfirmationPending-DwH4s0UQ.js"), "./Pages/Home.vue": () => import("./assets/Home-CTtbsGmM.js"), "./Pages/Page/Show.vue": () => import("./assets/Show-B_QysWpd.js"), "./Pages/Shop/Index.vue": () => import("./assets/Index-BlrBBOHH.js"), "./Pages/Shop/Show.vue": () => import("./assets/Show-DOtRaZrO.js") })),
    setup({ App, props, plugin }) {
      return createSSRApp({ render: () => h(App, props) }).use(plugin);
    }
  })
);
