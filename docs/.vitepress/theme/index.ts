import Theme from "vitepress/theme";
import "./styles/base.css";
import "./styles/vars.css";
import FeaturePage from "./components/FeaturePage.vue";

export default {
	...Theme,
	enhanceApp({ app }) {
		app.component("FeaturePage", FeaturePage);
	},
};
