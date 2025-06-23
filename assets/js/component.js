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

const preview = document.getElementById('preview')
const previewExpandButton = document.querySelector('#preview .preview__expand')
const copyDesignButton = document.querySelector('.rtbs-button--copy-design')
const codeTabs = document.getElementById('code')

if (preview && previewExpandButton) {
	previewExpandButton.addEventListener('click', () => {
		preview.removeAttribute('style')
		preview.classList.toggle('preview--expanded')
	})
}

if (copyDesignButton) {
	copyDesignButton.addEventListener('click', async () => {
		const componentHTML = preview.innerHTML
		const response = await fetch('https://api.to.design/html', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				Authorization: 'Bearer zpka_4c3accd44d2e4c36911c7ab921e46f74_26e07eb8'
			},
			body: JSON.stringify({ html: componentHTML, clip: true })
		})
		if (response.ok) {
			const clipboardDataFromAPI = await response.text()
			try {
				await navigator.clipboard.writeText(clipboardDataFromAPI)
				toggleButtonState(copyDesignButton, 'copy--success')
			} catch (error) {
				console.error('Failed to copy to clipboard:', error)
				toggleButtonState(copyDesignButton, 'copy--error')
			}
		} else {
			console.error('Failed to fetch clipboard data from API:', response.statusText)
			toggleButtonState(copyDesignButton, 'copy--error')
		}
	})
}

if (codeTabs) {
	const codePanels = codeTabs.querySelectorAll('.tabs-panel')

	codePanels.forEach(async (panel) => {
		const code = panel.querySelector('code')
		if (!code) return

		const copyButton = panel.querySelector('.copy')
		const codeContainer = panel.querySelector('.code')

		try {
			const lang = codeContainer.dataset.lang || 'javascript'
			const formattedCode = await codeToHtml(code.textContent, {
				lang,
				theme: 'github-dark',
				colorReplacements: { '#24292e': '#181d27' }
			})

			if (codeContainer) codeContainer.innerHTML = formattedCode

			if (copyButton) {
				copyButton.addEventListener('click', async () => {
					try {
						await navigator.clipboard.writeText(code.textContent)
						toggleButtonState(copyButton, 'copy--success')
					} catch (error) {
						console.error('Failed to copy:', error)
						toggleButtonState(copyButton, 'copy--error')
					}
				})
			}
		} catch (error) {
			console.error('Error processing code panel:', error)
		}
	})

	new Tabs(codeTabs)
}

function toggleButtonState(button, className, duration = 2000) {
	button.classList.add(className)

	setTimeout(() => {
		button.classList.remove(className)
	}, duration)
}
