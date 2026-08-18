import type { EmailDetail, EmailListResponse } from '@/types'

const basePath = window.__MAIL_DASHBOARD__?.basePath ?? '/mail-dashboard'

function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
    const response = await fetch(`${basePath}${path}`, {
        ...init,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...init.headers,
        },
    })

    if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`)
    }

    if (response.status === 204) {
        return undefined as T
    }

    return response.json() as Promise<T>
}

export function fetchEmails(): Promise<EmailListResponse> {
    return request<EmailListResponse>('/api/emails')
}

export function fetchEmail(id: string): Promise<{ email: EmailDetail }> {
    return request<{ email: EmailDetail }>(`/api/emails/${id}`)
}

export function deleteEmail(id: string): Promise<void> {
    return request<void>(`/api/emails/${id}`, { method: 'DELETE' })
}

export function deleteAllEmails(): Promise<void> {
    return request<void>('/api/emails', { method: 'DELETE' })
}

export function attachmentUrl(id: string, index: number): string {
    return `${basePath}/api/emails/${id}/attachments/${index}`
}
