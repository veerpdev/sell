import { createApp } from "vue";
import App from "./App.vue";

import router from "./router";
import store from "./store";
import ElementPlus from "element-plus";

//imports for app initialization
import ApiService from "@/core/services/ApiService";
import BillingApiService from "./core/services/BillingApiService";
import { initInlineSvg } from "@/core/plugins/inline-svg";
import { initVeeValidate } from "@/core/plugins/vee-validate";
import VueGoogleMaps from "@fawmi/vue-google-maps";

import HeadingText from "./components/presets/GeneralElements/HeadingText.vue";
import CardSection from "./components/presets/GeneralElements/CardSection.vue";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import ModalWrapper from "@/components/presets/GeneralElements/ModalWrapper.vue";
import InfoSection from "@/components/presets/GeneralElements/InfoSection.vue";
import IconButton from "@/components/presets/GeneralElements/IconButton.vue";
import InlineSVG from "@/components/presets/GeneralElements/InlineSVG.vue";
import LargeIconButton from "@/components/presets/GeneralElements/LargeIconButton.vue";
import CurrencyInput from "@/components/presets/GeneralElements/CurrencyInput.vue";
import "bootstrap";
import VueSignaturePad from "vue-signature-pad";
import CKEditor from "@ckeditor/ckeditor5-vue";
const app = createApp(App);

app.use(store);
app.use(router);
app.use(CKEditor);
app.use(ElementPlus);
app.use(VueGoogleMaps, {
  load: {
    key: "AIzaSyDCDe_kzdqziechOJ53yViPLc6hLQbhX1o",
    libraries: "places",
  },
});
app.use(VueSignaturePad);

ApiService.init(app);
BillingApiService.init();
initInlineSvg(app);
initVeeValidate();

app
  .component("HeadingText", HeadingText)
  .component("CardSection", CardSection)
  .component("InputWrapper", InputWrapper)
  .component("ModalWrapper", ModalWrapper)
  .component("InfoSection", InfoSection)
  .component("InlineSVG", InlineSVG)
  .component("IconButton", IconButton)
  .component("LargeIconButton", LargeIconButton)
  .component("CurrencyInput", CurrencyInput);

app.mount("#app");
