import { _ as _sfc_main$b } from "./Card-Bctow_EP.js";
import { ref, computed, defineComponent, mergeProps, withCtx, createTextVNode, useSSRContext, createVNode, toDisplayString, openBlock, createBlock, Fragment, renderList, resolveComponent, createCommentVNode, unref } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderClass, ssrRenderStyle } from "vue/server-renderer";
import { useForm, router } from "@inertiajs/vue3";
import { useToast } from "@nuxt/ui/runtime/composables/useToast.js";
import { _ as _sfc_main$7 } from "./Input-ChYVLMxJ.js";
import { _ as _sfc_main$6 } from "./Button-C2UOeJ2u.js";
import { _ as _sfc_main$a } from "./Badge-CZ-Hzv6j.js";
import { _ as _sfc_main$9 } from "./Alert-nxPelC10.js";
import { _ as _sfc_main$8 } from "./Modal-BOfqalmp.js";
import { _ as _sfc_main$c } from "./Icon-4Khzngjd.js";
import { _ as _sfc_main$d } from "./Empty-CaPO1Ei8.js";
import "reka-ui";
import "../ssr.js";
import "@inertiajs/vue3/server";
import "@unhead/vue/client";
import "tailwindcss/colors";
import "hookable";
import "@vueuse/core";
import "defu";
import "ohash/utils";
import "@unhead/vue";
import "ufo";
import "./usePortal-EQErrF6h.js";
import "tailwind-variants";
import "@iconify/vue";
function useNetworkPlacement(passiveMembers, currentCustomerId) {
  const toast = useToast();
  const showPlacementDialog = ref(false);
  const selectedUplineId = ref(null);
  const selectedPosition = ref(null);
  const selectedMember = ref(null);
  const memberSearchQuery = ref("");
  const placementForm = useForm({
    member_id: 0,
    upline_id: 0,
    position: ""
  });
  const filteredPassiveMembers = computed(() => {
    const query = memberSearchQuery.value.trim().toLowerCase();
    if (query === "") {
      return passiveMembers.value;
    }
    return passiveMembers.value.filter((member) => {
      const haystack = `${member.name} ${member.email} ${member.phone ?? ""} ${member.username ?? ""}`.toLowerCase();
      return haystack.includes(query);
    });
  });
  function applyPlacementContext(payload) {
    selectedUplineId.value = payload.uplineId;
    selectedPosition.value = payload.position;
    selectedMember.value = null;
    memberSearchQuery.value = "";
    showPlacementDialog.value = true;
  }
  function openPlacementDialog(payload) {
    if (!currentCustomerId.value) {
      toast.add({
        title: "Upline tidak valid",
        description: "Data customer login tidak tersedia.",
        color: "error",
        icon: "i-lucide-x-circle"
      });
      return;
    }
    if (passiveMembers.value.length === 0) {
      router.reload({
        only: ["passiveMembers"],
        onSuccess: () => {
          if (passiveMembers.value.length === 0) {
            toast.add({
              title: "Member pasif belum tersedia",
              description: "Tidak ada member status pasif yang bisa ditempatkan saat ini.",
              color: "warning",
              icon: "i-lucide-alert-circle"
            });
            return;
          }
          applyPlacementContext(payload);
        }
      });
      return;
    }
    applyPlacementContext(payload);
  }
  function closePlacementDialog() {
    showPlacementDialog.value = false;
    selectedUplineId.value = null;
    selectedPosition.value = null;
    selectedMember.value = null;
    memberSearchQuery.value = "";
    placementForm.reset();
  }
  function selectMember(member) {
    selectedMember.value = member;
  }
  function placeMemberToBinaryTree() {
    if (!selectedMember.value || !selectedPosition.value || !selectedUplineId.value) {
      toast.add({
        title: "Data belum lengkap",
        description: "Pilih member dan posisi placement terlebih dahulu.",
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    placementForm.member_id = selectedMember.value.id;
    placementForm.upline_id = selectedUplineId.value;
    placementForm.position = selectedPosition.value;
    placementForm.post("/mlm/place-member", {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: "Placement berhasil",
          description: `${selectedMember.value?.name} ditempatkan ke posisi ${selectedPosition.value === "left" ? "kiri" : "kanan"}.`,
          color: "success",
          icon: "i-lucide-check-circle-2"
        });
        closePlacementDialog();
        router.reload({
          only: ["binaryTree", "networkTreeStats", "activeMembers", "passiveMembers", "prospectMembers", "hasLeft", "hasRight"]
        });
      },
      onError: (errors) => {
        const errorMessage = typeof errors.error === "string" ? errors.error : "Gagal memproses placement member.";
        toast.add({
          title: "Placement gagal",
          description: errorMessage,
          color: "error",
          icon: "i-lucide-x-circle"
        });
      }
    });
  }
  return {
    showPlacementDialog,
    selectedUplineId,
    selectedPosition,
    selectedMember,
    memberSearchQuery,
    placementForm,
    filteredPassiveMembers,
    openPlacementDialog,
    closePlacementDialog,
    selectMember,
    placeMemberToBinaryTree
  };
}
function countNodes(node) {
  if (!node) {
    return 0;
  }
  return 1 + countNodes(node.left) + countNodes(node.right);
}
function findNodeById(node, nodeId) {
  if (!node) {
    return null;
  }
  if (node.id === nodeId) {
    return node;
  }
  const leftNode = findNodeById(node.left, nodeId);
  if (leftNode) {
    return leftNode;
  }
  return findNodeById(node.right, nodeId);
}
function flattenTree(node) {
  if (!node) {
    return [];
  }
  return [
    {
      id: node.id,
      name: node.name,
      username: node.username,
      email: node.email ?? "-",
      package_name: node.package_name ?? "Member",
      level: node.level
    },
    ...flattenTree(node.left),
    ...flattenTree(node.right)
  ];
}
function useNetworkTree(binaryTree, networkTreeStats) {
  const activeRootId = ref(null);
  const treeSearchQuery = ref("");
  const showTreeSearchResults = ref(false);
  const zoom = ref(1);
  const minZoom = 0.65;
  const maxZoom = 1.6;
  const collapsedIds = ref([]);
  const rootTree = computed(() => binaryTree.value ?? null);
  const currentTree = computed(() => {
    if (!rootTree.value) {
      return null;
    }
    if (!activeRootId.value) {
      return rootTree.value;
    }
    return findNodeById(rootTree.value, activeRootId.value) ?? rootTree.value;
  });
  const isViewingMemberTree = computed(() => activeRootId.value !== null);
  const selectedMemberForTree = computed(() => currentTree.value);
  const allMemberSearchData = computed(() => flattenTree(rootTree.value));
  const maxLoadedLevel = computed(() => {
    const levels = allMemberSearchData.value.map((item) => item.level);
    if (levels.length === 0) {
      return 0;
    }
    return Math.max(...levels);
  });
  const currentStats = computed(() => {
    if (!currentTree.value) {
      return {
        totalDownlines: 0,
        totalLeft: 0,
        totalRight: 0
      };
    }
    if (!isViewingMemberTree.value && networkTreeStats.value) {
      return {
        totalDownlines: networkTreeStats.value.total_downlines,
        totalLeft: networkTreeStats.value.left_count,
        totalRight: networkTreeStats.value.right_count
      };
    }
    return {
      totalDownlines: Math.max(0, countNodes(currentTree.value) - 1),
      totalLeft: countNodes(currentTree.value.left),
      totalRight: countNodes(currentTree.value.right)
    };
  });
  const treeSearchResults = computed(() => {
    const query = treeSearchQuery.value.trim().toLowerCase();
    if (query.length < 2) {
      return [];
    }
    return allMemberSearchData.value.filter((member) => {
      const haystack = `${member.name} ${member.username} ${member.email} ${member.package_name}`.toLowerCase();
      return haystack.includes(query);
    }).slice(0, 12);
  });
  function backToDefaultTree() {
    activeRootId.value = null;
  }
  function focusToMember(memberId) {
    activeRootId.value = memberId;
    collapsedIds.value = collapsedIds.value.filter((id) => id !== memberId);
  }
  function toggleNode(memberId) {
    if (collapsedIds.value.includes(memberId)) {
      collapsedIds.value = collapsedIds.value.filter((id) => id !== memberId);
      return;
    }
    collapsedIds.value = [...collapsedIds.value, memberId];
  }
  function expandAll() {
    collapsedIds.value = [];
  }
  function collapseAll() {
    if (!currentTree.value) {
      collapsedIds.value = [];
      return;
    }
    collapsedIds.value = flattenTree(currentTree.value).map((member) => member.id).filter((id) => id !== currentTree.value?.id);
  }
  function handleZoomIn() {
    zoom.value = Math.min(maxZoom, Number((zoom.value + 0.1).toFixed(2)));
  }
  function handleZoomOut() {
    zoom.value = Math.max(minZoom, Number((zoom.value - 0.1).toFixed(2)));
  }
  function handleResetZoom() {
    zoom.value = 1;
  }
  function handleTreeSearchInput() {
    showTreeSearchResults.value = treeSearchQuery.value.trim().length >= 2;
  }
  function selectTreeSearchResult(memberId) {
    treeSearchQuery.value = "";
    showTreeSearchResults.value = false;
    focusToMember(memberId);
  }
  function handleTreeSearchBlur() {
    window.setTimeout(() => {
      showTreeSearchResults.value = false;
    }, 120);
  }
  return {
    activeRootId,
    treeSearchQuery,
    showTreeSearchResults,
    zoom,
    collapsedIds,
    rootTree,
    currentTree,
    isViewingMemberTree,
    selectedMemberForTree,
    allMemberSearchData,
    maxLoadedLevel,
    currentStats,
    treeSearchResults,
    backToDefaultTree,
    focusToMember,
    toggleNode,
    expandAll,
    collapseAll,
    handleZoomIn,
    handleZoomOut,
    handleResetZoom,
    handleTreeSearchInput,
    selectTreeSearchResult,
    handleTreeSearchBlur
  };
}
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "NetworkHeaderControls",
  __ssrInlineRender: true,
  props: {
    isViewingMemberTree: { type: Boolean },
    selectedMemberForTree: { default: null },
    maxLoadedLevel: {},
    treeSearchQuery: { default: "" },
    showTreeSearchResults: { type: Boolean, default: false },
    treeSearchResults: { default: () => [] }
  },
  emits: ["back", "update:treeSearchQuery", "searchInput", "searchFocus", "searchBlur", "selectSearchResult", "expandAll", "collapseAll", "zoomOut", "zoomIn", "resetZoom"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function handleTreeSearchModelUpdate(value) {
      emit("update:treeSearchQuery", String(value ?? ""));
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$6;
      const _component_UInput = _sfc_main$7;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, _attrs))}><div class="flex items-center gap-2 sm:gap-3">`);
      if (__props.isViewingMemberTree) {
        _push(ssrRenderComponent(_component_UButton, {
          color: "neutral",
          variant: "outline",
          size: "xs",
          icon: "i-lucide-arrow-left",
          class: "rounded-xl",
          onClick: ($event) => emit("back")
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Kembali `);
            } else {
              return [
                createTextVNode(" Kembali ")
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`<div><p class="text-sm font-semibold text-highlighted sm:text-base">${ssrInterpolate(__props.isViewingMemberTree && __props.selectedMemberForTree ? `Jaringan ${__props.selectedMemberForTree.name}` : "Struktur Binary Tree MLM")}</p><p class="text-xs text-muted">${ssrInterpolate(__props.isViewingMemberTree && __props.selectedMemberForTree ? `@${__props.selectedMemberForTree.username}` : `Data model customer • depth termuat hingga level ${__props.maxLoadedLevel}`)}</p></div></div><div class="flex flex-wrap items-center gap-2"><div class="relative">`);
      _push(ssrRenderComponent(_component_UInput, {
        "model-value": __props.treeSearchQuery,
        icon: "i-lucide-search",
        placeholder: "Cari member...",
        size: "sm",
        class: "w-56",
        "onUpdate:modelValue": handleTreeSearchModelUpdate,
        onInput: ($event) => emit("searchInput"),
        onFocus: ($event) => emit("searchFocus"),
        onBlur: ($event) => emit("searchBlur")
      }, null, _parent));
      if (__props.showTreeSearchResults) {
        _push(`<div class="absolute right-0 top-full z-20 mt-1 w-72 overflow-hidden rounded-xl border border-default bg-default shadow-lg">`);
        if (__props.treeSearchResults.length === 0) {
          _push(`<div class="p-3 text-sm text-muted"> Tidak ditemukan member. </div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`<!--[-->`);
        ssrRenderList(__props.treeSearchResults, (member) => {
          _push(`<button type="button" class="w-full border-b border-default px-3 py-2 text-left transition hover:bg-elevated/70 last:border-b-0"><p class="truncate text-sm font-medium text-highlighted">${ssrInterpolate(member.name)}</p><p class="truncate text-xs text-muted">@${ssrInterpolate(member.username)} • ${ssrInterpolate(member.package_name)}</p></button>`);
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "sm",
        icon: "i-lucide-unfold-vertical",
        class: "rounded-xl",
        onClick: ($event) => emit("expandAll")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Expand `);
          } else {
            return [
              createTextVNode(" Expand ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "sm",
        icon: "i-lucide-fold-vertical",
        class: "rounded-xl",
        onClick: ($event) => emit("collapseAll")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Collapse `);
          } else {
            return [
              createTextVNode(" Collapse ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "sm",
        icon: "i-lucide-zoom-out",
        class: "rounded-xl",
        onClick: ($event) => emit("zoomOut")
      }, null, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "sm",
        icon: "i-lucide-zoom-in",
        class: "rounded-xl",
        onClick: ($event) => emit("zoomIn")
      }, null, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "ghost",
        size: "sm",
        icon: "i-lucide-rotate-ccw",
        class: "rounded-xl",
        onClick: ($event) => emit("resetZoom")
      }, null, _parent));
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkHeaderControls.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "NetworkPlacementModal",
  __ssrInlineRender: true,
  props: {
    open: { type: Boolean, default: false },
    selectedPosition: { default: null },
    selectedUplineId: { default: null },
    memberSearchQuery: { default: "" },
    filteredPassiveMembers: { default: () => [] },
    selectedMemberId: { default: null },
    processing: { type: Boolean, default: false }
  },
  emits: ["update:open", "update:memberSearchQuery", "selectMember", "submit", "cancel"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const modalOpen = computed({
      get: () => props.open,
      set: (value) => {
        emit("update:open", value);
        if (!value) {
          emit("cancel");
        }
      }
    });
    function handleMemberSearchModelUpdate(value) {
      emit("update:memberSearchQuery", String(value ?? ""));
    }
    function formatDate(value) {
      if (!value) {
        return "-";
      }
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return value;
      }
      return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      }).format(date);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$8;
      const _component_UAlert = _sfc_main$9;
      const _component_UInput = _sfc_main$7;
      const _component_UBadge = _sfc_main$a;
      const _component_UButton = _sfc_main$6;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: modalOpen.value,
        "onUpdate:open": ($event) => modalOpen.value = $event,
        title: "Tempatkan Member ke Binary Tree",
        description: __props.selectedPosition ? `Posisi terpilih: ${__props.selectedPosition === "left" ? "Kiri" : "Kanan"}` : ""
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UAlert, {
              color: "neutral",
              variant: "subtle",
              icon: "i-lucide-info",
              class: "rounded-2xl",
              title: `Upline Node ID: ${__props.selectedUplineId ?? "-"}`,
              description: "Pilih satu member pasif untuk placement pada node ini."
            }, null, _parent2, _scopeId));
            _push2(`<div class="mt-4 space-y-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UInput, {
              "model-value": __props.memberSearchQuery,
              icon: "i-lucide-search",
              placeholder: "Cari nama, email, atau telepon...",
              size: "sm",
              class: "w-full",
              "onUpdate:modelValue": handleMemberSearchModelUpdate
            }, null, _parent2, _scopeId));
            if (__props.filteredPassiveMembers.length === 0) {
              _push2(`<div class="rounded-2xl border border-dashed border-default p-6 text-center text-sm text-muted"${_scopeId}> Tidak ada member pasif yang cocok. </div>`);
            } else {
              _push2(`<div class="max-h-80 space-y-2 overflow-auto pr-1"${_scopeId}><!--[-->`);
              ssrRenderList(__props.filteredPassiveMembers, (member) => {
                _push2(`<button type="button" class="${ssrRenderClass([__props.selectedMemberId === member.id ? "border-primary bg-primary/10" : "border-default hover:bg-elevated/60", "w-full rounded-2xl border px-3 py-2 text-left transition"])}"${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div class="min-w-0"${_scopeId}><p class="truncate text-sm font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(member.name)}</p><p class="truncate text-xs text-muted"${_scopeId}>${ssrInterpolate(member.email)}</p><p class="truncate text-xs text-muted"${_scopeId}>${ssrInterpolate(member.phone ?? "-")}</p><p class="text-[11px] text-muted"${_scopeId}>Join: ${ssrInterpolate(formatDate(member.joined_at))}</p></div>`);
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: member.has_purchase ? "success" : "neutral",
                  variant: member.has_purchase ? "soft" : "subtle",
                  size: "xs",
                  class: "rounded-full"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(member.has_purchase ? "Purchase" : "No Purchase")}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(member.has_purchase ? "Purchase" : "No Purchase"), 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(`</div></button>`);
              });
              _push2(`<!--]--></div>`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode(_component_UAlert, {
                color: "neutral",
                variant: "subtle",
                icon: "i-lucide-info",
                class: "rounded-2xl",
                title: `Upline Node ID: ${__props.selectedUplineId ?? "-"}`,
                description: "Pilih satu member pasif untuk placement pada node ini."
              }, null, 8, ["title"]),
              createVNode("div", { class: "mt-4 space-y-3" }, [
                createVNode(_component_UInput, {
                  "model-value": __props.memberSearchQuery,
                  icon: "i-lucide-search",
                  placeholder: "Cari nama, email, atau telepon...",
                  size: "sm",
                  class: "w-full",
                  "onUpdate:modelValue": handleMemberSearchModelUpdate
                }, null, 8, ["model-value"]),
                __props.filteredPassiveMembers.length === 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-dashed border-default p-6 text-center text-sm text-muted"
                }, " Tidak ada member pasif yang cocok. ")) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "max-h-80 space-y-2 overflow-auto pr-1"
                }, [
                  (openBlock(true), createBlock(Fragment, null, renderList(__props.filteredPassiveMembers, (member) => {
                    return openBlock(), createBlock("button", {
                      key: member.id,
                      type: "button",
                      class: ["w-full rounded-2xl border px-3 py-2 text-left transition", __props.selectedMemberId === member.id ? "border-primary bg-primary/10" : "border-default hover:bg-elevated/60"],
                      onClick: ($event) => emit("selectMember", member)
                    }, [
                      createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                        createVNode("div", { class: "min-w-0" }, [
                          createVNode("p", { class: "truncate text-sm font-semibold text-highlighted" }, toDisplayString(member.name), 1),
                          createVNode("p", { class: "truncate text-xs text-muted" }, toDisplayString(member.email), 1),
                          createVNode("p", { class: "truncate text-xs text-muted" }, toDisplayString(member.phone ?? "-"), 1),
                          createVNode("p", { class: "text-[11px] text-muted" }, "Join: " + toDisplayString(formatDate(member.joined_at)), 1)
                        ]),
                        createVNode(_component_UBadge, {
                          color: member.has_purchase ? "success" : "neutral",
                          variant: member.has_purchase ? "soft" : "subtle",
                          size: "xs",
                          class: "rounded-full"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(member.has_purchase ? "Purchase" : "No Purchase"), 1)
                          ]),
                          _: 2
                        }, 1032, ["color", "variant"])
                      ])
                    ], 10, ["onClick"]);
                  }), 128))
                ]))
              ])
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex w-full justify-end gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              disabled: __props.processing,
              onClick: ($event) => emit("cancel")
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
              color: "primary",
              class: "rounded-xl",
              loading: __props.processing,
              disabled: !__props.selectedMemberId || !__props.selectedPosition || __props.processing,
              onClick: ($event) => emit("submit")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tempatkan `);
                } else {
                  return [
                    createTextVNode(" Tempatkan ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex w-full justify-end gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-xl",
                  disabled: __props.processing,
                  onClick: ($event) => emit("cancel")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Batal ")
                  ]),
                  _: 1
                }, 8, ["disabled", "onClick"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  class: "rounded-xl",
                  loading: __props.processing,
                  disabled: !__props.selectedMemberId || !__props.selectedPosition || __props.processing,
                  onClick: ($event) => emit("submit")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Tempatkan ")
                  ]),
                  _: 1
                }, 8, ["loading", "disabled", "onClick"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkPlacementModal.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "NetworkStatsCards",
  __ssrInlineRender: true,
  props: {
    stats: {}
  },
  setup(__props) {
    const props = __props;
    const statistics = computed(() => [
      {
        label: "Total Jaringan",
        value: props.stats.totalDownlines,
        icon: "i-lucide-users",
        color: "primary",
        description: "Akumulasi mitra aktif"
      },
      {
        label: "Kaki Kiri",
        value: props.stats.totalLeft,
        icon: "i-lucide-arrow-down-left",
        color: "blue",
        description: "Pertumbuhan sisi kiri"
      },
      {
        label: "Kaki Kanan",
        value: props.stats.totalRight,
        icon: "i-lucide-arrow-down-right",
        color: "emerald",
        description: "Pertumbuhan sisi kanan"
      }
    ]);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      const _component_UIcon = _sfc_main$c;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 sm:grid-cols-3 gap-4" }, _attrs))}><!--[-->`);
      ssrRenderList(statistics.value, (stat) => {
        _push(ssrRenderComponent(_component_UCard, {
          key: stat.label,
          class: "relative overflow-hidden group",
          ui: {
            root: "rounded-2xl transition-all duration-300 hover:shadow-sm",
            body: "p-4 sm:p-5"
          }
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<div class="${ssrRenderClass([stat.color === "primary" ? "bg-primary-500" : stat.color === "blue" ? "bg-blue-500" : "bg-emerald-500", "absolute -right-4 -top-4 size-24 blur-3xl opacity-10 transition-opacity group-hover:opacity-20"])}"${_scopeId}></div><div class="flex items-center justify-between"${_scopeId}><div class="space-y-1"${_scopeId}><p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(stat.label)}</p><div class="flex items-baseline gap-1"${_scopeId}><h3 class="text-2xl sm:text-3xl font-black tracking-tighter text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(stat.value.toLocaleString("id-ID"))}</h3><span class="text-[10px] font-medium text-gray-400"${_scopeId}>Mitra</span></div><p class="text-[10px] text-gray-400 hidden sm:block"${_scopeId}>${ssrInterpolate(stat.description)}</p></div><div class="${ssrRenderClass([[
                stat.color === "primary" ? "bg-primary-50 dark:bg-primary-950/50 ring-primary-500/20 text-primary-600" : stat.color === "blue" ? "bg-blue-50 dark:bg-blue-950/50 ring-blue-500/20 text-blue-600" : "bg-emerald-50 dark:bg-emerald-950/50 ring-emerald-500/20 text-emerald-600"
              ], "p-2.5 rounded-xl ring-1 ring-inset shadow-xs transition-transform duration-500 group-hover:scale-110"])}"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: stat.icon,
                class: "size-5 sm:size-6"
              }, null, _parent2, _scopeId));
              _push2(`</div></div><div class="mt-4 h-1 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"${_scopeId}><div class="${ssrRenderClass([[
                stat.color === "primary" ? "bg-primary-500" : stat.color === "blue" ? "bg-blue-500" : "bg-emerald-500"
              ], "h-full transition-all duration-1000"])}" style="${ssrRenderStyle({ width: "65%" })}"${_scopeId}></div></div>`);
            } else {
              return [
                createVNode("div", {
                  class: ["absolute -right-4 -top-4 size-24 blur-3xl opacity-10 transition-opacity group-hover:opacity-20", stat.color === "primary" ? "bg-primary-500" : stat.color === "blue" ? "bg-blue-500" : "bg-emerald-500"]
                }, null, 2),
                createVNode("div", { class: "flex items-center justify-between" }, [
                  createVNode("div", { class: "space-y-1" }, [
                    createVNode("p", { class: "text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400" }, toDisplayString(stat.label), 1),
                    createVNode("div", { class: "flex items-baseline gap-1" }, [
                      createVNode("h3", { class: "text-2xl sm:text-3xl font-black tracking-tighter text-gray-900 dark:text-white" }, toDisplayString(stat.value.toLocaleString("id-ID")), 1),
                      createVNode("span", { class: "text-[10px] font-medium text-gray-400" }, "Mitra")
                    ]),
                    createVNode("p", { class: "text-[10px] text-gray-400 hidden sm:block" }, toDisplayString(stat.description), 1)
                  ]),
                  createVNode("div", {
                    class: ["p-2.5 rounded-xl ring-1 ring-inset shadow-xs transition-transform duration-500 group-hover:scale-110", [
                      stat.color === "primary" ? "bg-primary-50 dark:bg-primary-950/50 ring-primary-500/20 text-primary-600" : stat.color === "blue" ? "bg-blue-50 dark:bg-blue-950/50 ring-blue-500/20 text-blue-600" : "bg-emerald-50 dark:bg-emerald-950/50 ring-emerald-500/20 text-emerald-600"
                    ]]
                  }, [
                    createVNode(_component_UIcon, {
                      name: stat.icon,
                      class: "size-5 sm:size-6"
                    }, null, 8, ["name"])
                  ], 2)
                ]),
                createVNode("div", { class: "mt-4 h-1 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden" }, [
                  createVNode("div", {
                    class: ["h-full transition-all duration-1000", [
                      stat.color === "primary" ? "bg-primary-500" : stat.color === "blue" ? "bg-blue-500" : "bg-emerald-500"
                    ]],
                    style: { width: "65%" }
                  }, null, 2)
                ])
              ];
            }
          }),
          _: 2
        }, _parent));
      });
      _push(`<!--]--></div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkStatsCards.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  ...{
    name: "NetworkTreeNode"
  },
  __name: "NetworkTreeNode",
  __ssrInlineRender: true,
  props: {
    node: {},
    depth: { default: 1 },
    maxDepth: { default: 6 },
    collapsedIds: { default: () => [] },
    allowPlacement: { type: Boolean, default: true }
  },
  emits: ["memberClick", "openPlacement", "toggleExpand"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const canRenderChildren = computed(() => props.depth < props.maxDepth);
    const hasChildNode = computed(() => Boolean(props.node.left || props.node.right || props.node.has_children));
    const isCollapsed = computed(() => props.collapsedIds.includes(props.node.id));
    const showChildren = computed(() => canRenderChildren.value && hasChildNode.value && !isCollapsed.value);
    const packageLabel = computed(() => props.node.package_name ?? "Member");
    const levelLabel = computed(() => `L${props.node.level}`);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      const _component_UButton = _sfc_main$6;
      const _component_UBadge = _sfc_main$a;
      const _component_NetworkTreeNode = resolveComponent("NetworkTreeNode", true);
      const _component_UIcon = _sfc_main$c;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col items-center" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UCard, { class: "w-[260px] rounded-2xl border border-default shadow-sm" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-2"${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div class="min-w-0"${_scopeId}><p class="truncate text-sm font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(__props.node.name)}</p><p class="truncate text-[11px] text-muted"${_scopeId}>@${ssrInterpolate(__props.node.username)}</p></div><div class="flex items-center gap-1"${_scopeId}>`);
            if (hasChildNode.value) {
              _push2(ssrRenderComponent(_component_UButton, {
                size: "xs",
                color: "neutral",
                variant: "ghost",
                icon: isCollapsed.value ? "i-lucide-plus" : "i-lucide-minus",
                onClick: ($event) => emit("toggleExpand", __props.node.id)
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(ssrRenderComponent(_component_UButton, {
              size: "xs",
              color: "neutral",
              variant: "ghost",
              icon: "i-lucide-scan-search",
              onClick: ($event) => emit("memberClick", __props.node.id)
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="flex flex-wrap gap-1.5"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "neutral",
              variant: "subtle",
              size: "xs",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(packageLabel.value)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(packageLabel.value), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "primary",
              variant: "subtle",
              size: "xs",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(levelLabel.value)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(levelLabel.value), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "success",
              variant: "subtle",
              size: "xs",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Kiri ${ssrInterpolate(__props.node.total_left)}`);
                } else {
                  return [
                    createTextVNode(" Kiri " + toDisplayString(__props.node.total_left), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "info",
              variant: "subtle",
              size: "xs",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Kanan ${ssrInterpolate(__props.node.total_right)}`);
                } else {
                  return [
                    createTextVNode(" Kanan " + toDisplayString(__props.node.total_right), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-2" }, [
                createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                  createVNode("div", { class: "min-w-0" }, [
                    createVNode("p", { class: "truncate text-sm font-semibold text-highlighted" }, toDisplayString(__props.node.name), 1),
                    createVNode("p", { class: "truncate text-[11px] text-muted" }, "@" + toDisplayString(__props.node.username), 1)
                  ]),
                  createVNode("div", { class: "flex items-center gap-1" }, [
                    hasChildNode.value ? (openBlock(), createBlock(_component_UButton, {
                      key: 0,
                      size: "xs",
                      color: "neutral",
                      variant: "ghost",
                      icon: isCollapsed.value ? "i-lucide-plus" : "i-lucide-minus",
                      onClick: ($event) => emit("toggleExpand", __props.node.id)
                    }, null, 8, ["icon", "onClick"])) : createCommentVNode("", true),
                    createVNode(_component_UButton, {
                      size: "xs",
                      color: "neutral",
                      variant: "ghost",
                      icon: "i-lucide-scan-search",
                      onClick: ($event) => emit("memberClick", __props.node.id)
                    }, null, 8, ["onClick"])
                  ])
                ]),
                createVNode("div", { class: "flex flex-wrap gap-1.5" }, [
                  createVNode(_component_UBadge, {
                    color: "neutral",
                    variant: "subtle",
                    size: "xs",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(packageLabel.value), 1)
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UBadge, {
                    color: "primary",
                    variant: "subtle",
                    size: "xs",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(levelLabel.value), 1)
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UBadge, {
                    color: "success",
                    variant: "subtle",
                    size: "xs",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Kiri " + toDisplayString(__props.node.total_left), 1)
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UBadge, {
                    color: "info",
                    variant: "subtle",
                    size: "xs",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Kanan " + toDisplayString(__props.node.total_right), 1)
                    ]),
                    _: 1
                  })
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      if (showChildren.value) {
        _push(`<div class="mt-4 w-full"><div class="mx-auto h-4 w-px bg-gray-300 dark:bg-gray-700"></div><div class="relative grid grid-cols-2 gap-4 px-2 sm:gap-8"><div class="absolute left-1/4 right-1/4 top-0 h-px bg-gray-300 dark:bg-gray-700"></div><div class="relative flex justify-center pt-4"><div class="absolute left-1/2 top-0 h-4 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700"></div>`);
        if (__props.node.left) {
          _push(ssrRenderComponent(_component_NetworkTreeNode, {
            node: __props.node.left,
            depth: __props.depth + 1,
            "max-depth": __props.maxDepth,
            "collapsed-ids": __props.collapsedIds,
            "allow-placement": __props.allowPlacement,
            onMemberClick: ($event) => emit("memberClick", $event),
            onOpenPlacement: ($event) => emit("openPlacement", $event),
            onToggleExpand: ($event) => emit("toggleExpand", $event)
          }, null, _parent));
        } else if (__props.allowPlacement) {
          _push(ssrRenderComponent(_component_UCard, { class: "w-[220px] rounded-2xl border border-dashed border-default bg-elevated/40" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="flex flex-col items-center gap-2 py-2 text-center"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-user-round-plus",
                  class: "size-5 text-muted"
                }, null, _parent2, _scopeId));
                _push2(`<p class="text-xs text-muted"${_scopeId}>Slot kiri kosong</p>`);
                _push2(ssrRenderComponent(_component_UButton, {
                  size: "xs",
                  color: "primary",
                  variant: "soft",
                  icon: "i-lucide-plus",
                  class: "rounded-xl",
                  onClick: ($event) => emit("openPlacement", { uplineId: __props.node.id, position: "left" })
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Tempatkan `);
                    } else {
                      return [
                        createTextVNode(" Tempatkan ")
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
                _push2(`</div>`);
              } else {
                return [
                  createVNode("div", { class: "flex flex-col items-center gap-2 py-2 text-center" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-user-round-plus",
                      class: "size-5 text-muted"
                    }),
                    createVNode("p", { class: "text-xs text-muted" }, "Slot kiri kosong"),
                    createVNode(_component_UButton, {
                      size: "xs",
                      color: "primary",
                      variant: "soft",
                      icon: "i-lucide-plus",
                      class: "rounded-xl",
                      onClick: ($event) => emit("openPlacement", { uplineId: __props.node.id, position: "left" })
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Tempatkan ")
                      ]),
                      _: 1
                    }, 8, ["onClick"])
                  ])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`</div><div class="relative flex justify-center pt-4"><div class="absolute left-1/2 top-0 h-4 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700"></div>`);
        if (__props.node.right) {
          _push(ssrRenderComponent(_component_NetworkTreeNode, {
            node: __props.node.right,
            depth: __props.depth + 1,
            "max-depth": __props.maxDepth,
            "collapsed-ids": __props.collapsedIds,
            "allow-placement": __props.allowPlacement,
            onMemberClick: ($event) => emit("memberClick", $event),
            onOpenPlacement: ($event) => emit("openPlacement", $event),
            onToggleExpand: ($event) => emit("toggleExpand", $event)
          }, null, _parent));
        } else if (__props.allowPlacement) {
          _push(ssrRenderComponent(_component_UCard, { class: "w-[220px] rounded-2xl border border-dashed border-default bg-elevated/40" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="flex flex-col items-center gap-2 py-2 text-center"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-user-round-plus",
                  class: "size-5 text-muted"
                }, null, _parent2, _scopeId));
                _push2(`<p class="text-xs text-muted"${_scopeId}>Slot kanan kosong</p>`);
                _push2(ssrRenderComponent(_component_UButton, {
                  size: "xs",
                  color: "primary",
                  variant: "soft",
                  icon: "i-lucide-plus",
                  class: "rounded-xl",
                  onClick: ($event) => emit("openPlacement", { uplineId: __props.node.id, position: "right" })
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Tempatkan `);
                    } else {
                      return [
                        createTextVNode(" Tempatkan ")
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
                _push2(`</div>`);
              } else {
                return [
                  createVNode("div", { class: "flex flex-col items-center gap-2 py-2 text-center" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-user-round-plus",
                      class: "size-5 text-muted"
                    }),
                    createVNode("p", { class: "text-xs text-muted" }, "Slot kanan kosong"),
                    createVNode(_component_UButton, {
                      size: "xs",
                      color: "primary",
                      variant: "soft",
                      icon: "i-lucide-plus",
                      class: "rounded-xl",
                      onClick: ($event) => emit("openPlacement", { uplineId: __props.node.id, position: "right" })
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Tempatkan ")
                      ]),
                      _: 1
                    }, 8, ["onClick"])
                  ])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`</div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkTreeNode.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "NetworkTreePanel",
  __ssrInlineRender: true,
  props: {
    currentTree: { default: null },
    zoom: {},
    collapsedIds: { default: () => [] },
    maxDepth: { default: 6 },
    allowPlacement: { type: Boolean, default: true }
  },
  emits: ["memberClick", "openPlacement", "toggleExpand"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UEmpty = _sfc_main$d;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "rounded-2xl border border-default bg-elevated/20 p-2 sm:p-4" }, _attrs))}><div class="overflow-auto"><div class="min-w-max p-3 sm:p-5">`);
      if (__props.currentTree) {
        _push(`<div class="mx-auto w-fit transition-transform duration-200" style="${ssrRenderStyle({ transform: `scale(${__props.zoom})`, transformOrigin: "top center" })}">`);
        _push(ssrRenderComponent(_sfc_main$2, {
          node: __props.currentTree,
          "max-depth": __props.maxDepth,
          "collapsed-ids": __props.collapsedIds,
          "allow-placement": __props.allowPlacement,
          onMemberClick: ($event) => emit("memberClick", $event),
          onOpenPlacement: ($event) => emit("openPlacement", $event),
          onToggleExpand: ($event) => emit("toggleExpand", $event)
        }, null, _parent));
        _push(`</div>`);
      } else {
        _push(ssrRenderComponent(_component_UEmpty, {
          icon: "i-lucide-network",
          title: "Jaringan belum tersedia",
          description: "Belum ada data node tree untuk akun ini.",
          ui: { root: "rounded-2xl py-16" }
        }, null, _parent));
      }
      _push(`</div></div></div>`);
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkTreePanel.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Network",
  __ssrInlineRender: true,
  props: {
    binaryTree: { default: null },
    networkTreeStats: { default: null },
    passiveMembers: { default: () => [] },
    currentCustomerId: { default: null }
  },
  setup(__props) {
    const props = __props;
    const binaryTree = computed(() => props.binaryTree ?? null);
    const networkTreeStats = computed(() => props.networkTreeStats ?? null);
    const passiveMembers = computed(() => props.passiveMembers ?? []);
    const currentCustomerId = computed(() => props.currentCustomerId ?? null);
    const {
      currentStats,
      isViewingMemberTree,
      selectedMemberForTree,
      maxLoadedLevel,
      treeSearchQuery,
      showTreeSearchResults,
      treeSearchResults,
      currentTree,
      zoom,
      collapsedIds,
      backToDefaultTree,
      focusToMember,
      toggleNode,
      expandAll,
      collapseAll,
      handleZoomIn,
      handleZoomOut,
      handleResetZoom,
      handleTreeSearchInput,
      selectTreeSearchResult,
      handleTreeSearchBlur
    } = useNetworkTree(binaryTree, networkTreeStats);
    const {
      showPlacementDialog,
      selectedUplineId,
      selectedPosition,
      selectedMember,
      memberSearchQuery,
      placementForm,
      filteredPassiveMembers,
      openPlacementDialog,
      closePlacementDialog,
      selectMember,
      placeMemberToBinaryTree
    } = useNetworkPlacement(passiveMembers, currentCustomerId);
    function updateTreeSearchQuery(value) {
      treeSearchQuery.value = value;
    }
    function updatePlacementModalOpen(value) {
      showPlacementDialog.value = value;
    }
    function updatePlacementSearchQuery(value) {
      memberSearchQuery.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      _push(`<!--[--><div class="space-y-4 sm:space-y-6">`);
      _push(ssrRenderComponent(_sfc_main$3, { stats: unref(currentStats) }, null, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "overflow-hidden rounded-3xl" }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$5, {
              "is-viewing-member-tree": unref(isViewingMemberTree),
              "selected-member-for-tree": unref(selectedMemberForTree),
              "max-loaded-level": unref(maxLoadedLevel),
              "tree-search-query": unref(treeSearchQuery),
              "show-tree-search-results": unref(showTreeSearchResults),
              "tree-search-results": unref(treeSearchResults),
              onBack: unref(backToDefaultTree),
              "onUpdate:treeSearchQuery": updateTreeSearchQuery,
              onSearchInput: unref(handleTreeSearchInput),
              onSearchFocus: unref(handleTreeSearchInput),
              onSearchBlur: unref(handleTreeSearchBlur),
              onSelectSearchResult: unref(selectTreeSearchResult),
              onExpandAll: unref(expandAll),
              onCollapseAll: unref(collapseAll),
              onZoomOut: unref(handleZoomOut),
              onZoomIn: unref(handleZoomIn),
              onResetZoom: unref(handleResetZoom)
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$5, {
                "is-viewing-member-tree": unref(isViewingMemberTree),
                "selected-member-for-tree": unref(selectedMemberForTree),
                "max-loaded-level": unref(maxLoadedLevel),
                "tree-search-query": unref(treeSearchQuery),
                "show-tree-search-results": unref(showTreeSearchResults),
                "tree-search-results": unref(treeSearchResults),
                onBack: unref(backToDefaultTree),
                "onUpdate:treeSearchQuery": updateTreeSearchQuery,
                onSearchInput: unref(handleTreeSearchInput),
                onSearchFocus: unref(handleTreeSearchInput),
                onSearchBlur: unref(handleTreeSearchBlur),
                onSelectSearchResult: unref(selectTreeSearchResult),
                onExpandAll: unref(expandAll),
                onCollapseAll: unref(collapseAll),
                onZoomOut: unref(handleZoomOut),
                onZoomIn: unref(handleZoomIn),
                onResetZoom: unref(handleResetZoom)
              }, null, 8, ["is-viewing-member-tree", "selected-member-for-tree", "max-loaded-level", "tree-search-query", "show-tree-search-results", "tree-search-results", "onBack", "onSearchInput", "onSearchFocus", "onSearchBlur", "onSelectSearchResult", "onExpandAll", "onCollapseAll", "onZoomOut", "onZoomIn", "onResetZoom"])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4 p-4 sm:p-6"${_scopeId}>`);
            _push2(ssrRenderComponent(_sfc_main$1, {
              "current-tree": unref(currentTree),
              zoom: unref(zoom),
              "collapsed-ids": unref(collapsedIds),
              "max-depth": 6,
              onMemberClick: unref(focusToMember),
              onOpenPlacement: unref(openPlacementDialog),
              onToggleExpand: unref(toggleNode)
            }, null, _parent2, _scopeId));
            _push2(`<div class="rounded-2xl border border-default bg-elevated/20 p-3"${_scopeId}><div class="flex flex-col gap-2 text-xs text-muted sm:flex-row sm:items-center sm:justify-between"${_scopeId}><p${_scopeId}>Klik node member untuk fokus subtree dan gunakan tombol expand/collapse untuk organization tree.</p><p${_scopeId}>Placement hanya menampilkan member pasif yang belum ditempatkan.</p><p${_scopeId}>Zoom saat ini: ${ssrInterpolate(Math.round(unref(zoom) * 100))}%</p></div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4 p-4 sm:p-6" }, [
                createVNode(_sfc_main$1, {
                  "current-tree": unref(currentTree),
                  zoom: unref(zoom),
                  "collapsed-ids": unref(collapsedIds),
                  "max-depth": 6,
                  onMemberClick: unref(focusToMember),
                  onOpenPlacement: unref(openPlacementDialog),
                  onToggleExpand: unref(toggleNode)
                }, null, 8, ["current-tree", "zoom", "collapsed-ids", "onMemberClick", "onOpenPlacement", "onToggleExpand"]),
                createVNode("div", { class: "rounded-2xl border border-default bg-elevated/20 p-3" }, [
                  createVNode("div", { class: "flex flex-col gap-2 text-xs text-muted sm:flex-row sm:items-center sm:justify-between" }, [
                    createVNode("p", null, "Klik node member untuk fokus subtree dan gunakan tombol expand/collapse untuk organization tree."),
                    createVNode("p", null, "Placement hanya menampilkan member pasif yang belum ditempatkan."),
                    createVNode("p", null, "Zoom saat ini: " + toDisplayString(Math.round(unref(zoom) * 100)) + "%", 1)
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_sfc_main$4, {
        open: unref(showPlacementDialog),
        "selected-position": unref(selectedPosition),
        "selected-upline-id": unref(selectedUplineId),
        "member-search-query": unref(memberSearchQuery),
        "filtered-passive-members": unref(filteredPassiveMembers),
        "selected-member-id": unref(selectedMember)?.id ?? null,
        processing: unref(placementForm).processing,
        "onUpdate:open": updatePlacementModalOpen,
        "onUpdate:memberSearchQuery": updatePlacementSearchQuery,
        onSelectMember: unref(selectMember),
        onSubmit: unref(placeMemberToBinaryTree),
        onCancel: unref(closePlacementDialog)
      }, null, _parent));
      _push(`<!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Network.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
