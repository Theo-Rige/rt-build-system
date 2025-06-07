const searchEngineFiltersForm = document.getElementById('search-engine-filters')
const searchEngineResultsContainer = document.getElementById('search-engine-results')

async function getPosts(event) {
	if (event.type === 'submit') event.preventDefault()

	const data = event.type === 'reset' ? new FormData() : new FormData(searchEngineFiltersForm)
	data.append('action', 'rtbs_get_posts')
	data.append('_ajax_nonce', RTBS.nonce)

	const request = await fetch(RTBS.ajaxUrl, {
		method: 'POST',
		body: data
	})

	const response = await request.json()

	if (searchEngineResultsContainer && response.data.html) {
		searchEngineResultsContainer.innerHTML = response.data.html
	}
}

if (searchEngineFiltersForm) {
	searchEngineFiltersForm.addEventListener('submit', getPosts)
	searchEngineFiltersForm.addEventListener('reset', getPosts)
}
