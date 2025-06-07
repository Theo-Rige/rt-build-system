import { codeToHtml } from 'https://esm.sh/shiki@3.5.0'

const phpCodeSection = document.getElementById('component-php')
const jsCodeSection = document.getElementById('component-js')

if (phpCodeSection) {
	const code = phpCodeSection.querySelector('code')

	if (code) {
		phpCodeSection.innerHTML = await codeToHtml(code.textContent, {
			lang: 'php',
			theme: 'vitesse-dark'
		})
	}
}

if (jsCodeSection) {
	const code = jsCodeSection.querySelector('code')

	if (code) {
		jsCodeSection.innerHTML = await codeToHtml(code.textContent, {
			lang: 'javascript',
			theme: 'vitesse-dark'
		})
	}
}
