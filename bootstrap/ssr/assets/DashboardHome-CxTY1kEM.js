import { defineComponent, mergeProps, withCtx, createTextVNode, createVNode, toDisplayString, unref, useSSRContext, openBlock, createBlock, createCommentVNode, computed, Fragment, renderList } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderClass } from "vue/server-renderer";
import { _ as _sfc_main$a } from "./Button-C2UOeJ2u.js";
import { _ as _sfc_main$9 } from "./Icon-4Khzngjd.js";
import { _ as _sfc_main$8 } from "./Card-Bctow_EP.js";
import { u as useDashboard } from "./useDashboard-DEG0AsLD.js";
import { _ as _sfc_main$b } from "./Badge-CZ-Hzv6j.js";
import "defu";
import "reka-ui";
import "@inertiajs/vue3";
import "../ssr.js";
import "@inertiajs/vue3/server";
import "@unhead/vue/client";
import "tailwindcss/colors";
import "hookable";
import "@vueuse/core";
import "ohash/utils";
import "@unhead/vue";
import "ufo";
import "tailwind-variants";
import "@iconify/vue";
const _sfc_main$7 = /* @__PURE__ */ defineComponent({
  __name: "DashboardStatCards",
  __ssrInlineRender: true,
  props: {
    stats: {}
  },
  emits: ["navigate"],
  setup(__props) {
    const { formatIDR } = useDashboard();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Order Total</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats?.orders_total ?? 0)}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Pending: <span class="font-semibold"${_scopeId}>${ssrInterpolate(__props.stats?.orders_pending ?? 0)}</span></p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-receipt",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "orders")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lihat Pesanan `);
                } else {
                  return [
                    createTextVNode(" Lihat Pesanan ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Order Total"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(__props.stats?.orders_total ?? 0), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, [
                    createTextVNode(" Pending: "),
                    createVNode("span", { class: "font-semibold" }, toDisplayString(__props.stats?.orders_pending ?? 0), 1)
                  ])
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-receipt",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "orders")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Lihat Pesanan ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Wallet</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(__props.stats?.wallet_balance ?? 0))}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Siap dipakai checkout</p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-wallet",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4 grid grid-cols-2 gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "wallet")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Detail `);
                } else {
                  return [
                    createTextVNode(" Detail ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "wallet")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Top up `);
                } else {
                  return [
                    createTextVNode(" Top up ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Wallet"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(__props.stats?.wallet_balance ?? 0)), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, "Siap dipakai checkout")
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-wallet",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4 grid grid-cols-2 gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "wallet")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Detail ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  variant: "soft",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "wallet")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Top up ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Statistik Jaringan</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats?.network_total ?? 0)}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Aktif: <span class="font-semibold"${_scopeId}>${ssrInterpolate(__props.stats?.network_active ?? 0)}</span><span class="mx-2 text-gray-400"${_scopeId}>•</span> Level: <span class="font-semibold"${_scopeId}>${ssrInterpolate(__props.stats?.network_level ?? 0)}</span></p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-network",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "network")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Buka Network `);
                } else {
                  return [
                    createTextVNode(" Buka Network ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Statistik Jaringan"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(__props.stats?.network_total ?? 0), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, [
                    createTextVNode(" Aktif: "),
                    createVNode("span", { class: "font-semibold" }, toDisplayString(__props.stats?.network_active ?? 0), 1),
                    createVNode("span", { class: "mx-2 text-gray-400" }, "•"),
                    createTextVNode(" Level: "),
                    createVNode("span", { class: "font-semibold" }, toDisplayString(__props.stats?.network_level ?? 0), 1)
                  ])
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-network",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "network")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Buka Network ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Statistik Bonus</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(__props.stats?.bonus_available ?? 0))}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Bulan ini: <span class="font-semibold"${_scopeId}>${ssrInterpolate(unref(formatIDR)(__props.stats?.bonus_month ?? 0))}</span></p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-coins",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4 grid grid-cols-2 gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "bonus")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Detail `);
                } else {
                  return [
                    createTextVNode(" Detail ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "bonus")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tarik `);
                } else {
                  return [
                    createTextVNode(" Tarik ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Statistik Bonus"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(__props.stats?.bonus_available ?? 0)), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, [
                    createTextVNode(" Bulan ini: "),
                    createVNode("span", { class: "font-semibold" }, toDisplayString(unref(formatIDR)(__props.stats?.bonus_month ?? 0)), 1)
                  ])
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-coins",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4 grid grid-cols-2 gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "bonus")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Detail ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  variant: "soft",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "bonus")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Tarik ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardStatCards.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  __name: "DashboardAddressWidget",
  __ssrInlineRender: true,
  props: {
    defaultAddress: {}
  },
  emits: ["navigate"],
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UButton = _sfc_main$a;
      const _component_UBadge = _sfc_main$b;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"${_scopeId}><div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Kelola Alamat</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Atur alamat default untuk mempercepat checkout. </p></div><div class="flex gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              size: "sm",
              onClick: ($event) => _ctx.$emit("navigate", "addresses")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lihat semua `);
                } else {
                  return [
                    createTextVNode(" Lihat semua ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "solid",
              class: "rounded-xl",
              size: "sm",
              icon: "i-lucide-plus",
              onClick: ($event) => _ctx.$emit("navigate", "addresses")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tambah `);
                } else {
                  return [
                    createTextVNode(" Tambah ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Kelola Alamat"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Atur alamat default untuk mempercepat checkout. ")
                ]),
                createVNode("div", { class: "flex gap-2" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    size: "sm",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Lihat semua ")
                    ]),
                    _: 1
                  }, 8, ["onClick"]),
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "solid",
                    class: "rounded-xl",
                    size: "sm",
                    icon: "i-lucide-plus",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tambah ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
                ])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.defaultAddress) {
              _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><div class="flex items-start justify-between gap-3"${_scopeId}><div class="min-w-0"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.defaultAddress.label)} `);
              if (__props.defaultAddress.is_default) {
                _push2(ssrRenderComponent(_component_UBadge, {
                  label: "Default",
                  color: "success",
                  variant: "soft",
                  size: "xs",
                  class: "ml-2 rounded-full"
                }, null, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(`</p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300"${_scopeId}>${ssrInterpolate(__props.defaultAddress.recipient_name)} • ${ssrInterpolate(__props.defaultAddress.phone)}</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-200"${_scopeId}>${ssrInterpolate(__props.defaultAddress.address_line)}, ${ssrInterpolate(__props.defaultAddress.city)}, ${ssrInterpolate(__props.defaultAddress.province)}, ${ssrInterpolate(__props.defaultAddress.postal_code)}</p></div>`);
              _push2(ssrRenderComponent(_component_UButton, {
                color: "neutral",
                variant: "ghost",
                class: "rounded-xl",
                size: "sm",
                icon: "i-lucide-pencil",
                onClick: ($event) => _ctx.$emit("navigate", "addresses")
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Edit `);
                  } else {
                    return [
                      createTextVNode(" Edit ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div></div>`);
            } else {
              _push2(`<div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 p-6 text-center"${_scopeId}><p class="text-sm text-gray-600 dark:text-gray-300"${_scopeId}>Kamu belum punya alamat. Tambahkan sekarang.</p><div class="mt-4"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UButton, {
                color: "primary",
                variant: "solid",
                class: "rounded-xl",
                icon: "i-lucide-plus",
                onClick: ($event) => _ctx.$emit("navigate", "addresses")
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Tambah alamat `);
                  } else {
                    return [
                      createTextVNode(" Tambah alamat ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div></div>`);
            }
          } else {
            return [
              __props.defaultAddress ? (openBlock(), createBlock("div", {
                key: 0,
                class: "rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
              }, [
                createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                  createVNode("div", { class: "min-w-0" }, [
                    createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, [
                      createTextVNode(toDisplayString(__props.defaultAddress.label) + " ", 1),
                      __props.defaultAddress.is_default ? (openBlock(), createBlock(_component_UBadge, {
                        key: 0,
                        label: "Default",
                        color: "success",
                        variant: "soft",
                        size: "xs",
                        class: "ml-2 rounded-full"
                      })) : createCommentVNode("", true)
                    ]),
                    createVNode("p", { class: "mt-1 text-sm text-gray-600 dark:text-gray-300" }, toDisplayString(__props.defaultAddress.recipient_name) + " • " + toDisplayString(__props.defaultAddress.phone), 1),
                    createVNode("p", { class: "mt-2 text-sm text-gray-700 dark:text-gray-200" }, toDisplayString(__props.defaultAddress.address_line) + ", " + toDisplayString(__props.defaultAddress.city) + ", " + toDisplayString(__props.defaultAddress.province) + ", " + toDisplayString(__props.defaultAddress.postal_code), 1)
                  ]),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "ghost",
                    class: "rounded-xl",
                    size: "sm",
                    icon: "i-lucide-pencil",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Edit ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
                ])
              ])) : (openBlock(), createBlock("div", {
                key: 1,
                class: "rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 p-6 text-center"
              }, [
                createVNode("p", { class: "text-sm text-gray-600 dark:text-gray-300" }, "Kamu belum punya alamat. Tambahkan sekarang."),
                createVNode("div", { class: "mt-4" }, [
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "solid",
                    class: "rounded-xl",
                    icon: "i-lucide-plus",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tambah alamat ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
                ])
              ]))
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardAddressWidget.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "DashboardNetworkProfile",
  __ssrInlineRender: true,
  props: {
    customer: {},
    networkProfile: {}
  },
  setup(__props) {
    const { formatIDR, copyToClipboard } = useDashboard();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><div class="flex items-center gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-circle-user-round",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`<p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Profil Network</p></div>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "ghost",
              size: "xs",
              class: "rounded-xl",
              icon: "i-lucide-arrow-right"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lengkapi Profil `);
                } else {
                  return [
                    createTextVNode(" Lengkapi Profil ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("div", { class: "flex items-center gap-3" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-circle-user-round",
                    class: "size-5 text-gray-500 dark:text-gray-300"
                  }),
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Profil Network")
                ]),
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "ghost",
                  size: "xs",
                  class: "rounded-xl",
                  icon: "i-lucide-arrow-right"
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Lengkapi Profil ")
                  ]),
                  _: 1
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="grid grid-cols-2 gap-2"${_scopeId}><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Nama</p><p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.customer?.name ?? "—")}</p></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Username</p><p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.networkProfile?.username ?? "—")}</p></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Level</p><p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.networkProfile?.level ?? "—")}</p></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Kode Referral</p><div class="mt-0.5 flex items-center gap-1.5"${_scopeId}><p class="truncate font-mono text-sm font-semibold tracking-wider text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.networkProfile?.referral_code ?? "—")}</p>`);
            if (__props.networkProfile?.referral_code) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "neutral",
                variant: "ghost",
                size: "xs",
                icon: "i-lucide-copy",
                class: "shrink-0 rounded-lg",
                onClick: ($event) => unref(copyToClipboard)(__props.networkProfile?.referral_code ?? "")
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div><div class="col-span-2 rounded-xl bg-primary-50 dark:bg-primary-950/30 px-3 py-2.5"${_scopeId}><p class="text-xs text-primary-600 dark:text-primary-400"${_scopeId}>Saldo</p><p class="mt-0.5 text-lg font-extrabold text-primary-700 dark:text-primary-300"${_scopeId}>${ssrInterpolate(unref(formatIDR)(__props.networkProfile?.balance ?? 0))}</p></div></div>`);
          } else {
            return [
              createVNode("div", { class: "grid grid-cols-2 gap-2" }, [
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Nama"),
                  createVNode("p", { class: "mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.customer?.name ?? "—"), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Username"),
                  createVNode("p", { class: "mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.networkProfile?.username ?? "—"), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Level"),
                  createVNode("p", { class: "mt-0.5 text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.networkProfile?.level ?? "—"), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Kode Referral"),
                  createVNode("div", { class: "mt-0.5 flex items-center gap-1.5" }, [
                    createVNode("p", { class: "truncate font-mono text-sm font-semibold tracking-wider text-gray-900 dark:text-white" }, toDisplayString(__props.networkProfile?.referral_code ?? "—"), 1),
                    __props.networkProfile?.referral_code ? (openBlock(), createBlock(_component_UButton, {
                      key: 0,
                      color: "neutral",
                      variant: "ghost",
                      size: "xs",
                      icon: "i-lucide-copy",
                      class: "shrink-0 rounded-lg",
                      onClick: ($event) => unref(copyToClipboard)(__props.networkProfile?.referral_code ?? "")
                    }, null, 8, ["onClick"])) : createCommentVNode("", true)
                  ])
                ]),
                createVNode("div", { class: "col-span-2 rounded-xl bg-primary-50 dark:bg-primary-950/30 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-primary-600 dark:text-primary-400" }, "Saldo"),
                  createVNode("p", { class: "mt-0.5 text-lg font-extrabold text-primary-700 dark:text-primary-300" }, toDisplayString(unref(formatIDR)(__props.networkProfile?.balance ?? 0)), 1)
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardNetworkProfile.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "DashboardNetworkStats",
  __ssrInlineRender: true,
  props: {
    networkStats: {}
  },
  emits: ["navigate"],
  setup(__props) {
    const { formatIDR } = useDashboard();
    const props = __props;
    const statItems = computed(() => [
      { label: "Jaringan Kiri", value: String(props.networkStats?.left_count ?? 0) },
      { label: "Jaringan Kanan", value: String(props.networkStats?.right_count ?? 0) },
      { label: "Total Downline", value: String(props.networkStats?.total_downline ?? 0) },
      { label: "Omset Group", value: formatIDR(props.networkStats?.omset_group ?? 0) },
      { label: "Omset NB Kiri", value: formatIDR(props.networkStats?.omset_nb_left ?? 0) },
      { label: "Omset NB Kanan", value: formatIDR(props.networkStats?.omset_nb_right ?? 0) },
      { label: "Omset Retail Kiri", value: formatIDR(props.networkStats?.omset_retail_left ?? 0) },
      { label: "Omset Retail Kanan", value: formatIDR(props.networkStats?.omset_retail_right ?? 0) }
    ]);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><div class="flex items-center gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-bar-chart-2",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`<p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Statistik Jaringan</p></div>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "ghost",
              size: "xs",
              class: "rounded-xl",
              icon: "i-lucide-arrow-right",
              onClick: ($event) => _ctx.$emit("navigate", "network")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Selengkapnya `);
                } else {
                  return [
                    createTextVNode(" Selengkapnya ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("div", { class: "flex items-center gap-3" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-bar-chart-2",
                    class: "size-5 text-gray-500 dark:text-gray-300"
                  }),
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Statistik Jaringan")
                ]),
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "ghost",
                  size: "xs",
                  class: "rounded-xl",
                  icon: "i-lucide-arrow-right",
                  onClick: ($event) => _ctx.$emit("navigate", "network")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Selengkapnya ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="grid grid-cols-2 gap-2"${_scopeId}><!--[-->`);
            ssrRenderList(statItems.value, (item) => {
              _push2(`<div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(item.label)}</p><p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(item.value)}</p></div>`);
            });
            _push2(`<!--]--></div>`);
          } else {
            return [
              createVNode("div", { class: "grid grid-cols-2 gap-2" }, [
                (openBlock(true), createBlock(Fragment, null, renderList(statItems.value, (item) => {
                  return openBlock(), createBlock("div", {
                    key: item.label,
                    class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"
                  }, [
                    createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(item.label), 1),
                    createVNode("p", { class: "mt-0.5 text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(item.value), 1)
                  ]);
                }), 128))
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardNetworkStats.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "DashboardMemberCard",
  __ssrInlineRender: true,
  props: {
    customer: {}
  },
  emits: ["navigate"],
  setup(__props) {
    const { formatDate } = useDashboard();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Member Sejak</p>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-calendar",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Member Sejak"),
                createVNode(_component_UIcon, {
                  name: "i-lucide-calendar",
                  class: "size-5 text-gray-500 dark:text-gray-300"
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-2"${_scopeId}><p class="text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatDate)(__props.customer?.member_since))}</p><p class="text-sm text-gray-600 dark:text-gray-300"${_scopeId}> Terima kasih sudah menjadi bagian dari kami. Kamu bisa cek aktivitas dan benefit member di menu Zenner. </p><div class="mt-4 flex gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "soft",
              class: "rounded-xl",
              size: "sm",
              icon: "i-lucide-sparkles",
              onClick: ($event) => _ctx.$emit("navigate", "zenner")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Buka Zenner `);
                } else {
                  return [
                    createTextVNode(" Buka Zenner ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              size: "sm",
              icon: "i-lucide-trophy",
              onClick: ($event) => _ctx.$emit("navigate", "lifetime")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lifetime `);
                } else {
                  return [
                    createTextVNode(" Lifetime ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-2" }, [
                createVNode("p", { class: "text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(unref(formatDate)(__props.customer?.member_since)), 1),
                createVNode("p", { class: "text-sm text-gray-600 dark:text-gray-300" }, " Terima kasih sudah menjadi bagian dari kami. Kamu bisa cek aktivitas dan benefit member di menu Zenner. "),
                createVNode("div", { class: "mt-4 flex gap-2" }, [
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "soft",
                    class: "rounded-xl",
                    size: "sm",
                    icon: "i-lucide-sparkles",
                    onClick: ($event) => _ctx.$emit("navigate", "zenner")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Buka Zenner ")
                    ]),
                    _: 1
                  }, 8, ["onClick"]),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    size: "sm",
                    icon: "i-lucide-trophy",
                    onClick: ($event) => _ctx.$emit("navigate", "lifetime")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Lifetime ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardMemberCard.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "DashboardLifetimeCard",
  __ssrInlineRender: true,
  props: {
    stats: {}
  },
  setup(__props) {
    const { formatIDR } = useDashboard();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Lifetime</p>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-trophy",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Lifetime"),
                createVNode(_component_UIcon, {
                  name: "i-lucide-trophy",
                  class: "size-5 text-gray-500 dark:text-gray-300"
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-3"${_scopeId}><div class="flex items-center justify-between text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Bonus lifetime</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(__props.stats?.bonus_lifetime ?? 0))}</span></div><div class="flex items-center justify-between text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Promo aktif</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats?.promo_active ?? 0)}</span></div><div class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Rekomendasi</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li${_scopeId}>Aktifkan network untuk unlock bonus lebih besar.</li><li${_scopeId}>Cek promo sebelum checkout agar lebih hemat.</li></ul></div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-3" }, [
                createVNode("div", { class: "flex items-center justify-between text-sm" }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Bonus lifetime"),
                  createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(__props.stats?.bonus_lifetime ?? 0)), 1)
                ]),
                createVNode("div", { class: "flex items-center justify-between text-sm" }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Promo aktif"),
                  createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.stats?.promo_active ?? 0), 1)
                ]),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                  createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Rekomendasi"),
                  createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                    createVNode("li", null, "Aktifkan network untuk unlock bonus lebih besar."),
                    createVNode("li", null, "Cek promo sebelum checkout agar lebih hemat.")
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardLifetimeCard.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "DashboardSecurityZone",
  __ssrInlineRender: true,
  props: {
    securitySummary: {}
  },
  emits: ["navigate"],
  setup(__props) {
    const { formatDate } = useDashboard();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UBadge = _sfc_main$b;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between"${_scopeId}><div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Keamanan Akun</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Lindungi akun dan data kamu. Gunakan menu Lock untuk pengaturan keamanan. </p><div class="mt-2 flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              label: `Status akun: ${__props.securitySummary?.account_status_label ?? "Prospek"}`,
              color: "neutral",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              label: __props.securitySummary?.email_verified ? "Email terverifikasi" : "Email belum terverifikasi",
              color: __props.securitySummary?.email_verified ? "success" : "warning",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-shield",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Keamanan Akun"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Lindungi akun dan data kamu. Gunakan menu Lock untuk pengaturan keamanan. "),
                  createVNode("div", { class: "mt-2 flex flex-wrap items-center gap-2" }, [
                    createVNode(_component_UBadge, {
                      label: `Status akun: ${__props.securitySummary?.account_status_label ?? "Prospek"}`,
                      color: "neutral",
                      variant: "soft",
                      class: "rounded-full"
                    }, null, 8, ["label"]),
                    createVNode(_component_UBadge, {
                      label: __props.securitySummary?.email_verified ? "Email terverifikasi" : "Email belum terverifikasi",
                      color: __props.securitySummary?.email_verified ? "success" : "warning",
                      variant: "soft",
                      class: "rounded-full"
                    }, null, 8, ["label", "color"])
                  ])
                ]),
                createVNode(_component_UIcon, {
                  name: "i-lucide-shield",
                  class: "size-5 text-gray-500 dark:text-gray-300"
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="mb-4 rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Status keamanan</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li class="${ssrRenderClass(__props.securitySummary?.has_bank_account ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}> Data rekening ${ssrInterpolate(__props.securitySummary?.has_bank_account ? "sudah lengkap" : "belum lengkap")}</li><li class="${ssrRenderClass(__props.securitySummary?.has_npwp ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}> NPWP ${ssrInterpolate(__props.securitySummary?.has_npwp ? "sudah terdaftar" : "belum terdaftar")}</li><li${_scopeId}> Order terakhir: <span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.securitySummary?.last_order_at ? unref(formatDate)(__props.securitySummary.last_order_at) : "Belum ada order")}</span></li></ul></div>`);
            _push2(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border border-rose-200 bg-rose-50/60 dark:border-rose-900/50 dark:bg-rose-950/30" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex items-start gap-3"${_scopeId2}><div class="grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-user-x",
                    class: "size-5 text-rose-600 dark:text-rose-300"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="min-w-0"${_scopeId2}><p class="text-sm font-semibold text-rose-800 dark:text-rose-200"${_scopeId2}>Delete Account</p><p class="mt-1 text-xs text-rose-700 dark:text-rose-300"${_scopeId2}> Aksi ini permanen. Pastikan kamu sudah backup data penting. </p><div class="mt-3"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    color: "error",
                    variant: "solid",
                    size: "sm",
                    class: "rounded-xl",
                    onClick: ($event) => _ctx.$emit("navigate", "delete")
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(` Hapus Akun `);
                      } else {
                        return [
                          createTextVNode(" Hapus Akun ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div></div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex items-start gap-3" }, [
                      createVNode("div", { class: "grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50" }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-user-x",
                          class: "size-5 text-rose-600 dark:text-rose-300"
                        })
                      ]),
                      createVNode("div", { class: "min-w-0" }, [
                        createVNode("p", { class: "text-sm font-semibold text-rose-800 dark:text-rose-200" }, "Delete Account"),
                        createVNode("p", { class: "mt-1 text-xs text-rose-700 dark:text-rose-300" }, " Aksi ini permanen. Pastikan kamu sudah backup data penting. "),
                        createVNode("div", { class: "mt-3" }, [
                          createVNode(_component_UButton, {
                            color: "error",
                            variant: "solid",
                            size: "sm",
                            class: "rounded-xl",
                            onClick: ($event) => _ctx.$emit("navigate", "delete")
                          }, {
                            default: withCtx(() => [
                              createTextVNode(" Hapus Akun ")
                            ]),
                            _: 1
                          }, 8, ["onClick"])
                        ])
                      ])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode("div", { class: "mb-4 rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Status keamanan"),
                createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                  createVNode("li", {
                    class: __props.securitySummary?.has_bank_account ? "text-emerald-600 dark:text-emerald-400" : ""
                  }, " Data rekening " + toDisplayString(__props.securitySummary?.has_bank_account ? "sudah lengkap" : "belum lengkap"), 3),
                  createVNode("li", {
                    class: __props.securitySummary?.has_npwp ? "text-emerald-600 dark:text-emerald-400" : ""
                  }, " NPWP " + toDisplayString(__props.securitySummary?.has_npwp ? "sudah terdaftar" : "belum terdaftar"), 3),
                  createVNode("li", null, [
                    createTextVNode(" Order terakhir: "),
                    createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.securitySummary?.last_order_at ? unref(formatDate)(__props.securitySummary.last_order_at) : "Belum ada order"), 1)
                  ])
                ])
              ]),
              createVNode(_component_UCard, { class: "rounded-2xl border border-rose-200 bg-rose-50/60 dark:border-rose-900/50 dark:bg-rose-950/30" }, {
                default: withCtx(() => [
                  createVNode("div", { class: "flex items-start gap-3" }, [
                    createVNode("div", { class: "grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-user-x",
                        class: "size-5 text-rose-600 dark:text-rose-300"
                      })
                    ]),
                    createVNode("div", { class: "min-w-0" }, [
                      createVNode("p", { class: "text-sm font-semibold text-rose-800 dark:text-rose-200" }, "Delete Account"),
                      createVNode("p", { class: "mt-1 text-xs text-rose-700 dark:text-rose-300" }, " Aksi ini permanen. Pastikan kamu sudah backup data penting. "),
                      createVNode("div", { class: "mt-3" }, [
                        createVNode(_component_UButton, {
                          color: "error",
                          variant: "solid",
                          size: "sm",
                          class: "rounded-xl",
                          onClick: ($event) => _ctx.$emit("navigate", "delete")
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Hapus Akun ")
                          ]),
                          _: 1
                        }, 8, ["onClick"])
                      ])
                    ])
                  ])
                ]),
                _: 1
              })
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardSecurityZone.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "DashboardHome",
  __ssrInlineRender: true,
  props: {
    customer: {},
    defaultAddress: {},
    stats: {},
    networkProfile: {},
    networkStats: {},
    securitySummary: {}
  },
  emits: ["navigate"],
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}>`);
      _push(ssrRenderComponent(_sfc_main$7, {
        stats: __props.stats,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$6, {
        "default-address": __props.defaultAddress,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(`<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">`);
      _push(ssrRenderComponent(_sfc_main$5, {
        customer: __props.customer,
        "network-profile": __props.networkProfile
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$4, {
        "network-stats": __props.networkStats,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(`</div><div class="grid grid-cols-1 gap-4 lg:grid-cols-2">`);
      _push(ssrRenderComponent(_sfc_main$3, {
        customer: __props.customer,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$2, { stats: __props.stats }, null, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_sfc_main$1, {
        "security-summary": __props.securitySummary,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/DashboardHome.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
