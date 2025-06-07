import { codeToHtml } from 'https://esm.sh/shiki@3.5.0'

class Tabs {
	constructor(root) {
		this.root = root
		this.tabList = root.querySelector('.tabs-list')
		this.tabs = Array.from(root.querySelectorAll('.tabs-trigger'))
		this.panels = Array.from(root.querySelectorAll('.tabs-panel'))
		this.indicator = this.tabList.querySelector('.tabs-indicator')

		this.tabList.setAttribute('role', 'tablist')

		this.tabs.forEach((tab, index) => {
			tab.setAttribute('role', 'tab')
			tab.setAttribute('tabindex', index === 0 ? '0' : '-1')
			tab.setAttribute('aria-selected', index === 0 ? 'true' : 'false')

			const panel = this.panels[index]

			if (panel) {
				const tabID = tab.id || `tab-${index}`
				const panelId = panel.id || `tabpanel-${index}`
				tab.id = tabID
				panel.id = panelId
				tab.setAttribute('aria-controls', panelId)
				panel.setAttribute('role', 'tabpanel')
				panel.setAttribute('aria-labelledby', tabID)
				panel.hidden = index !== 0
			}
		})

		this.tabs.forEach((tab) => {
			tab.addEventListener('click', (event) => {
				event.preventDefault()
				this.selectTab(tab)
			})
		})

		// Set indicator on init
		this.updateIndicator(this.tabs[0])
	}

	selectTab(selectedTab) {
		this.tabs.forEach((tab) => {
			const selected = tab === selectedTab
			tab.setAttribute('aria-selected', selected)
			tab.tabIndex = selected ? 0 : -1
		})

		this.panels.forEach((panel) => {
			const tabID = selectedTab.getAttribute('aria-controls')
			panel.hidden = panel.id !== tabID
		})

		this.updateIndicator(selectedTab)
	}

	updateIndicator(tab) {
		if (!this.indicator || !tab) return
		const tabRect = tab.getBoundingClientRect()
		const listRect = this.tabList.getBoundingClientRect()
		this.indicator.style.width = `${tabRect.width}px`
		this.indicator.style.left = `${tabRect.left - listRect.left}px`
	}
}

const codeTabs = document.getElementById('code')
const phpCodeSection = document.getElementById('code-tabs-php-panel')
const jsCodeSection = document.getElementById('code-tabs-js-panel')

if (phpCodeSection) {
	const code = phpCodeSection.querySelector('code')

	if (code) {
		phpCodeSection.innerHTML = await codeToHtml(code.textContent, {
			lang: 'php',
			theme: 'github-dark'
		})
	}
}

if (jsCodeSection) {
	const code = jsCodeSection.querySelector('code')

	if (code) {
		jsCodeSection.innerHTML = await codeToHtml(code.textContent, {
			lang: 'js',
			theme: 'github-dark'
		})
	}
}

if (codeTabs) new Tabs(codeTabs)
