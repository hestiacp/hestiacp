import Theme from 'vitepress/theme';
import '@fortawesome/fontawesome-free/css/fontawesome.css';
import '@fortawesome/fontawesome-free/css/brands.css';
import '@fortawesome/fontawesome-free/css/solid.css';
import './styles/base.css';
import './styles/vars.css';
import FeaturePage from './components/FeaturePage.vue';
import InstallPage from './components/InstallPage.vue';

export default {
	...Theme,
	enhanceApp({ app }) {
		app.component('FeaturePage', FeaturePage);
		app.component('InstallPage', InstallPage);
	},
};
