export interface EmailSummary {
    id: string
    subject: string
    from: string | null
    to: string[]
    date: string | null
    source_class: string | null
    preview: string
    has_html: boolean
    has_text: boolean
    attachments_count: number
}

export interface AttachmentMeta {
    filename: string
    content_type: string
    size: number
    content_id: string | null
}

export interface EmailDetail {
    id: string
    message_id: string | null
    subject: string
    from: string | null
    to: string[]
    cc: string[]
    bcc: string[]
    reply_to: string[]
    date: string | null
    source_class: string | null
    text_body: string | null
    html_body: string | null
    attachments: AttachmentMeta[]
    headers: { name: string; value: string }[]
    raw: string
    size: number
}

export interface EmailListResponse {
    emails: EmailSummary[]
    count: number
    storage: string
}

declare global {
    interface Window {
        __MAIL_DASHBOARD__?: { basePath: string }
    }
}
