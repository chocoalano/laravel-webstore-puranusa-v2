import { defineComponent, computed, unref, withCtx, createVNode, toDisplayString, openBlock, createBlock, createCommentVNode, useSSRContext } from "vue";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttr } from "vue/server-renderer";
import { usePage, Head } from "@inertiajs/vue3";
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "SeoHead",
  __ssrInlineRender: true,
  props: {
    title: {},
    description: {},
    canonical: {},
    robots: {},
    image: {}
  },
  setup(__props) {
    const props = __props;
    const page = usePage();
    const siteName = computed(() => page.props.appName ?? "Puranusa");
    const ogTitle = computed(() => `${props.title} | ${siteName.value}`);
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Head), _attrs, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<title${_scopeId}>${ssrInterpolate(__props.title)}</title>`);
            if (__props.description) {
              _push2(`<meta name="description"${ssrRenderAttr("content", __props.description)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta name="robots"${ssrRenderAttr("content", __props.robots ?? "index, follow")}${_scopeId}>`);
            if (__props.canonical) {
              _push2(`<link rel="canonical"${ssrRenderAttr("href", __props.canonical)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta property="og:type" content="website"${_scopeId}><meta property="og:site_name"${ssrRenderAttr("content", siteName.value)}${_scopeId}><meta property="og:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
            if (__props.description) {
              _push2(`<meta property="og:description"${ssrRenderAttr("content", __props.description)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.canonical) {
              _push2(`<meta property="og:url"${ssrRenderAttr("content", __props.canonical)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta property="og:locale" content="id_ID"${_scopeId}>`);
            if (__props.image) {
              _push2(`<meta property="og:image"${ssrRenderAttr("content", __props.image)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.image) {
              _push2(`<meta property="og:image:width" content="1200"${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.image) {
              _push2(`<meta property="og:image:height" content="630"${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta name="twitter:card"${ssrRenderAttr("content", __props.image ? "summary_large_image" : "summary")}${_scopeId}><meta name="twitter:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
            if (__props.description) {
              _push2(`<meta name="twitter:description"${ssrRenderAttr("content", __props.description)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.image) {
              _push2(`<meta name="twitter:image"${ssrRenderAttr("content", __props.image)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              createVNode("title", null, toDisplayString(__props.title), 1),
              __props.description ? (openBlock(), createBlock("meta", {
                key: 0,
                name: "description",
                content: __props.description
              }, null, 8, ["content"])) : createCommentVNode("", true),
              createVNode("meta", {
                name: "robots",
                content: __props.robots ?? "index, follow"
              }, null, 8, ["content"]),
              __props.canonical ? (openBlock(), createBlock("link", {
                key: 1,
                rel: "canonical",
                href: __props.canonical
              }, null, 8, ["href"])) : createCommentVNode("", true),
              createVNode("meta", {
                property: "og:type",
                content: "website"
              }),
              createVNode("meta", {
                property: "og:site_name",
                content: siteName.value
              }, null, 8, ["content"]),
              createVNode("meta", {
                property: "og:title",
                content: ogTitle.value
              }, null, 8, ["content"]),
              __props.description ? (openBlock(), createBlock("meta", {
                key: 2,
                property: "og:description",
                content: __props.description
              }, null, 8, ["content"])) : createCommentVNode("", true),
              __props.canonical ? (openBlock(), createBlock("meta", {
                key: 3,
                property: "og:url",
                content: __props.canonical
              }, null, 8, ["content"])) : createCommentVNode("", true),
              createVNode("meta", {
                property: "og:locale",
                content: "id_ID"
              }),
              __props.image ? (openBlock(), createBlock("meta", {
                key: 4,
                property: "og:image",
                content: __props.image
              }, null, 8, ["content"])) : createCommentVNode("", true),
              __props.image ? (openBlock(), createBlock("meta", {
                key: 5,
                property: "og:image:width",
                content: "1200"
              })) : createCommentVNode("", true),
              __props.image ? (openBlock(), createBlock("meta", {
                key: 6,
                property: "og:image:height",
                content: "630"
              })) : createCommentVNode("", true),
              createVNode("meta", {
                name: "twitter:card",
                content: __props.image ? "summary_large_image" : "summary"
              }, null, 8, ["content"]),
              createVNode("meta", {
                name: "twitter:title",
                content: ogTitle.value
              }, null, 8, ["content"]),
              __props.description ? (openBlock(), createBlock("meta", {
                key: 7,
                name: "twitter:description",
                content: __props.description
              }, null, 8, ["content"])) : createCommentVNode("", true),
              __props.image ? (openBlock(), createBlock("meta", {
                key: 8,
                name: "twitter:image",
                content: __props.image
              }, null, 8, ["content"])) : createCommentVNode("", true)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/SeoHead.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _
};
