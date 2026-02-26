import { createTV } from "tailwind-variants";
import { j as appConfig } from "../ssr.js";
import { unref, mergeProps, createVNode, resolveDynamicComponent, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderVNode } from "vue/server-renderer";
import { Icon } from "@iconify/vue";
const appConfigTv = appConfig;
const tv = /* @__PURE__ */ createTV(appConfigTv.ui?.tv);
const _sfc_main = {
  __name: "Icon",
  __ssrInlineRender: true,
  props: {
    name: { type: null, required: true }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      if (typeof __props.name === "string") {
        _push(ssrRenderComponent(unref(Icon), mergeProps({
          icon: __props.name.replace(/^i-/, "")
        }, _attrs), null, _parent));
      } else {
        ssrRenderVNode(_push, createVNode(resolveDynamicComponent(__props.name), _attrs, null), _parent);
      }
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/vue/components/Icon.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _,
  tv as t
};
