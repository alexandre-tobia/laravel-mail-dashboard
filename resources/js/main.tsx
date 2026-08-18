import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'

import App from '@/App'
import '../css/app.css'

if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.classList.add('dark')
}

createRoot(document.getElementById('mail-dashboard')!).render(
    <StrictMode>
        <App />
    </StrictMode>,
)
