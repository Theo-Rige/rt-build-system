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

librariesEntries.forEach((entry, index) => {
	const repositoryInput = entry.querySelector('input[name*="[repository]"]')

	if (repositoryInput) getLatestReleaseDate(entry, repositoryInput)
})

if (librariesEmptyEntry) {
	const librariesEmptyEntryNode = librariesEmptyEntry.cloneNode(true)

	function getNextEntryIndex() {
		const allEntries = document.querySelectorAll('#rtbs_component_libraries .rtbs-library')
		return allEntries.length
	}

	function updateEntryAttributes(entry, index) {
		const inputs = entry.querySelectorAll('input')
		const labels = entry.querySelectorAll('label')

		inputs.forEach((input) => {
			if (input.name && input.name.includes('rtbs_libraries[')) {
				const fieldName = input.name.match(/\[([^\]]+)\]$/)?.[1]
				if (fieldName) {
					input.name = `rtbs_libraries[${index}][${fieldName}]`
					input.id = `rtbs_libraries[${index}][${fieldName}]`
				}
			}
		})

		labels.forEach((label) => {
			if (label.getAttribute('for') && label.getAttribute('for').includes('rtbs_libraries[')) {
				const fieldName = label.getAttribute('for').match(/\[([^\]]+)\]$/)?.[1]
				if (fieldName) {
					label.setAttribute('for', `rtbs_libraries[${index}][${fieldName}]`)
				}
			}
		})
	}

	function addLastRepeatedEntryLogic(entry) {
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
				(input) => input.checkValidity() && input.value.length > 0
			)

			if (lastEntryIsValid) {
				const allEntries = entry.parentNode.querySelectorAll('.rtbs-library')
				const lastEntry = allEntries[allEntries.length - 1]

				if (lastEntry === entry) {
					const newEntry = librariesEmptyEntryNode.cloneNode(true)
					const nextIndex = getNextEntryIndex()
					updateEntryAttributes(newEntry, nextIndex)
					newEntry.querySelectorAll('input').forEach((input) => (input.value = ''))
					entry.parentNode.appendChild(newEntry)
					addLastRepeatedEntryLogic(newEntry)
				}
			} else {
				const newEntry = entry.parentNode.querySelector('.rtbs-library:last-child')
				if (newEntry && newEntry !== entry) {
					const allInputsEmpty = Array.from(newEntry.querySelectorAll('input')).every(
						(input) => input.value.length === 0
					)

					if (allInputsEmpty) {
						newEntry.remove()
					}
				}
			}
		})
	}

	addLastRepeatedEntryLogic(librariesEmptyEntry)
}
