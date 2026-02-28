import { _ as _sfc_main$b } from "./Separator-5rFlZiju.js";
import { _ as _sfc_main$5 } from "./Card-Bctow_EP.js";
import { _ as _sfc_main$8 } from "./Button-C2UOeJ2u.js";
import { computed, defineComponent, mergeProps, withCtx, createTextVNode, toDisplayString, createVNode, openBlock, createBlock, createCommentVNode, Fragment, renderList, useSSRContext, resolveDynamicComponent, unref } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderVNode, ssrRenderAttr } from "vue/server-renderer";
import { Head } from "@inertiajs/vue3";
import { a as _sfc_main$9 } from "./AppLayout-DrAs5LL6.js";
import { _ as _sfc_main$a } from "./SeoHead-qa3Msjgd.js";
import { _ as _sfc_main$6 } from "./Badge-CZ-Hzv6j.js";
import { _ as _sfc_main$4 } from "./Breadcrumb-DeoGXg5d.js";
import { _ as _sfc_main$7 } from "./Icon-4Khzngjd.js";
import "reka-ui";
import "@vueuse/core";
import "../ssr.js";
import "@inertiajs/vue3/server";
import "@unhead/vue/client";
import "tailwindcss/colors";
import "hookable";
import "defu";
import "ohash/utils";
import "@unhead/vue";
import "ufo";
import "./usePortal-EQErrF6h.js";
import "./Input-ChYVLMxJ.js";
import "@nuxt/ui/runtime/composables/useToast.js";
import "reka-ui/namespaced";
import "@nuxt/ui/runtime/vue/stubs/inertia.js";
import "./Checkbox-B2eEIhTD.js";
import "vaul-vue";
import "tailwind-variants";
import "@iconify/vue";
function useArticleDetail(article, seo) {
  const publishedDate = computed(() => {
    if (!article.published_at) {
      return null;
    }
    return new Intl.DateTimeFormat("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric"
    }).format(new Date(article.published_at));
  });
  const updatedDate = computed(() => {
    if (!article.updated_at) {
      return null;
    }
    return new Intl.DateTimeFormat("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit"
    }).format(new Date(article.updated_at));
  });
  const readTimeLabel = computed(() => `${article.read_time_minutes} menit baca`);
  const jsonLdScripts = computed(() => {
    const list = seo.structured_data ?? [];
    return list.map((item) => JSON.stringify(item));
  });
  const breadcrumbItems = computed(() => [
    { label: "Home", icon: "i-lucide-home", to: "/" },
    { label: "Artikel", to: "/articles" },
    { label: article.title }
  ]);
  function headingTag(level) {
    if (level <= 1) {
      return "h1";
    }
    if (level >= 6) {
      return "h6";
    }
    return `h${level}`;
  }
  return {
    publishedDate,
    updatedDate,
    readTimeLabel,
    jsonLdScripts,
    breadcrumbItems,
    headingTag
  };
}
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "ArticleDetailHeader",
  __ssrInlineRender: true,
  props: {
    article: {},
    breadcrumbItems: {},
    publishedDate: {},
    updatedDate: {},
    readTimeLabel: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UBreadcrumb = _sfc_main$4;
      const _component_UCard = _sfc_main$5;
      const _component_UBadge = _sfc_main$6;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-4" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UBreadcrumb, { items: __props.breadcrumbItems }, null, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "overflow-hidden rounded-2xl bg-white/90 dark:bg-gray-950/70" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-5"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Artikel `);
                } else {
                  return [
                    createTextVNode(" Artikel ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "neutral",
              variant: "subtle",
              size: "sm",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(__props.readTimeLabel)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(__props.readTimeLabel), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (__props.publishedDate) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "neutral",
                variant: "subtle",
                size: "sm",
                class: "rounded-full"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`${ssrInterpolate(__props.publishedDate)}`);
                  } else {
                    return [
                      createTextVNode(toDisplayString(__props.publishedDate), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><div class="space-y-2"${_scopeId}><h1 class="text-2xl font-bold leading-tight text-highlighted sm:text-4xl"${_scopeId}>${ssrInterpolate(__props.article.title)}</h1><p class="max-w-3xl text-sm leading-relaxed text-muted sm:text-base"${_scopeId}>${ssrInterpolate(__props.article.excerpt)}</p></div>`);
            if (__props.article.tags.length > 0) {
              _push2(`<div class="flex flex-wrap gap-2"${_scopeId}><!--[-->`);
              ssrRenderList(__props.article.tags, (tag) => {
                _push2(ssrRenderComponent(_component_UBadge, {
                  key: `${__props.article.id}-${tag}`,
                  color: "primary",
                  variant: "subtle",
                  class: "rounded-full"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(tag)}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(tag), 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
              });
              _push2(`<!--]--></div>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.updatedDate) {
              _push2(`<p class="text-xs text-muted"${_scopeId}> Terakhir diperbarui ${ssrInterpolate(__props.updatedDate)}</p>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-5" }, [
                createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                  createVNode(_component_UBadge, {
                    color: "primary",
                    variant: "soft",
                    size: "sm",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Artikel ")
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UBadge, {
                    color: "neutral",
                    variant: "subtle",
                    size: "sm",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(__props.readTimeLabel), 1)
                    ]),
                    _: 1
                  }),
                  __props.publishedDate ? (openBlock(), createBlock(_component_UBadge, {
                    key: 0,
                    color: "neutral",
                    variant: "subtle",
                    size: "sm",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(__props.publishedDate), 1)
                    ]),
                    _: 1
                  })) : createCommentVNode("", true)
                ]),
                createVNode("div", { class: "space-y-2" }, [
                  createVNode("h1", { class: "text-2xl font-bold leading-tight text-highlighted sm:text-4xl" }, toDisplayString(__props.article.title), 1),
                  createVNode("p", { class: "max-w-3xl text-sm leading-relaxed text-muted sm:text-base" }, toDisplayString(__props.article.excerpt), 1)
                ]),
                __props.article.tags.length > 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "flex flex-wrap gap-2"
                }, [
                  (openBlock(true), createBlock(Fragment, null, renderList(__props.article.tags, (tag) => {
                    return openBlock(), createBlock(_component_UBadge, {
                      key: `${__props.article.id}-${tag}`,
                      color: "primary",
                      variant: "subtle",
                      class: "rounded-full"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(toDisplayString(tag), 1)
                      ]),
                      _: 2
                    }, 1024);
                  }), 128))
                ])) : createCommentVNode("", true),
                __props.updatedDate ? (openBlock(), createBlock("p", {
                  key: 1,
                  class: "text-xs text-muted"
                }, " Terakhir diperbarui " + toDisplayString(__props.updatedDate), 1)) : createCommentVNode("", true)
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
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleDetailHeader.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "ArticleContentRenderer",
  __ssrInlineRender: true,
  props: {
    blocks: {}
  },
  setup(__props) {
    const props = __props;
    function headingTag(level) {
      if (level <= 1) {
        return "h1";
      }
      if (level >= 6) {
        return "h6";
      }
      return `h${level}`;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$5;
      const _component_UIcon = _sfc_main$7;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<article class="prose prose-gray max-w-none dark:prose-invert"${_scopeId}><!--[-->`);
            ssrRenderList(props.blocks, (block, index) => {
              _push2(`<!--[-->`);
              if (block.type === "heading") {
                ssrRenderVNode(_push2, createVNode(resolveDynamicComponent(headingTag(block.level)), { class: "font-bold tracking-tight" }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(block.text)}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(block.text), 1)
                      ];
                    }
                  }),
                  _: 2
                }), _parent2, _scopeId);
              } else if (block.type === "rich_text") {
                _push2(`<div class="leading-relaxed"${_scopeId}>${block.html ?? ""}</div>`);
              } else if (block.type === "image") {
                _push2(`<figure class="my-8"${_scopeId}><div class="overflow-hidden rounded-2xl bg-elevated"${_scopeId}>`);
                if (block.url) {
                  _push2(`<img${ssrRenderAttr("src", block.url)}${ssrRenderAttr("alt", block.alt || "Gambar artikel")} class="w-full object-cover" loading="lazy"${_scopeId}>`);
                } else {
                  _push2(`<div class="grid h-56 w-full place-items-center text-muted"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-image-off",
                    class: "size-7"
                  }, null, _parent2, _scopeId));
                  _push2(`</div>`);
                }
                _push2(`</div>`);
                if (block.caption) {
                  _push2(`<figcaption class="mt-2 text-sm text-muted"${_scopeId}>${ssrInterpolate(block.caption)}</figcaption>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</figure>`);
              } else if (block.type === "list") {
                ssrRenderVNode(_push2, createVNode(resolveDynamicComponent(block.ordered ? "ol" : "ul"), { class: "space-y-2" }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`<!--[-->`);
                      ssrRenderList(block.items, (item, itemIndex) => {
                        _push3(`<li${_scopeId2}>${item ?? ""}</li>`);
                      });
                      _push3(`<!--]-->`);
                    } else {
                      return [
                        (openBlock(true), createBlock(Fragment, null, renderList(block.items, (item, itemIndex) => {
                          return openBlock(), createBlock("li", {
                            key: `item-${index}-${itemIndex}`,
                            innerHTML: item
                          }, null, 8, ["innerHTML"]);
                        }), 128))
                      ];
                    }
                  }),
                  _: 2
                }), _parent2, _scopeId);
              } else if (block.type === "quote") {
                _push2(`<blockquote class="border-primary pl-4"${_scopeId}><p${_scopeId}>${ssrInterpolate(block.quote)}</p>`);
                if (block.cite) {
                  _push2(`<cite class="not-italic text-muted"${_scopeId}>— ${ssrInterpolate(block.cite)}</cite>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</blockquote>`);
              } else if (block.type === "divider") {
                _push2(`<hr class="my-8 border-default"${_scopeId}>`);
              } else {
                _push2(`<details class="rounded-xl border border-default p-3 text-sm"${_scopeId}><summary class="cursor-pointer font-medium text-highlighted"${_scopeId}>Blok tidak dikenal: ${ssrInterpolate(block.block_type)}</summary><pre class="mt-2 overflow-x-auto text-xs text-muted"${_scopeId}>${ssrInterpolate(JSON.stringify(block.data, null, 2))}</pre></details>`);
              }
              _push2(`<!--]-->`);
            });
            _push2(`<!--]-->`);
            if (props.blocks.length === 0) {
              _push2(`<div class="rounded-2xl border border-dashed border-default p-8 text-center text-muted"${_scopeId}> Konten artikel belum tersedia. </div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</article>`);
          } else {
            return [
              createVNode("article", { class: "prose prose-gray max-w-none dark:prose-invert" }, [
                (openBlock(true), createBlock(Fragment, null, renderList(props.blocks, (block, index) => {
                  return openBlock(), createBlock(Fragment, {
                    key: `block-${index}`
                  }, [
                    block.type === "heading" ? (openBlock(), createBlock(resolveDynamicComponent(headingTag(block.level)), {
                      key: 0,
                      class: "font-bold tracking-tight"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(toDisplayString(block.text), 1)
                      ]),
                      _: 2
                    }, 1024)) : block.type === "rich_text" ? (openBlock(), createBlock("div", {
                      key: 1,
                      class: "leading-relaxed",
                      innerHTML: block.html
                    }, null, 8, ["innerHTML"])) : block.type === "image" ? (openBlock(), createBlock("figure", {
                      key: 2,
                      class: "my-8"
                    }, [
                      createVNode("div", { class: "overflow-hidden rounded-2xl bg-elevated" }, [
                        block.url ? (openBlock(), createBlock("img", {
                          key: 0,
                          src: block.url,
                          alt: block.alt || "Gambar artikel",
                          class: "w-full object-cover",
                          loading: "lazy"
                        }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                          key: 1,
                          class: "grid h-56 w-full place-items-center text-muted"
                        }, [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-image-off",
                            class: "size-7"
                          })
                        ]))
                      ]),
                      block.caption ? (openBlock(), createBlock("figcaption", {
                        key: 0,
                        class: "mt-2 text-sm text-muted"
                      }, toDisplayString(block.caption), 1)) : createCommentVNode("", true)
                    ])) : block.type === "list" ? (openBlock(), createBlock(resolveDynamicComponent(block.ordered ? "ol" : "ul"), {
                      key: 3,
                      class: "space-y-2"
                    }, {
                      default: withCtx(() => [
                        (openBlock(true), createBlock(Fragment, null, renderList(block.items, (item, itemIndex) => {
                          return openBlock(), createBlock("li", {
                            key: `item-${index}-${itemIndex}`,
                            innerHTML: item
                          }, null, 8, ["innerHTML"]);
                        }), 128))
                      ]),
                      _: 2
                    }, 1024)) : block.type === "quote" ? (openBlock(), createBlock("blockquote", {
                      key: 4,
                      class: "border-primary pl-4"
                    }, [
                      createVNode("p", null, toDisplayString(block.quote), 1),
                      block.cite ? (openBlock(), createBlock("cite", {
                        key: 0,
                        class: "not-italic text-muted"
                      }, "— " + toDisplayString(block.cite), 1)) : createCommentVNode("", true)
                    ])) : block.type === "divider" ? (openBlock(), createBlock("hr", {
                      key: 5,
                      class: "my-8 border-default"
                    })) : (openBlock(), createBlock("details", {
                      key: 6,
                      class: "rounded-xl border border-default p-3 text-sm"
                    }, [
                      createVNode("summary", { class: "cursor-pointer font-medium text-highlighted" }, "Blok tidak dikenal: " + toDisplayString(block.block_type), 1),
                      createVNode("pre", { class: "mt-2 overflow-x-auto text-xs text-muted" }, toDisplayString(JSON.stringify(block.data, null, 2)), 1)
                    ]))
                  ], 64);
                }), 128)),
                props.blocks.length === 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-dashed border-default p-8 text-center text-muted"
                }, " Konten artikel belum tersedia. ")) : createCommentVNode("", true)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleContentRenderer.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "ArticleRelatedPosts",
  __ssrInlineRender: true,
  props: {
    relatedArticles: {}
  },
  setup(__props) {
    const props = __props;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$5;
      const _component_UButton = _sfc_main$8;
      const _component_UIcon = _sfc_main$7;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl dark:bg-gray-950/80" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}><div class="flex items-center justify-between gap-2"${_scopeId}><h2 class="text-lg font-semibold text-highlighted sm:text-xl"${_scopeId}>Artikel Terkait</h2>`);
            _push2(ssrRenderComponent(_component_UButton, {
              to: "/articles",
              color: "neutral",
              variant: "outline",
              size: "sm",
              "trailing-icon": "i-lucide-arrow-right"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Semua Artikel `);
                } else {
                  return [
                    createTextVNode(" Semua Artikel ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            if (props.relatedArticles.length === 0) {
              _push2(`<div class="rounded-2xl border border-dashed border-default p-6 text-sm text-muted"${_scopeId}> Belum ada artikel terkait. </div>`);
            } else {
              _push2(`<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"${_scopeId}><!--[-->`);
              ssrRenderList(props.relatedArticles, (article) => {
                _push2(ssrRenderComponent(_component_UCard, {
                  key: article.id,
                  class: "overflow-hidden rounded-2xl",
                  ui: { body: "p-0" }
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`<div class="h-36 overflow-hidden bg-elevated"${_scopeId2}>`);
                      if (article.cover_image) {
                        _push3(`<img${ssrRenderAttr("src", article.cover_image)}${ssrRenderAttr("alt", article.title)} class="h-full w-full object-cover" loading="lazy"${_scopeId2}>`);
                      } else {
                        _push3(`<div class="grid h-full w-full place-items-center text-muted"${_scopeId2}>`);
                        _push3(ssrRenderComponent(_component_UIcon, {
                          name: "i-lucide-image-off",
                          class: "size-6"
                        }, null, _parent3, _scopeId2));
                        _push3(`</div>`);
                      }
                      _push3(`</div><div class="space-y-3 p-3"${_scopeId2}><h3 class="line-clamp-2 text-sm font-semibold leading-snug text-highlighted"${_scopeId2}>${ssrInterpolate(article.title)}</h3><p class="line-clamp-2 text-xs text-muted"${_scopeId2}>${ssrInterpolate(article.excerpt)}</p>`);
                      _push3(ssrRenderComponent(_component_UButton, {
                        to: article.url,
                        size: "xs",
                        color: "primary",
                        variant: "outline",
                        "trailing-icon": "i-lucide-arrow-right"
                      }, {
                        default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                          if (_push4) {
                            _push4(` Baca `);
                          } else {
                            return [
                              createTextVNode(" Baca ")
                            ];
                          }
                        }),
                        _: 2
                      }, _parent3, _scopeId2));
                      _push3(`</div>`);
                    } else {
                      return [
                        createVNode("div", { class: "h-36 overflow-hidden bg-elevated" }, [
                          article.cover_image ? (openBlock(), createBlock("img", {
                            key: 0,
                            src: article.cover_image,
                            alt: article.title,
                            class: "h-full w-full object-cover",
                            loading: "lazy"
                          }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                            key: 1,
                            class: "grid h-full w-full place-items-center text-muted"
                          }, [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-image-off",
                              class: "size-6"
                            })
                          ]))
                        ]),
                        createVNode("div", { class: "space-y-3 p-3" }, [
                          createVNode("h3", { class: "line-clamp-2 text-sm font-semibold leading-snug text-highlighted" }, toDisplayString(article.title), 1),
                          createVNode("p", { class: "line-clamp-2 text-xs text-muted" }, toDisplayString(article.excerpt), 1),
                          createVNode(_component_UButton, {
                            to: article.url,
                            size: "xs",
                            color: "primary",
                            variant: "outline",
                            "trailing-icon": "i-lucide-arrow-right"
                          }, {
                            default: withCtx(() => [
                              createTextVNode(" Baca ")
                            ]),
                            _: 1
                          }, 8, ["to"])
                        ])
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
              });
              _push2(`<!--]--></div>`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode("div", { class: "flex items-center justify-between gap-2" }, [
                  createVNode("h2", { class: "text-lg font-semibold text-highlighted sm:text-xl" }, "Artikel Terkait"),
                  createVNode(_component_UButton, {
                    to: "/articles",
                    color: "neutral",
                    variant: "outline",
                    size: "sm",
                    "trailing-icon": "i-lucide-arrow-right"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Semua Artikel ")
                    ]),
                    _: 1
                  })
                ]),
                props.relatedArticles.length === 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-dashed border-default p-6 text-sm text-muted"
                }, " Belum ada artikel terkait. ")) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                }, [
                  (openBlock(true), createBlock(Fragment, null, renderList(props.relatedArticles, (article) => {
                    return openBlock(), createBlock(_component_UCard, {
                      key: article.id,
                      class: "overflow-hidden rounded-2xl",
                      ui: { body: "p-0" }
                    }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "h-36 overflow-hidden bg-elevated" }, [
                          article.cover_image ? (openBlock(), createBlock("img", {
                            key: 0,
                            src: article.cover_image,
                            alt: article.title,
                            class: "h-full w-full object-cover",
                            loading: "lazy"
                          }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                            key: 1,
                            class: "grid h-full w-full place-items-center text-muted"
                          }, [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-image-off",
                              class: "size-6"
                            })
                          ]))
                        ]),
                        createVNode("div", { class: "space-y-3 p-3" }, [
                          createVNode("h3", { class: "line-clamp-2 text-sm font-semibold leading-snug text-highlighted" }, toDisplayString(article.title), 1),
                          createVNode("p", { class: "line-clamp-2 text-xs text-muted" }, toDisplayString(article.excerpt), 1),
                          createVNode(_component_UButton, {
                            to: article.url,
                            size: "xs",
                            color: "primary",
                            variant: "outline",
                            "trailing-icon": "i-lucide-arrow-right"
                          }, {
                            default: withCtx(() => [
                              createTextVNode(" Baca ")
                            ]),
                            _: 1
                          }, 8, ["to"])
                        ])
                      ]),
                      _: 2
                    }, 1024);
                  }), 128))
                ]))
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleRelatedPosts.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$9 },
  __name: "Show",
  __ssrInlineRender: true,
  props: {
    seo: {},
    article: {},
    relatedArticles: {}
  },
  setup(__props) {
    const props = __props;
    const { publishedDate, updatedDate, readTimeLabel, jsonLdScripts, breadcrumbItems } = useArticleDetail(
      props.article,
      props.seo
    );
    const hasCoverImage = computed(() => !!props.article.cover_image);
    const coverSrc = computed(() => props.article.cover_image || "");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$8;
      const _component_UCard = _sfc_main$5;
      const _component_USeparator = _sfc_main$b;
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$a, {
        title: props.seo.title,
        description: props.seo.description,
        canonical: props.seo.canonical,
        robots: props.seo.robots,
        image: props.seo.image ?? void 0
      }, null, _parent));
      _push(ssrRenderComponent(unref(Head), null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<!--[-->`);
            ssrRenderList(unref(jsonLdScripts), (script, index) => {
              ssrRenderVNode(_push2, createVNode(resolveDynamicComponent("script"), {
                key: `article-show-ld-${index}`,
                type: "application/ld+json"
              }, null), _parent2, _scopeId);
            });
            _push2(`<!--]-->`);
          } else {
            return [
              (openBlock(true), createBlock(Fragment, null, renderList(unref(jsonLdScripts), (script, index) => {
                return openBlock(), createBlock(resolveDynamicComponent("script"), {
                  key: `article-show-ld-${index}`,
                  type: "application/ld+json",
                  innerHTML: script
                }, null, 8, ["innerHTML"]);
              }), 128))
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="min-h-screen bg-gray-50 dark:bg-gray-950"><div class="relative isolate">`);
      if (hasCoverImage.value) {
        _push(`<div class="absolute inset-0 -z-10"><img${ssrRenderAttr("src", coverSrc.value)}${ssrRenderAttr("alt", props.article.title)} class="h-full w-full object-cover"><div class="absolute inset-0 bg-linear-to-b from-black/55 via-black/35 to-gray-50 dark:to-gray-950"></div></div>`);
      } else {
        _push(`<div class="absolute inset-0 -z-10"><div class="h-full w-full bg-linear-to-b from-gray-900/10 via-gray-50 to-gray-50 dark:from-white/5 dark:via-gray-950 dark:to-gray-950"></div></div>`);
      }
      _push(`<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="flex items-center justify-between py-6">`);
      _push(ssrRenderComponent(_component_UButton, {
        to: "/articles",
        color: "neutral",
        variant: "outline",
        icon: "i-lucide-arrow-left",
        size: "sm",
        class: "rounded-xl bg-white/80 backdrop-blur dark:bg-gray-950/60"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Kembali ke Artikel `);
          } else {
            return [
              createTextVNode(" Kembali ke Artikel ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="pb-10 pt-4 sm:pb-14 sm:pt-6"><div class="max-w-3xl"><div class="flex flex-wrap items-center gap-2"><span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur dark:border-white/10">${ssrInterpolate(unref(publishedDate))}</span><span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur dark:border-white/10">${ssrInterpolate(unref(readTimeLabel))}</span>`);
      if (unref(updatedDate)) {
        _push(`<span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur dark:border-white/10"> Updated: ${ssrInterpolate(unref(updatedDate))}</span>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">${ssrInterpolate(props.article.title)}</h1>`);
      if (props.seo?.description) {
        _push(`<p class="mt-4 max-w-2xl text-sm leading-6 text-white/85 sm:text-base">${ssrInterpolate(props.seo.description)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div><div class="pointer-events-none h-8 bg-linear-to-b from-transparent to-gray-50 dark:to-gray-950"></div></div><div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">`);
      _push(ssrRenderComponent(_component_UCard, {
        class: "-mt-10 rounded-3xl backdrop-blur dark:bg-gray-950/65",
        ui: { body: "p-5 sm:p-8" }
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$3, {
              article: props.article,
              "breadcrumb-items": unref(breadcrumbItems),
              "published-date": unref(publishedDate),
              "updated-date": unref(updatedDate),
              "read-time-label": unref(readTimeLabel)
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_USeparator, { class: "my-6 dark:border-white/10" }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_sfc_main$2, {
              blocks: props.article.blocks
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$3, {
                article: props.article,
                "breadcrumb-items": unref(breadcrumbItems),
                "published-date": unref(publishedDate),
                "updated-date": unref(updatedDate),
                "read-time-label": unref(readTimeLabel)
              }, null, 8, ["article", "breadcrumb-items", "published-date", "updated-date", "read-time-label"]),
              createVNode(_component_USeparator, { class: "my-6 dark:border-white/10" }),
              createVNode(_sfc_main$2, {
                blocks: props.article.blocks
              }, null, 8, ["blocks"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="mt-10">`);
      _push(ssrRenderComponent(_sfc_main$1, {
        "related-articles": props.relatedArticles
      }, null, _parent));
      _push(`</div></div></div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Article/Show.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
