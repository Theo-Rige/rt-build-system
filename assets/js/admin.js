const librariesEntries = document.querySelectorAll('#rtbs_component_libraries .rtbs-library')
const librariesEmptyEntry = document.querySelector('#rtbs_component_libraries .rtbs-library:last-child')

function updateLatestReleaseDateDisplay(entry, date) {
	const dateContainer = entry.querySelector('.rtbs-library-date')
	const dateInput = entry.querySelector('input[name*="[date]"]')

	dateContainer.classList.remove('rtbs-library-date--warning', 'rtbs-library-date--danger')

	if (date) {
		dateInput.value = date.toLocaleDateString()

		const now = new Date()
		const sixMonthsAgo = new Date(now)
		const oneYearAgo = new Date(now)
		sixMonthsAgo.setMonth(now.getMonth() - 6)
		oneYearAgo.setFullYear(now.getFullYear() - 1)

		if (date < oneYearAgo) {
			dateContainer.classList.add('rtbs-library-date--danger')
		} else if (date < sixMonthsAgo) {
			dateContainer.classList.add('rtbs-library-date--warning')
		}
	} else {
		dateInput.value = ''
	}

	dateContainer.classList.remove('rtbs-library-date--loading')
}

function extractRepoFromUrl(url) {
	const match = url.match(/github\.com\/([^\/]+)\/([^\/]+)/)
	return match ? `${match[1]}/${match[2]}` : null
}

// Server-side refresh function for library dates
async function refreshLibraryDates() {
	if (!window.wp || !window.wp.data) {
		console.warn('WordPress data API not available')
		return
	}

	const postId = window.wp.data.select('core/editor')?.getCurrentPostId() || 
	              new URLSearchParams(window.location.search).get('post') ||
	              document.querySelector('#post_ID')?.value

	if (!postId) {
		console.warn('Post ID not found')
		return
	}

	const formData = new FormData()
	formData.append('action', 'rtbs_refresh_library_dates')
	formData.append('id', postId)
	formData.append('_ajax_nonce', document.querySelector('#_wpnonce')?.value || '')

	try {
		const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: formData
		})

		const data = await response.json()
		
		if (data.success) {
			// Update the UI with the new library dates
			const libraries = data.data.libraries
			const libraryEntries = document.querySelectorAll('#rtbs_component_libraries .rtbs-library')
			
			libraries.forEach((library, index) => {
				const entry = libraryEntries[index]
				if (entry && library.date) {
					const dateInput = entry.querySelector('input[name*="[date]"]')
					if (dateInput) {
						dateInput.value = library.date
						updateLatestReleaseDateDisplay(entry, new Date(library.date))
					}
				}
			})
			
			console.log('Library dates refreshed successfully')
		} else {
			console.error('Failed to refresh library dates:', data.data?.message)
		}
	} catch (error) {
		console.error('Error refreshing library dates:', error)
	}
}

// Legacy function - kept for backward compatibility but no longer auto-called
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

// Modified function - no longer automatically fetches from GitHub
async function getLatestReleaseDate(entry, repositoryInput) {
	const dateContainer = entry.querySelector('.rtbs-library-date')

	// Check if we already have a date
	const existingDate = entry.querySelector('input[name*="[date]"]').value
	if (existingDate && existingDate.trim() !== '') {
		// Date already exists, just update display without fetching
		updateLatestReleaseDateDisplay(entry, new Date(existingDate))
		return
	}

	dateContainer.classList.add('rtbs-library-date--loading')

	// Clear any existing date and loading state
	updateLatestReleaseDateDisplay(entry, null)
}

function getNextEntryIndex(container, itemSelector) {
	const allEntries = container.querySelectorAll(itemSelector)
	return allEntries.length
}

function updateEntryAttributes(entry, index, fieldPrefix) {
	const inputs = entry.querySelectorAll('input')
	const labels = entry.querySelectorAll('label')

	inputs.forEach((input) => {
		if (input.name && input.name.includes(`${fieldPrefix}[`)) {
			const fieldName = input.name.match(/\[([^\]]+)\]$/)?.[1]
			if (fieldName) {
				input.name = `${fieldPrefix}[${index}][${fieldName}]`
				input.id = `${fieldPrefix}[${index}][${fieldName}]`
			}
		}
	})

	labels.forEach((label) => {
		if (label.getAttribute('for') && label.getAttribute('for').includes(`${fieldPrefix}[`)) {
			const fieldName = label.getAttribute('for').match(/\[([^\]]+)\]$/)?.[1]
			if (fieldName) {
				label.setAttribute('for', `${fieldPrefix}[${index}][${fieldName}]`)
			}
		}
	})
}

function addRepeatedEntryLogic(entry, container, itemSelector, fieldPrefix, emptyEntryNode) {
	const entryInputs = entry.querySelectorAll('input')
	const repositoryInput = entry.querySelector('input[name*="[repository]"]')

	// Repository input no longer automatically fetches dates on blur
	// Dates will be fetched when the form is saved instead

	entry.addEventListener('input', () => {
		const lastEntryIsValid = Array.from(entryInputs).every(
			(input) => input.readOnly || (input.checkValidity() && input.value.length > 0)
		)

		if (lastEntryIsValid) {
			const allEntries = container.querySelectorAll(itemSelector)
			const lastEntry = allEntries[allEntries.length - 1]

			if (lastEntry === entry) {
				const newEntry = emptyEntryNode.cloneNode(true)
				const nextIndex = getNextEntryIndex(container, itemSelector)
				updateEntryAttributes(newEntry, nextIndex, fieldPrefix)
				newEntry.querySelectorAll('input').forEach((input) => (input.value = ''))
				container.appendChild(newEntry)
				addRepeatedEntryLogic(newEntry, container, itemSelector, fieldPrefix, emptyEntryNode)
			}
		} else {
			const lastEntry = container.querySelector(`${itemSelector}:last-child`)
			if (lastEntry && lastEntry !== entry) {
				const allInputsEmpty = Array.from(lastEntry.querySelectorAll('input')).every(
					(input) => input.value.length === 0
				)

				if (allInputsEmpty) {
					lastEntry.remove()
				}
			}
		}
	})
}

function initializeRepeatedFields(config) {
	const { containerSelector, itemSelector, fieldPrefix } = config

	const container = document.querySelector(`${containerSelector} .inside`)
	if (!container) return

	const entries = container.querySelectorAll(itemSelector)
	const emptyEntry = container.querySelector(`${itemSelector}:last-child`)

	// Initialize existing entries display (but don't fetch new dates)
	entries.forEach((entry) => {
		const dateInput = entry.querySelector('input[name*="[date]"]')
		if (dateInput && dateInput.value) {
			// Update display for existing dates
			updateLatestReleaseDateDisplay(entry, new Date(dateInput.value))
		}
	})

	// Set up repeatable logic for the last entry
	if (emptyEntry) {
		const emptyEntryNode = emptyEntry.cloneNode(true)
		addRepeatedEntryLogic(emptyEntry, container, itemSelector, fieldPrefix, emptyEntryNode)
	}
}

// Initialize libraries metabox
initializeRepeatedFields({
	containerSelector: '#rtbs-component-libraries',
	itemSelector: '.rtbs-postbox-item--library',
	fieldPrefix: 'rtbs-libraries'
})

// Initialize references metabox
initializeRepeatedFields({
	containerSelector: '#rtbs-component-references',
	itemSelector: '.rtbs-postbox-item--reference',
	fieldPrefix: 'rtbs-references'
})

// Add event listener for the refresh library dates button
document.addEventListener('DOMContentLoaded', () => {
	const refreshButton = document.getElementById('rtbs-refresh-library-dates')
	if (refreshButton) {
		refreshButton.addEventListener('click', async (e) => {
			e.preventDefault()
			refreshButton.disabled = true
			refreshButton.textContent = 'Refreshing...'
			
			try {
				await refreshLibraryDates()
				refreshButton.textContent = 'Refresh Complete!'
				setTimeout(() => {
					refreshButton.textContent = 'Refresh Release Dates'
					refreshButton.disabled = false
				}, 2000)
			} catch (error) {
				console.error('Error refreshing dates:', error)
				refreshButton.textContent = 'Refresh Failed'
				setTimeout(() => {
					refreshButton.textContent = 'Refresh Release Dates'
					refreshButton.disabled = false
				}, 2000)
			}
		})
	}
})
