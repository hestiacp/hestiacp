// Add class to (sticky) toolbar on list view pages when scrolling
export default function handleStickyToolbar() {
	const toolbar = document.querySelector('.toolbar');
	const header = document.querySelector('.top-bar');

	if (!toolbar || !header) {
		return;
	}

	window.addEventListener('scroll', addClassOnScroll);

	function addClassOnScroll() {
		const toolbarRectTop = toolbar.getBoundingClientRect().top;
		const scrolledDistance = window.scrollY;
		const clientTop = document.documentElement.clientTop;
		const toolbarOffsetTop = toolbarRectTop + scrolledDistance - clientTop;
		const headerHeight = header.offsetHeight;
		const isToolbarActive = scrolledDistance > toolbarOffsetTop - headerHeight;

		toolbar.classList.toggle('active', isToolbarActive);
	}
}
