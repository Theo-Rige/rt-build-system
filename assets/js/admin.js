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

async function getLatestReleaseDate(entry, repositoryInput) {
	const repo = extractRepoFromUrl(repositoryInput.value)
	const dateContainer = entry.querySelector('.rtbs-library-date')

	dateContainer.classList.add('rtbs-library-date--loading')

	if (repo && repositoryInput.checkValidity()) {
		const releaseDate = await fetchLatestReleaseDate(repo)
		updateLatestReleaseDateDisplay(entry, releaseDate)
	} else {
		updateLatestReleaseDateDisplay(entry, null)
	}
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

	if (repositoryInput) {
		repositoryInput.addEventListener('blur', () => {
			console.log(`Fetching latest release date for repository: ${repositoryInput.value}`)
			getLatestReleaseDate(entry, repositoryInput)
		})
	}

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

	// Initialize existing entries for repository fetching
	entries.forEach((entry) => {
		const repositoryInput = entry.querySelector('input[name*="[repository]"]')
		if (repositoryInput) {
			getLatestReleaseDate(entry, repositoryInput)
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
