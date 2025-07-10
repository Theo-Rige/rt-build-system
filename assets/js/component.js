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

function toggleButtonState(button, className, duration = 2000) {
	button.classList.add(className)

	setTimeout(() => {
		button.classList.remove(className)
	}, duration)
}

function extractRepoFromUrl(url) {
	const match = url.match(/github\.com\/([^\/]+)\/([^\/]+)/)
	return match ? `${match[1]}/${match[2]}` : null
}

async function fetchLatestReleaseDate(repo) {
	try {
		const response = await fetch(`https://api.github.com/repos/${repo}/releases/latest`)
		if (response.ok) {
			const data = await response.json()
			return data.published_at ? new Date(data.published_at) : null
		}
	} catch (error) {
		console.error('Error fetching release data:', error)
	}
	return null
}

async function updateLibraryStatus(statusCell, releaseDate) {
	if (!releaseDate) return

	const index = statusCell.dataset.index
	const data = new FormData()
	data.append('action', 'rtbs_update_library_status')
	data.append('_ajax_nonce', RTBS.nonce)
	data.append('id', RTBS.id)
	data.append('index', index)
	data.append('date', releaseDate.toISOString())

	const updateLibraryStatusRequest = await fetch(RTBS.ajaxUrl, {
		method: 'POST',
		body: data
	})

	const updateLibraryStatusResponse = await updateLibraryStatusRequest.json()

	if (updateLibraryStatusResponse.success) {
		statusCell.innerHTML = updateLibraryStatusResponse.data
	}
}

async function updateLibraryDates() {
	const librariesGrid = document.querySelector('.grid--libraries')

	if (!librariesGrid) return

	const libraryGitHubRepositoryLinks = librariesGrid.querySelectorAll('a.grid__cell--link[href*="github.com"]')

	libraryGitHubRepositoryLinks.forEach(async (link) => {
		const repo = extractRepoFromUrl(link.href)
		const index = link.dataset.index

		if (!repo) return

		const dateCell = librariesGrid.querySelector(`.grid__cell--date[data-index="${index}"]`)
		const statusCell = librariesGrid.querySelector(`.grid__cell--status[data-index="${index}"]`)

		if (dateCell && statusCell) {
			try {
				const releaseDate = await fetchLatestReleaseDate(repo)

				if (releaseDate) {
					dateCell.textContent = releaseDate.toLocaleDateString()
					updateLibraryStatus(statusCell, releaseDate)
				}
			} catch (error) {
				console.error(`Failed to update library date for ${repo}:`, error)
			}
		}
	})
}

document.addEventListener('DOMContentLoaded', () => {
	const preview = document.getElementById('preview')
	const previewExpandButton = document.querySelector('#preview .rtbs-button--expand')
	const copyDesignButton = document.querySelector('.rtbs-button--copy')
	const shareButton = document.querySelector('.rtbs-button--share')
	const codeTabs = document.getElementById('code')

	if (preview && previewExpandButton) {
		previewExpandButton.addEventListener('click', () => {
			preview.removeAttribute('style')
			preview.classList.toggle('expanded')
		})
	}

	if (copyDesignButton) {
		copyDesignButton.addEventListener('click', async () => {
			const componentHTML = preview.innerHTML
			const response = await fetch('https://api.to.design/html', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Authorization: 'Bearer ...'
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

	if (shareButton) {
		shareButton.addEventListener('click', async () => {
			try {
				await navigator.share({
					title: document.title,
					url: window.location.href
				})
			} catch (error) {
				console.error('Failed to share:', error)
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
					theme: 'dark-plus',
					colorReplacements: { '#1e1e1e': '#0f172b' }
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

	updateLibraryDates()
})
