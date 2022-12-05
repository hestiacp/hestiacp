class AppClass {
	// Actions. More widly used funcs
	Actions = {
		DB: {},
		WEB: {},
		PACKAGE: {},
		MAIL_ACC: {},
		MAIL: {},
	};

	Listeners = {
		DB: {},
		WEB: {},
		PACKAGE: {},
		MAIL_ACC: {},
	};
}

const App = new AppClass();

function elementHideShow(id, trigger) {
	const el = document.querySelector(`#${id}`);
	const showing = el.style.display === 'none';
	el.style.display = showing ? 'block' : 'none';

	if (typeof trigger !== 'undefined') {
		trigger.querySelector('.js-section-toggle-icon').classList.toggle('fa-square-minus', showing);
		trigger.querySelector('.js-section-toggle-icon').classList.toggle('fa-square-plus', !showing);
	}
}
