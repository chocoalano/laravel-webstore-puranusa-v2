import { _ as _sfc_main$6 } from "./Button-C2UOeJ2u.js";
import { _ as _sfc_main$5 } from "./Icon-4Khzngjd.js";
import { _ as _sfc_main$e } from "./Card-Bctow_EP.js";
import { defineComponent, computed, mergeProps, withCtx, createTextVNode, useSSRContext, useModel, createVNode, openBlock, createBlock, createCommentVNode, toDisplayString, Fragment, renderList, mergeModels, ref, reactive, watch, nextTick, unref, isRef } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrInterpolate } from "vue/server-renderer";
import { _ as _sfc_main$7 } from "./Badge-CZ-Hzv6j.js";
import { _ as _sfc_main$d } from "./SelectMenu-oE01C-PZ.js";
import { _ as _sfc_main$c } from "./Textarea-CnN6KAd1.js";
import { _ as _sfc_main$b } from "./Checkbox-B2eEIhTD.js";
import { _ as _sfc_main$a } from "./Input-ChYVLMxJ.js";
import { _ as _sfc_main$9 } from "./FormField-DcQ8h94p.js";
import { _ as _sfc_main$8 } from "./Modal-BOfqalmp.js";
import { router } from "@inertiajs/vue3";
import "defu";
import "reka-ui";
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
import "./usePortal-EQErrF6h.js";
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "AddressList",
  __ssrInlineRender: true,
  props: {
    addresses: {},
    settingDefault: {}
  },
  emits: ["create", "edit", "delete", "setDefault"],
  setup(__props) {
    const props = __props;
    const hasAddresses = computed(() => props.addresses.length > 0);
    function formatPhoneDisplay(phone) {
      return phone || "—";
    }
    function fullAddress(address) {
      const secondLine = address.address_line2 ? `, ${address.address_line2}` : "";
      const postalCode = address.postal_code ? `, ${address.postal_code}` : "";
      return `${address.address_line1}${secondLine}, ${address.city_label}, ${address.province_label}${postalCode}`;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$5;
      const _component_UButton = _sfc_main$6;
      const _component_UBadge = _sfc_main$7;
      if (!hasAddresses.value) {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "py-12 text-center text-gray-500 dark:text-gray-400" }, _attrs))}>`);
        _push(ssrRenderComponent(_component_UIcon, {
          name: "i-lucide-map-pin",
          class: "mx-auto size-10 opacity-40"
        }, null, _parent));
        _push(`<p class="mt-3 text-sm">Belum ada alamat tersimpan.</p><p class="mt-1 text-xs">Tambahkan alamat untuk mempercepat proses checkout.</p><div class="mt-4">`);
        _push(ssrRenderComponent(_component_UButton, {
          color: "primary",
          variant: "solid",
          class: "rounded-xl",
          icon: "i-lucide-plus",
          onClick: ($event) => _ctx.$emit("create")
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Tambah Alamat Pertama `);
            } else {
              return [
                createTextVNode(" Tambah Alamat Pertama ")
              ];
            }
          }),
          _: 1
        }, _parent));
        _push(`</div></div>`);
      } else {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-3" }, _attrs))}><!--[-->`);
        ssrRenderList(__props.addresses, (address) => {
          _push(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"><div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><p class="text-sm font-semibold text-gray-900 dark:text-white">${ssrInterpolate(address.label || "Alamat")}</p>`);
          if (address.is_default) {
            _push(ssrRenderComponent(_component_UBadge, {
              label: "Default",
              color: "success",
              variant: "soft",
              size: "xs",
              class: "rounded-full"
            }, null, _parent));
          } else {
            _push(`<!---->`);
          }
          _push(`</div><p class="mt-1 text-sm text-gray-600 dark:text-gray-300">${ssrInterpolate(address.recipient_name)} • ${ssrInterpolate(formatPhoneDisplay(address.recipient_phone))}</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-200">${ssrInterpolate(fullAddress(address))}</p>`);
          if (address.description) {
            _push(`<p class="mt-2 text-xs text-gray-500 dark:text-gray-400"> Catatan: ${ssrInterpolate(address.description)}</p>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div><div class="flex flex-wrap items-center gap-2 sm:justify-end">`);
          if (!address.is_default) {
            _push(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              icon: "i-lucide-check",
              loading: !!__props.settingDefault[String(address.id)],
              onClick: ($event) => _ctx.$emit("setDefault", address)
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(` Jadikan Default `);
                } else {
                  return [
                    createTextVNode(" Jadikan Default ")
                  ];
                }
              }),
              _: 2
            }, _parent));
          } else {
            _push(`<!---->`);
          }
          _push(ssrRenderComponent(_component_UButton, {
            color: "neutral",
            variant: "outline",
            size: "sm",
            class: "rounded-xl",
            icon: "i-lucide-pencil",
            onClick: ($event) => _ctx.$emit("edit", address)
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(` Edit `);
              } else {
                return [
                  createTextVNode(" Edit ")
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(ssrRenderComponent(_component_UButton, {
            color: "error",
            variant: "soft",
            size: "sm",
            class: "rounded-xl",
            icon: "i-lucide-trash-2",
            onClick: ($event) => _ctx.$emit("delete", address)
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(` Hapus `);
              } else {
                return [
                  createTextVNode(" Hapus ")
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`</div></div></div>`);
        });
        _push(`<!--]--></div>`);
      }
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/addresses/AddressList.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "AddressFormModal",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    mode: {},
    form: {},
    errors: {},
    submitting: { type: Boolean },
    provinceItems: {},
    cityItems: {},
    districtItems: {}
  }, {
    "open": { type: Boolean, ...{ required: true } },
    "openModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["submit", "reset"], ["update:open"]),
  setup(__props) {
    const isOpen = useModel(__props, "open");
    const props = __props;
    const title = computed(() => props.mode === "create" ? "Tambah Alamat" : "Edit Alamat");
    const description = computed(
      () => props.mode === "create" ? "Isi detail alamat untuk pengiriman & checkout." : "Perbarui detail alamat yang sudah tersimpan."
    );
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$8;
      const _component_UFormField = _sfc_main$9;
      const _component_UInput = _sfc_main$a;
      const _component_UCheckbox = _sfc_main$b;
      const _component_UTextarea = _sfc_main$c;
      const _component_USelectMenu = _sfc_main$d;
      const _component_UButton = _sfc_main$6;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: isOpen.value,
        "onUpdate:open": ($event) => isOpen.value = $event,
        title: title.value,
        description: description.value,
        scrollable: ""
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="w-full max-w-2xl space-y-4"${_scopeId}><div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Label (opsional)",
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.label,
                    "onUpdate:modelValue": ($event) => __props.form.label = $event,
                    placeholder: "Contoh: Rumah, Kantor",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.label,
                      "onUpdate:modelValue": ($event) => __props.form.label = $event,
                      placeholder: "Contoh: Rumah, Kantor",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="w-full"${_scopeId}><p class="text-sm font-medium text-gray-900 dark:text-white"${_scopeId}>Jadikan default</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Default dipakai otomatis saat checkout.</p><div class="mt-2 flex items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UCheckbox, {
              modelValue: __props.form.is_default,
              "onUpdate:modelValue": ($event) => __props.form.is_default = $event
            }, null, _parent2, _scopeId));
            _push2(`<span class="text-sm text-gray-700 dark:text-gray-200"${_scopeId}>Set sebagai alamat utama</span></div></div></div><div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nama penerima",
              required: "",
              error: __props.errors.recipient_name,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.recipient_name,
                    "onUpdate:modelValue": ($event) => __props.form.recipient_name = $event,
                    placeholder: "Nama lengkap",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.recipient_name,
                      "onUpdate:modelValue": ($event) => __props.form.recipient_name = $event,
                      placeholder: "Nama lengkap",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "No. HP penerima",
              required: "",
              error: __props.errors.recipient_phone,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.recipient_phone,
                    "onUpdate:modelValue": ($event) => __props.form.recipient_phone = $event,
                    placeholder: "08xxxxxxxxxx",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.recipient_phone,
                      "onUpdate:modelValue": ($event) => __props.form.recipient_phone = $event,
                      placeholder: "08xxxxxxxxxx",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Alamat utama",
              required: "",
              error: __props.errors.address_line1,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UTextarea, {
                    modelValue: __props.form.address_line1,
                    "onUpdate:modelValue": ($event) => __props.form.address_line1 = $event,
                    placeholder: "Jalan, RT/RW, nomor, patokan...",
                    rows: 3,
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UTextarea, {
                      modelValue: __props.form.address_line1,
                      "onUpdate:modelValue": ($event) => __props.form.address_line1 = $event,
                      placeholder: "Jalan, RT/RW, nomor, patokan...",
                      rows: 3,
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Alamat tambahan (opsional)",
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.address_line2,
                    "onUpdate:modelValue": ($event) => __props.form.address_line2 = $event,
                    placeholder: "Contoh: Blok A No. 12",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.address_line2,
                      "onUpdate:modelValue": ($event) => __props.form.address_line2 = $event,
                      placeholder: "Contoh: Blok A No. 12",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Provinsi",
              required: "",
              error: __props.errors.province_id || __props.errors.province_label,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="space-y-2"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: __props.form.province_id,
                    "onUpdate:modelValue": ($event) => __props.form.province_id = $event,
                    items: __props.provinceItems,
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih provinsi",
                    disabled: __props.provinceItems.length === 0,
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  if (__props.provinceItems.length === 0) {
                    _push3(`<p class="text-xs text-amber-600 dark:text-amber-300"${_scopeId2}> Data target pengiriman untuk provinsi belum tersedia. </p>`);
                  } else {
                    _push3(`<!---->`);
                  }
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "space-y-2" }, [
                      createVNode(_component_USelectMenu, {
                        modelValue: __props.form.province_id,
                        "onUpdate:modelValue": ($event) => __props.form.province_id = $event,
                        items: __props.provinceItems,
                        "value-key": "value",
                        "label-key": "label",
                        placeholder: "Pilih provinsi",
                        disabled: __props.provinceItems.length === 0,
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "disabled"]),
                      __props.provinceItems.length === 0 ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "text-xs text-amber-600 dark:text-amber-300"
                      }, " Data target pengiriman untuk provinsi belum tersedia. ")) : createCommentVNode("", true)
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Kota/Kab",
              required: "",
              error: __props.errors.city_id || __props.errors.city_label,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="space-y-2"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: __props.form.city_id,
                    "onUpdate:modelValue": ($event) => __props.form.city_id = $event,
                    items: __props.cityItems,
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih kota/kab",
                    disabled: !__props.form.province_id || __props.cityItems.length === 0,
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  if (__props.form.province_id && __props.cityItems.length === 0) {
                    _push3(`<p class="text-xs text-amber-600 dark:text-amber-300"${_scopeId2}> Kota/Kab untuk provinsi ini belum tersedia di target pengiriman. </p>`);
                  } else {
                    _push3(`<!---->`);
                  }
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "space-y-2" }, [
                      createVNode(_component_USelectMenu, {
                        modelValue: __props.form.city_id,
                        "onUpdate:modelValue": ($event) => __props.form.city_id = $event,
                        items: __props.cityItems,
                        "value-key": "value",
                        "label-key": "label",
                        placeholder: "Pilih kota/kab",
                        disabled: !__props.form.province_id || __props.cityItems.length === 0,
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "disabled"]),
                      __props.form.province_id && __props.cityItems.length === 0 ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "text-xs text-amber-600 dark:text-amber-300"
                      }, " Kota/Kab untuk provinsi ini belum tersedia di target pengiriman. ")) : createCommentVNode("", true)
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Kecamatan",
              required: "",
              error: __props.errors.district,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="space-y-2"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: __props.form.district,
                    "onUpdate:modelValue": ($event) => __props.form.district = $event,
                    items: __props.districtItems,
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih kecamatan",
                    disabled: !__props.form.city_id || __props.districtItems.length === 0,
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  if (__props.form.city_id && __props.districtItems.length === 0) {
                    _push3(`<p class="text-xs text-amber-600 dark:text-amber-300"${_scopeId2}> Kecamatan untuk kota ini belum tersedia di target pengiriman. </p>`);
                  } else {
                    _push3(`<!---->`);
                  }
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "space-y-2" }, [
                      createVNode(_component_USelectMenu, {
                        modelValue: __props.form.district,
                        "onUpdate:modelValue": ($event) => __props.form.district = $event,
                        items: __props.districtItems,
                        "value-key": "value",
                        "label-key": "label",
                        placeholder: "Pilih kecamatan",
                        disabled: !__props.form.city_id || __props.districtItems.length === 0,
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "disabled"]),
                      __props.form.city_id && __props.districtItems.length === 0 ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "text-xs text-amber-600 dark:text-amber-300"
                      }, " Kecamatan untuk kota ini belum tersedia di target pengiriman. ")) : createCommentVNode("", true)
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "District Lion (auto)",
              error: __props.errors.district_lion,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.district_lion,
                    "onUpdate:modelValue": ($event) => __props.form.district_lion = $event,
                    placeholder: "Terisi otomatis dari target pengiriman",
                    readonly: "",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.district_lion,
                      "onUpdate:modelValue": ($event) => __props.form.district_lion = $event,
                      placeholder: "Terisi otomatis dari target pengiriman",
                      readonly: "",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Kode pos (opsional)",
              error: __props.errors.postal_code,
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.postal_code,
                    "onUpdate:modelValue": ($event) => __props.form.postal_code = $event,
                    placeholder: "12345",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.postal_code,
                      "onUpdate:modelValue": ($event) => __props.form.postal_code = $event,
                      placeholder: "12345",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Negara",
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.country,
                    "onUpdate:modelValue": ($event) => __props.form.country = $event,
                    placeholder: "Indonesia",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.country,
                      "onUpdate:modelValue": ($event) => __props.form.country = $event,
                      placeholder: "Indonesia",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Catatan (opsional)",
              class: "w-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: __props.form.description,
                    "onUpdate:modelValue": ($event) => __props.form.description = $event,
                    placeholder: "Contoh: Titip satpam / rumah pagar hitam",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.description,
                      "onUpdate:modelValue": ($event) => __props.form.description = $event,
                      placeholder: "Contoh: Titip satpam / rumah pagar hitam",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (Object.keys(__props.errors).length) {
              _push2(`<div class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200"${_scopeId}><p class="font-semibold"${_scopeId}>Periksa kembali:</p><ul class="mt-1 list-disc pl-5 space-y-1"${_scopeId}><!--[-->`);
              ssrRenderList(__props.errors, (message, key) => {
                _push2(`<li${_scopeId}>${ssrInterpolate(message)}</li>`);
              });
              _push2(`<!--]--></ul></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              disabled: __props.submitting,
              onClick: ($event) => isOpen.value = false
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Batal `);
                } else {
                  return [
                    createTextVNode(" Batal ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="flex gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "ghost",
              class: "rounded-xl",
              disabled: __props.submitting,
              onClick: ($event) => _ctx.$emit("reset")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Reset `);
                } else {
                  return [
                    createTextVNode(" Reset ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "solid",
              class: "rounded-xl",
              loading: __props.submitting,
              onClick: ($event) => _ctx.$emit("submit")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(__props.mode === "create" ? "Simpan" : "Update")}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(__props.mode === "create" ? "Simpan" : "Update"), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "w-full max-w-2xl space-y-4" }, [
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Label (opsional)",
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: __props.form.label,
                        "onUpdate:modelValue": ($event) => __props.form.label = $event,
                        placeholder: "Contoh: Rumah, Kantor",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }),
                  createVNode("div", { class: "w-full" }, [
                    createVNode("p", { class: "text-sm font-medium text-gray-900 dark:text-white" }, "Jadikan default"),
                    createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, "Default dipakai otomatis saat checkout."),
                    createVNode("div", { class: "mt-2 flex items-center gap-2" }, [
                      createVNode(_component_UCheckbox, {
                        modelValue: __props.form.is_default,
                        "onUpdate:modelValue": ($event) => __props.form.is_default = $event
                      }, null, 8, ["modelValue", "onUpdate:modelValue"]),
                      createVNode("span", { class: "text-sm text-gray-700 dark:text-gray-200" }, "Set sebagai alamat utama")
                    ])
                  ])
                ]),
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Nama penerima",
                    required: "",
                    error: __props.errors.recipient_name,
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: __props.form.recipient_name,
                        "onUpdate:modelValue": ($event) => __props.form.recipient_name = $event,
                        placeholder: "Nama lengkap",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "No. HP penerima",
                    required: "",
                    error: __props.errors.recipient_phone,
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: __props.form.recipient_phone,
                        "onUpdate:modelValue": ($event) => __props.form.recipient_phone = $event,
                        placeholder: "08xxxxxxxxxx",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode(_component_UFormField, {
                  label: "Alamat utama",
                  required: "",
                  error: __props.errors.address_line1,
                  class: "w-full"
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UTextarea, {
                      modelValue: __props.form.address_line1,
                      "onUpdate:modelValue": ($event) => __props.form.address_line1 = $event,
                      placeholder: "Jalan, RT/RW, nomor, patokan...",
                      rows: 3,
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }, 8, ["error"]),
                createVNode(_component_UFormField, {
                  label: "Alamat tambahan (opsional)",
                  class: "w-full"
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.address_line2,
                      "onUpdate:modelValue": ($event) => __props.form.address_line2 = $event,
                      placeholder: "Contoh: Blok A No. 12",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Provinsi",
                    required: "",
                    error: __props.errors.province_id || __props.errors.province_label,
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode("div", { class: "space-y-2" }, [
                        createVNode(_component_USelectMenu, {
                          modelValue: __props.form.province_id,
                          "onUpdate:modelValue": ($event) => __props.form.province_id = $event,
                          items: __props.provinceItems,
                          "value-key": "value",
                          "label-key": "label",
                          placeholder: "Pilih provinsi",
                          disabled: __props.provinceItems.length === 0,
                          class: "w-full"
                        }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "disabled"]),
                        __props.provinceItems.length === 0 ? (openBlock(), createBlock("p", {
                          key: 0,
                          class: "text-xs text-amber-600 dark:text-amber-300"
                        }, " Data target pengiriman untuk provinsi belum tersedia. ")) : createCommentVNode("", true)
                      ])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Kota/Kab",
                    required: "",
                    error: __props.errors.city_id || __props.errors.city_label,
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode("div", { class: "space-y-2" }, [
                        createVNode(_component_USelectMenu, {
                          modelValue: __props.form.city_id,
                          "onUpdate:modelValue": ($event) => __props.form.city_id = $event,
                          items: __props.cityItems,
                          "value-key": "value",
                          "label-key": "label",
                          placeholder: "Pilih kota/kab",
                          disabled: !__props.form.province_id || __props.cityItems.length === 0,
                          class: "w-full"
                        }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "disabled"]),
                        __props.form.province_id && __props.cityItems.length === 0 ? (openBlock(), createBlock("p", {
                          key: 0,
                          class: "text-xs text-amber-600 dark:text-amber-300"
                        }, " Kota/Kab untuk provinsi ini belum tersedia di target pengiriman. ")) : createCommentVNode("", true)
                      ])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Kecamatan",
                    required: "",
                    error: __props.errors.district,
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode("div", { class: "space-y-2" }, [
                        createVNode(_component_USelectMenu, {
                          modelValue: __props.form.district,
                          "onUpdate:modelValue": ($event) => __props.form.district = $event,
                          items: __props.districtItems,
                          "value-key": "value",
                          "label-key": "label",
                          placeholder: "Pilih kecamatan",
                          disabled: !__props.form.city_id || __props.districtItems.length === 0,
                          class: "w-full"
                        }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "disabled"]),
                        __props.form.city_id && __props.districtItems.length === 0 ? (openBlock(), createBlock("p", {
                          key: 0,
                          class: "text-xs text-amber-600 dark:text-amber-300"
                        }, " Kecamatan untuk kota ini belum tersedia di target pengiriman. ")) : createCommentVNode("", true)
                      ])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "District Lion (auto)",
                    error: __props.errors.district_lion,
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: __props.form.district_lion,
                        "onUpdate:modelValue": ($event) => __props.form.district_lion = $event,
                        placeholder: "Terisi otomatis dari target pengiriman",
                        readonly: "",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Kode pos (opsional)",
                    error: __props.errors.postal_code,
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: __props.form.postal_code,
                        "onUpdate:modelValue": ($event) => __props.form.postal_code = $event,
                        placeholder: "12345",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Negara",
                    class: "w-full"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: __props.form.country,
                        "onUpdate:modelValue": ($event) => __props.form.country = $event,
                        placeholder: "Indonesia",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  })
                ]),
                createVNode(_component_UFormField, {
                  label: "Catatan (opsional)",
                  class: "w-full"
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: __props.form.description,
                      "onUpdate:modelValue": ($event) => __props.form.description = $event,
                      placeholder: "Contoh: Titip satpam / rumah pagar hitam",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                Object.keys(__props.errors).length ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200"
                }, [
                  createVNode("p", { class: "font-semibold" }, "Periksa kembali:"),
                  createVNode("ul", { class: "mt-1 list-disc pl-5 space-y-1" }, [
                    (openBlock(true), createBlock(Fragment, null, renderList(__props.errors, (message, key) => {
                      return openBlock(), createBlock("li", { key }, toDisplayString(message), 1);
                    }), 128))
                  ])
                ])) : createCommentVNode("", true),
                createVNode("div", { class: "flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    disabled: __props.submitting,
                    onClick: ($event) => isOpen.value = false
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Batal ")
                    ]),
                    _: 1
                  }, 8, ["disabled", "onClick"]),
                  createVNode("div", { class: "flex gap-2" }, [
                    createVNode(_component_UButton, {
                      color: "neutral",
                      variant: "ghost",
                      class: "rounded-xl",
                      disabled: __props.submitting,
                      onClick: ($event) => _ctx.$emit("reset")
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Reset ")
                      ]),
                      _: 1
                    }, 8, ["disabled", "onClick"]),
                    createVNode(_component_UButton, {
                      color: "primary",
                      variant: "solid",
                      class: "rounded-xl",
                      loading: __props.submitting,
                      onClick: ($event) => _ctx.$emit("submit")
                    }, {
                      default: withCtx(() => [
                        createTextVNode(toDisplayString(__props.mode === "create" ? "Simpan" : "Update"), 1)
                      ]),
                      _: 1
                    }, 8, ["loading", "onClick"])
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
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/addresses/AddressFormModal.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "AddressDeleteConfirmModal",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    address: {},
    deleting: { type: Boolean }
  }, {
    "open": { type: Boolean, ...{ required: true } },
    "openModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["confirm"], ["update:open"]),
  setup(__props) {
    const isOpen = useModel(__props, "open");
    function fullAddress(address) {
      const secondLine = address.address_line2 ? `, ${address.address_line2}` : "";
      const postalCode = address.postal_code ? `, ${address.postal_code}` : "";
      return `${address.address_line1}${secondLine}, ${address.city_label}, ${address.province_label}${postalCode}`;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$8;
      const _component_UIcon = _sfc_main$5;
      const _component_UButton = _sfc_main$6;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: isOpen.value,
        "onUpdate:open": ($event) => isOpen.value = $event,
        title: "Hapus alamat",
        description: "Aksi ini tidak bisa dibatalkan."
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="w-full max-w-lg space-y-3"${_scopeId}><div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.address?.label || "Alamat")}</p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300"${_scopeId}>${ssrInterpolate(__props.address?.recipient_name)} • ${ssrInterpolate(__props.address?.recipient_phone)}</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-200"${_scopeId}>${ssrInterpolate(__props.address ? fullAddress(__props.address) : "")}</p></div><div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200"${_scopeId}><div class="flex items-start gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-alert-triangle",
              class: "mt-0.5 size-4"
            }, null, _parent2, _scopeId));
            _push2(`<p${_scopeId}>Pastikan alamat ini tidak diperlukan untuk pengiriman berikutnya.</p></div></div><div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              disabled: __props.deleting,
              onClick: ($event) => isOpen.value = false
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Batal `);
                } else {
                  return [
                    createTextVNode(" Batal ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "error",
              variant: "solid",
              class: "rounded-xl",
              loading: __props.deleting,
              onClick: ($event) => _ctx.$emit("confirm")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Hapus Permanen `);
                } else {
                  return [
                    createTextVNode(" Hapus Permanen ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "w-full max-w-lg space-y-3" }, [
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40" }, [
                  createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.address?.label || "Alamat"), 1),
                  createVNode("p", { class: "mt-1 text-sm text-gray-600 dark:text-gray-300" }, toDisplayString(__props.address?.recipient_name) + " • " + toDisplayString(__props.address?.recipient_phone), 1),
                  createVNode("p", { class: "mt-2 text-sm text-gray-700 dark:text-gray-200" }, toDisplayString(__props.address ? fullAddress(__props.address) : ""), 1)
                ]),
                createVNode("div", { class: "rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200" }, [
                  createVNode("div", { class: "flex items-start gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-alert-triangle",
                      class: "mt-0.5 size-4"
                    }),
                    createVNode("p", null, "Pastikan alamat ini tidak diperlukan untuk pengiriman berikutnya.")
                  ])
                ]),
                createVNode("div", { class: "flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    disabled: __props.deleting,
                    onClick: ($event) => isOpen.value = false
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Batal ")
                    ]),
                    _: 1
                  }, 8, ["disabled", "onClick"]),
                  createVNode(_component_UButton, {
                    color: "error",
                    variant: "solid",
                    class: "rounded-xl",
                    loading: __props.deleting,
                    onClick: ($event) => _ctx.$emit("confirm")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Hapus Permanen ")
                    ]),
                    _: 1
                  }, 8, ["loading", "onClick"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/addresses/AddressDeleteConfirmModal.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "AddressDefaultSwitchModal",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    selectedAddress: {},
    otherAddresses: {},
    settingDefault: {}
  }, {
    "open": { type: Boolean, ...{ required: true } },
    "openModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["createAddress", "setDefaultContinue"], ["update:open"]),
  setup(__props) {
    const isOpen = useModel(__props, "open");
    function fullAddress(address) {
      const secondLine = address.address_line2 ? `, ${address.address_line2}` : "";
      const postalCode = address.postal_code ? `, ${address.postal_code}` : "";
      return `${address.address_line1}${secondLine}, ${address.city_label}, ${address.province_label}${postalCode}`;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$8;
      const _component_UIcon = _sfc_main$5;
      const _component_UButton = _sfc_main$6;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: isOpen.value,
        "onUpdate:open": ($event) => isOpen.value = $event,
        title: "Tidak bisa hapus alamat default",
        description: "Ubah default terlebih dulu."
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="w-full max-w-2xl space-y-4"${_scopeId}><div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200"${_scopeId}><div class="flex items-start gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-info",
              class: "mt-0.5 size-4"
            }, null, _parent2, _scopeId));
            _push2(`<div class="min-w-0"${_scopeId}><p class="font-semibold"${_scopeId}>Instruksi</p><ol class="mt-1 list-decimal pl-5 space-y-1"${_scopeId}><li${_scopeId}>Pilih alamat lain untuk dijadikan <b${_scopeId}>Default</b>.</li><li${_scopeId}>Setelah default berubah, lanjutkan proses hapus.</li></ol></div></div></div><div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Alamat yang ingin dihapus:</p><p class="mt-1 text-sm text-gray-700 dark:text-gray-200"${_scopeId}>${ssrInterpolate(__props.selectedAddress?.label || "Alamat")} — ${ssrInterpolate(__props.selectedAddress ? fullAddress(__props.selectedAddress) : "")}</p></div><div class="space-y-2"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Pilih default baru:</p>`);
            if (__props.otherAddresses.length === 0) {
              _push2(`<div class="rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"${_scopeId}> Kamu belum punya alamat lain. Tambahkan alamat baru dulu, lalu coba hapus lagi. <div class="mt-3"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UButton, {
                color: "primary",
                variant: "solid",
                class: "rounded-xl",
                icon: "i-lucide-plus",
                onClick: ($event) => _ctx.$emit("createAddress")
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
            } else {
              _push2(`<div class="grid grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}><!--[-->`);
              ssrRenderList(__props.otherAddresses, (address) => {
                _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><p class="truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(address.label || "Alamat")}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(address.recipient_name)} • ${ssrInterpolate(address.recipient_phone)}</p><p class="mt-2 line-clamp-2 text-sm text-gray-700 dark:text-gray-200"${_scopeId}>${ssrInterpolate(fullAddress(address))}</p><div class="mt-3"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UButton, {
                  color: "primary",
                  variant: "soft",
                  size: "sm",
                  class: "rounded-xl",
                  loading: !!__props.settingDefault[String(address.id)],
                  onClick: ($event) => _ctx.$emit("setDefaultContinue", address)
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Jadikan Default &amp; Lanjut Hapus `);
                    } else {
                      return [
                        createTextVNode(" Jadikan Default & Lanjut Hapus ")
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(`</div></div>`);
              });
              _push2(`<!--]--></div>`);
            }
            _push2(`</div><div class="flex justify-end pt-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              onClick: ($event) => isOpen.value = false
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tutup `);
                } else {
                  return [
                    createTextVNode(" Tutup ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "w-full max-w-2xl space-y-4" }, [
                createVNode("div", { class: "rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200" }, [
                  createVNode("div", { class: "flex items-start gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-info",
                      class: "mt-0.5 size-4"
                    }),
                    createVNode("div", { class: "min-w-0" }, [
                      createVNode("p", { class: "font-semibold" }, "Instruksi"),
                      createVNode("ol", { class: "mt-1 list-decimal pl-5 space-y-1" }, [
                        createVNode("li", null, [
                          createTextVNode("Pilih alamat lain untuk dijadikan "),
                          createVNode("b", null, "Default"),
                          createTextVNode(".")
                        ]),
                        createVNode("li", null, "Setelah default berubah, lanjutkan proses hapus.")
                      ])
                    ])
                  ])
                ]),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40" }, [
                  createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Alamat yang ingin dihapus:"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-700 dark:text-gray-200" }, toDisplayString(__props.selectedAddress?.label || "Alamat") + " — " + toDisplayString(__props.selectedAddress ? fullAddress(__props.selectedAddress) : ""), 1)
                ]),
                createVNode("div", { class: "space-y-2" }, [
                  createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Pilih default baru:"),
                  __props.otherAddresses.length === 0 ? (openBlock(), createBlock("div", {
                    key: 0,
                    class: "rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"
                  }, [
                    createTextVNode(" Kamu belum punya alamat lain. Tambahkan alamat baru dulu, lalu coba hapus lagi. "),
                    createVNode("div", { class: "mt-3" }, [
                      createVNode(_component_UButton, {
                        color: "primary",
                        variant: "solid",
                        class: "rounded-xl",
                        icon: "i-lucide-plus",
                        onClick: ($event) => _ctx.$emit("createAddress")
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" Tambah alamat ")
                        ]),
                        _: 1
                      }, 8, ["onClick"])
                    ])
                  ])) : (openBlock(), createBlock("div", {
                    key: 1,
                    class: "grid grid-cols-1 gap-3 sm:grid-cols-2"
                  }, [
                    (openBlock(true), createBlock(Fragment, null, renderList(__props.otherAddresses, (address) => {
                      return openBlock(), createBlock("div", {
                        key: address.id,
                        class: "rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
                      }, [
                        createVNode("p", { class: "truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(address.label || "Alamat"), 1),
                        createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(address.recipient_name) + " • " + toDisplayString(address.recipient_phone), 1),
                        createVNode("p", { class: "mt-2 line-clamp-2 text-sm text-gray-700 dark:text-gray-200" }, toDisplayString(fullAddress(address)), 1),
                        createVNode("div", { class: "mt-3" }, [
                          createVNode(_component_UButton, {
                            color: "primary",
                            variant: "soft",
                            size: "sm",
                            class: "rounded-xl",
                            loading: !!__props.settingDefault[String(address.id)],
                            onClick: ($event) => _ctx.$emit("setDefaultContinue", address)
                          }, {
                            default: withCtx(() => [
                              createTextVNode(" Jadikan Default & Lanjut Hapus ")
                            ]),
                            _: 1
                          }, 8, ["loading", "onClick"])
                        ])
                      ]);
                    }), 128))
                  ]))
                ]),
                createVNode("div", { class: "flex justify-end pt-2" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    onClick: ($event) => isOpen.value = false
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tutup ")
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
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/addresses/AddressDefaultSwitchModal.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
function toErrorMessage(value) {
  if (Array.isArray(value)) {
    return value.map((item) => String(item)).join(" ");
  }
  return value ? String(value) : "";
}
function useDashboardAddresses(options) {
  const formOpen = ref(false);
  const deleteOpen = ref(false);
  const blockedOpen = ref(false);
  const formMode = ref("create");
  const submitting = ref(false);
  const deleting = ref(false);
  const settingDefault = ref({});
  const selectedForEdit = ref(null);
  const selectedForDelete = ref(null);
  const errors = ref({});
  const isHydratingForm = ref(false);
  const form = reactive({
    label: "",
    is_default: false,
    recipient_name: "",
    recipient_phone: "",
    address_line1: "",
    address_line2: "",
    province_label: "",
    province_id: 0,
    city_label: "",
    city_id: 0,
    district: "",
    district_lion: "",
    postal_code: "",
    country: "Indonesia",
    description: ""
  });
  const provinceItems = computed(
    () => options.provinces.value.map((province) => ({
      label: province.label,
      value: province.id
    }))
  );
  const cityItems = computed(() => {
    const selectedProvinceId = Number(form.province_id || 0);
    if (!selectedProvinceId) {
      return [];
    }
    return options.cities.value.filter((city) => Number(city.province_id) === selectedProvinceId).map((city) => ({
      label: city.label,
      value: city.id
    }));
  });
  const districtItems = computed(() => {
    const selectedProvinceId = Number(form.province_id || 0);
    const selectedCityId = Number(form.city_id || 0);
    if (!selectedProvinceId || !selectedCityId) {
      return [];
    }
    return options.districts.value.filter(
      (district) => Number(district.province_id) === selectedProvinceId && Number(district.city_id) === selectedCityId
    ).map((district) => ({
      label: district.label,
      value: district.label
    }));
  });
  const otherAddressesForDefault = computed(() => {
    const currentAddress = selectedForDelete.value;
    if (!currentAddress) {
      return [];
    }
    return options.addresses.value.filter((address) => address.id !== currentAddress.id);
  });
  watch(
    () => form.province_id,
    (provinceIdRaw) => {
      if (isHydratingForm.value) {
        return;
      }
      const provinceId = Number(provinceIdRaw || 0);
      if (!provinceId) {
        form.province_label = "";
        form.city_id = 0;
        form.city_label = "";
        form.district = "";
        form.district_lion = "";
        return;
      }
      const province = provinceItems.value.find((item) => item.value === provinceId);
      if (province) {
        form.province_label = province.label;
      }
      form.city_id = 0;
      form.city_label = "";
      form.district = "";
      form.district_lion = "";
    }
  );
  watch(
    () => form.city_id,
    (cityIdRaw) => {
      if (isHydratingForm.value) {
        return;
      }
      const cityId = Number(cityIdRaw || 0);
      if (!cityId) {
        form.city_label = "";
        form.district = "";
        form.district_lion = "";
        return;
      }
      const city = cityItems.value.find((item) => item.value === cityId);
      if (city) {
        form.city_label = city.label;
      }
      form.district = "";
      form.district_lion = "";
    }
  );
  watch(
    () => form.district,
    (districtLabel) => {
      if (isHydratingForm.value) {
        return;
      }
      const normalizedDistrict = districtLabel.trim();
      if (!normalizedDistrict || !form.city_id || !form.province_id) {
        form.district_lion = "";
        return;
      }
      const district = options.districts.value.find(
        (item) => Number(item.province_id) === Number(form.province_id) && Number(item.city_id) === Number(form.city_id) && item.label === normalizedDistrict
      );
      form.district_lion = district?.district_lion ?? "";
    }
  );
  function clearErrors() {
    errors.value = {};
  }
  function resetForm() {
    form.label = "";
    form.is_default = false;
    form.recipient_name = "";
    form.recipient_phone = "";
    form.address_line1 = "";
    form.address_line2 = "";
    form.province_label = "";
    form.province_id = 0;
    form.city_label = "";
    form.city_id = 0;
    form.district = "";
    form.district_lion = "";
    form.postal_code = "";
    form.country = "Indonesia";
    form.description = "";
    clearErrors();
  }
  function fillForm(address) {
    isHydratingForm.value = true;
    form.label = address.label ?? "";
    form.is_default = !!address.is_default;
    form.recipient_name = address.recipient_name ?? "";
    form.recipient_phone = address.recipient_phone ?? "";
    form.address_line1 = address.address_line1 ?? "";
    form.address_line2 = address.address_line2 ?? "";
    form.province_label = address.province_label ?? "";
    form.province_id = Number(address.province_id ?? 0);
    form.city_label = address.city_label ?? "";
    form.city_id = Number(address.city_id ?? 0);
    form.district = address.district ?? "";
    form.district_lion = address.district_lion ?? "";
    form.postal_code = address.postal_code ?? "";
    form.country = address.country ?? "Indonesia";
    form.description = address.description ?? "";
    clearErrors();
    nextTick(() => {
      isHydratingForm.value = false;
    });
  }
  function validate() {
    const validationErrors = {};
    if (!form.recipient_name.trim()) {
      validationErrors.recipient_name = "Nama penerima wajib diisi.";
    }
    if (!form.recipient_phone.trim()) {
      validationErrors.recipient_phone = "No. HP wajib diisi.";
    } else if (form.recipient_phone.replace(/\D/g, "").length < 8) {
      validationErrors.recipient_phone = "No. HP terlalu pendek.";
    }
    if (!form.address_line1.trim()) {
      validationErrors.address_line1 = "Alamat utama wajib diisi.";
    }
    if (!form.province_id) {
      validationErrors.province_id = "Provinsi wajib dipilih/diisi.";
    }
    if (!form.city_id) {
      validationErrors.city_id = "Kota/Kab wajib dipilih/diisi.";
    }
    if (!form.district.trim()) {
      validationErrors.district = "Kecamatan wajib dipilih.";
    }
    if (form.postal_code && form.postal_code.trim().length > 0 && form.postal_code.trim().length < 5) {
      validationErrors.postal_code = "Kode pos minimal 5 karakter.";
    }
    errors.value = validationErrors;
    return Object.keys(validationErrors).length === 0;
  }
  function normalizeErrors(serverErrors) {
    errors.value = Object.fromEntries(
      Object.entries(serverErrors ?? {}).map(([field, message]) => [field, toErrorMessage(message)]).filter((entry) => entry[1].length > 0)
    );
  }
  function reloadAddressData() {
    router.reload({
      only: ["addresses", "defaultAddress", "provinces", "cities", "districts"]
    });
  }
  function openCreate() {
    formMode.value = "create";
    selectedForEdit.value = null;
    resetForm();
    formOpen.value = true;
  }
  function openEdit(address) {
    formMode.value = "edit";
    selectedForEdit.value = address;
    fillForm(address);
    formOpen.value = true;
  }
  function submitForm() {
    clearErrors();
    if (!validate()) {
      return;
    }
    submitting.value = true;
    const payload = {
      label: form.label || null,
      is_default: !!form.is_default,
      recipient_name: form.recipient_name,
      recipient_phone: form.recipient_phone,
      address_line1: form.address_line1,
      address_line2: form.address_line2 || null,
      province_label: form.province_label,
      province_id: Number(form.province_id),
      city_label: form.city_label,
      city_id: Number(form.city_id),
      district: form.district || null,
      district_lion: form.district_lion || null,
      postal_code: form.postal_code || null,
      country: form.country || "Indonesia",
      description: form.description || null
    };
    const onSuccess = () => {
      formOpen.value = false;
      resetForm();
      reloadAddressData();
    };
    const onError = (serverErrors) => {
      normalizeErrors(serverErrors);
    };
    const onFinish = () => {
      submitting.value = false;
    };
    if (formMode.value === "create") {
      router.post("/account/addresses", payload, {
        preserveScroll: true,
        onSuccess,
        onError,
        onFinish
      });
      return;
    }
    const addressId = selectedForEdit.value?.id;
    if (!addressId) {
      submitting.value = false;
      return;
    }
    router.put(`/account/addresses/${addressId}`, payload, {
      preserveScroll: true,
      onSuccess,
      onError,
      onFinish
    });
  }
  function setAsDefault(address) {
    const addressKey = String(address.id);
    settingDefault.value[addressKey] = true;
    router.post(`/account/addresses/${address.id}/default`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        reloadAddressData();
      },
      onFinish: () => {
        settingDefault.value[addressKey] = false;
      }
    });
  }
  function requestDelete(address) {
    selectedForDelete.value = address;
    if (address.is_default) {
      blockedOpen.value = true;
      return;
    }
    deleteOpen.value = true;
  }
  function confirmDelete() {
    const address = selectedForDelete.value;
    if (!address) {
      return;
    }
    deleting.value = true;
    router.delete(`/account/addresses/${address.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        deleteOpen.value = false;
        selectedForDelete.value = null;
        reloadAddressData();
      },
      onFinish: () => {
        deleting.value = false;
      }
    });
  }
  function setDefaultThenContinueDelete(newDefaultAddress) {
    if (!selectedForDelete.value) {
      return;
    }
    const addressKey = String(newDefaultAddress.id);
    settingDefault.value[addressKey] = true;
    router.post(`/account/addresses/${newDefaultAddress.id}/default`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        blockedOpen.value = false;
        router.reload({
          only: ["addresses", "defaultAddress", "provinces", "cities", "districts"],
          onSuccess: () => {
            nextTick(() => {
              deleteOpen.value = true;
            });
          }
        });
      },
      onFinish: () => {
        settingDefault.value[addressKey] = false;
      }
    });
  }
  function closeBlockedAndOpenCreate() {
    blockedOpen.value = false;
    nextTick(() => {
      openCreate();
    });
  }
  return {
    formOpen,
    deleteOpen,
    blockedOpen,
    formMode,
    submitting,
    deleting,
    settingDefault,
    selectedForDelete,
    otherAddressesForDefault,
    form,
    errors,
    provinceItems,
    cityItems,
    districtItems,
    resetForm,
    openCreate,
    openEdit,
    submitForm,
    setAsDefault,
    requestDelete,
    confirmDelete,
    setDefaultThenContinueDelete,
    closeBlockedAndOpenCreate
  };
}
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Addresses",
  __ssrInlineRender: true,
  props: {
    addresses: {},
    provinces: {},
    cities: {},
    districts: {}
  },
  setup(__props) {
    const props = __props;
    const addresses = computed(() => props.addresses ?? []);
    const provinces = computed(() => props.provinces ?? []);
    const cities = computed(() => props.cities ?? []);
    const districts = computed(() => props.districts ?? []);
    const {
      formOpen,
      deleteOpen,
      blockedOpen,
      formMode,
      submitting,
      deleting,
      settingDefault,
      selectedForDelete,
      otherAddressesForDefault,
      form,
      errors,
      provinceItems,
      cityItems,
      districtItems,
      resetForm,
      openCreate,
      openEdit,
      submitForm,
      setAsDefault,
      requestDelete,
      confirmDelete,
      setDefaultThenContinueDelete,
      closeBlockedAndOpenCreate
    } = useDashboardAddresses({
      addresses,
      provinces,
      cities,
      districts
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$e;
      const _component_UIcon = _sfc_main$5;
      const _component_UButton = _sfc_main$6;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><div class="flex items-center gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-map-pin",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`<div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Alamat</p><p class="text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Kelola alamat pengiriman untuk checkout lebih cepat. </p></div></div><div class="flex items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "rounded-xl",
              icon: "i-lucide-plus",
              onClick: unref(openCreate)
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tambah Alamat `);
                } else {
                  return [
                    createTextVNode(" Tambah Alamat ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              to: "/account/addresses",
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lihat Semua `);
                } else {
                  return [
                    createTextVNode(" Lihat Semua ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("div", { class: "flex items-center gap-3" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-map-pin",
                    class: "size-5 text-gray-500 dark:text-gray-300"
                  }),
                  createVNode("div", null, [
                    createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Alamat"),
                    createVNode("p", { class: "text-sm text-gray-500 dark:text-gray-400" }, " Kelola alamat pengiriman untuk checkout lebih cepat. ")
                  ])
                ]),
                createVNode("div", { class: "flex items-center gap-2" }, [
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "soft",
                    size: "sm",
                    class: "rounded-xl",
                    icon: "i-lucide-plus",
                    onClick: unref(openCreate)
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tambah Alamat ")
                    ]),
                    _: 1
                  }, 8, ["onClick"]),
                  createVNode(_component_UButton, {
                    to: "/account/addresses",
                    color: "neutral",
                    variant: "outline",
                    size: "sm",
                    class: "rounded-xl"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Lihat Semua ")
                    ]),
                    _: 1
                  })
                ])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$4, {
              addresses: addresses.value,
              "setting-default": unref(settingDefault),
              onCreate: unref(openCreate),
              onEdit: unref(openEdit),
              onDelete: unref(requestDelete),
              onSetDefault: unref(setAsDefault)
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_sfc_main$3, {
              open: unref(formOpen),
              "onUpdate:open": ($event) => isRef(formOpen) ? formOpen.value = $event : null,
              mode: unref(formMode),
              form: unref(form),
              errors: unref(errors),
              submitting: unref(submitting),
              "province-items": unref(provinceItems),
              "city-items": unref(cityItems),
              "district-items": unref(districtItems),
              onSubmit: unref(submitForm),
              onReset: unref(resetForm)
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_sfc_main$2, {
              open: unref(deleteOpen),
              "onUpdate:open": ($event) => isRef(deleteOpen) ? deleteOpen.value = $event : null,
              address: unref(selectedForDelete),
              deleting: unref(deleting),
              onConfirm: unref(confirmDelete)
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_sfc_main$1, {
              open: unref(blockedOpen),
              "onUpdate:open": ($event) => isRef(blockedOpen) ? blockedOpen.value = $event : null,
              "selected-address": unref(selectedForDelete),
              "other-addresses": unref(otherAddressesForDefault),
              "setting-default": unref(settingDefault),
              onCreateAddress: unref(closeBlockedAndOpenCreate),
              onSetDefaultContinue: unref(setDefaultThenContinueDelete)
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$4, {
                addresses: addresses.value,
                "setting-default": unref(settingDefault),
                onCreate: unref(openCreate),
                onEdit: unref(openEdit),
                onDelete: unref(requestDelete),
                onSetDefault: unref(setAsDefault)
              }, null, 8, ["addresses", "setting-default", "onCreate", "onEdit", "onDelete", "onSetDefault"]),
              createVNode(_sfc_main$3, {
                open: unref(formOpen),
                "onUpdate:open": ($event) => isRef(formOpen) ? formOpen.value = $event : null,
                mode: unref(formMode),
                form: unref(form),
                errors: unref(errors),
                submitting: unref(submitting),
                "province-items": unref(provinceItems),
                "city-items": unref(cityItems),
                "district-items": unref(districtItems),
                onSubmit: unref(submitForm),
                onReset: unref(resetForm)
              }, null, 8, ["open", "onUpdate:open", "mode", "form", "errors", "submitting", "province-items", "city-items", "district-items", "onSubmit", "onReset"]),
              createVNode(_sfc_main$2, {
                open: unref(deleteOpen),
                "onUpdate:open": ($event) => isRef(deleteOpen) ? deleteOpen.value = $event : null,
                address: unref(selectedForDelete),
                deleting: unref(deleting),
                onConfirm: unref(confirmDelete)
              }, null, 8, ["open", "onUpdate:open", "address", "deleting", "onConfirm"]),
              createVNode(_sfc_main$1, {
                open: unref(blockedOpen),
                "onUpdate:open": ($event) => isRef(blockedOpen) ? blockedOpen.value = $event : null,
                "selected-address": unref(selectedForDelete),
                "other-addresses": unref(otherAddressesForDefault),
                "setting-default": unref(settingDefault),
                onCreateAddress: unref(closeBlockedAndOpenCreate),
                onSetDefaultContinue: unref(setDefaultThenContinueDelete)
              }, null, 8, ["open", "onUpdate:open", "selected-address", "other-addresses", "setting-default", "onCreateAddress", "onSetDefaultContinue"])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Addresses.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
