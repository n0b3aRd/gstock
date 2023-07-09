import "./bootstrap";
import "../css/app.css";

import { createApp, h, reactive } from "vue";
import { createInertiaApp } from "@inertiajs/inertia-vue3";
import { InertiaProgress } from "@inertiajs/progress";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const appName = window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob("./Pages/**/*.vue")),
  setup({ el, app, props, plugin }) {
    const myApp = createApp({ render: () => h(app, props) })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
        .use(Toast, {});

    myApp.config.globalProperties.$appState = reactive({ isSideMenuOpen: false, isDark: false, parentSelection: null, elementName: null });

    myApp.mount(el);
    return myApp;
  },
});

InertiaProgress.init({ color: "#4B5563" });
