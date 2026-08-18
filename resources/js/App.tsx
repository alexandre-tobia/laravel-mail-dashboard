import { useCallback, useEffect, useMemo, useState } from 'react'
import { Mail, RefreshCw, Trash2 } from 'lucide-react'

import { EmailDetail } from '@/components/EmailDetail'
import { EmailList } from '@/components/EmailList'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'
import { deleteAllEmails, deleteEmail, fetchEmail, fetchEmails } from '@/lib/api'
import type { EmailDetail as EmailDetailType, EmailSummary } from '@/types'

const POLL_INTERVAL_MS = 10_000

export default function App() {
    const [emails, setEmails] = useState<EmailSummary[]>([])
    const [storage, setStorage] = useState('')
    const [search, setSearch] = useState('')
    const [selectedId, setSelectedId] = useState<string | null>(null)
    const [detail, setDetail] = useState<EmailDetailType | null>(null)
    const [listLoading, setListLoading] = useState(true)
    const [detailLoading, setDetailLoading] = useState(false)
    const [refreshing, setRefreshing] = useState(false)

    const refresh = useCallback(async (showSpinner = false) => {
        if (showSpinner) setRefreshing(true)

        try {
            const response = await fetchEmails()
            setEmails(response.emails)
            setStorage(response.storage)
        } catch {
            // Keep the current list when a poll fails.
        } finally {
            setListLoading(false)
            if (showSpinner) setRefreshing(false)
        }
    }, [])

    useEffect(() => {
        void refresh()
        const timer = setInterval(() => void refresh(), POLL_INTERVAL_MS)

        return () => clearInterval(timer)
    }, [refresh])

    useEffect(() => {
        if (!selectedId) {
            setDetail(null)

            return
        }

        let cancelled = false
        setDetailLoading(true)

        fetchEmail(selectedId)
            .then((response) => {
                if (!cancelled) setDetail(response.email)
            })
            .catch(() => {
                if (!cancelled) {
                    setDetail(null)
                    setSelectedId(null)
                }
            })
            .finally(() => {
                if (!cancelled) setDetailLoading(false)
            })

        return () => {
            cancelled = true
        }
    }, [selectedId])

    const filteredEmails = useMemo(() => {
        const query = search.trim().toLowerCase()

        if (!query) return emails

        return emails.filter((email) =>
            [email.subject, email.from ?? '', email.to.join(' '), email.preview, email.source_class ?? '']
                .join(' ')
                .toLowerCase()
                .includes(query),
        )
    }, [emails, search])

    const handleDelete = async (id: string) => {
        if (!confirm('Remove this email from the log file?')) return

        await deleteEmail(id)

        if (selectedId === id) setSelectedId(null)

        await refresh()
    }

    const handleClearAll = async () => {
        if (!confirm('Remove all logged emails from the log file? Other log entries are kept.')) return

        await deleteAllEmails()
        setSelectedId(null)
        await refresh()
    }

    return (
        <div className="flex h-screen flex-col">
            <header className="flex items-center gap-3 border-b px-4 py-3">
                <div className="flex items-center gap-2">
                    <div className="bg-primary text-primary-foreground flex size-8 items-center justify-center rounded-lg">
                        <Mail className="size-4" />
                    </div>
                    <h1 className="text-base font-semibold">Mail Dashboard</h1>
                    <Badge variant="secondary">{emails.length}</Badge>
                </div>

                <Input
                    placeholder="Search by subject, sender, recipient…"
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    className="max-w-sm flex-1"
                />

                <div className="ml-auto flex items-center gap-2">
                    <span className="text-muted-foreground hidden font-mono text-xs xl:inline" title={storage}>
                        {storage}
                    </span>
                    <Button variant="outline" size="sm" onClick={() => void refresh(true)}>
                        <RefreshCw className={refreshing ? 'animate-spin' : ''} />
                        Refresh
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        className="text-destructive hover:text-destructive"
                        disabled={emails.length === 0}
                        onClick={() => void handleClearAll()}
                    >
                        <Trash2 />
                        Clear all
                    </Button>
                </div>
            </header>

            <div className="flex min-h-0 flex-1">
                <aside className="w-96 shrink-0 border-r">
                    <EmailList
                        emails={filteredEmails}
                        selectedId={selectedId}
                        loading={listLoading}
                        onSelect={setSelectedId}
                    />
                </aside>

                <Separator orientation="vertical" className="hidden" />

                <main className="min-w-0 flex-1">
                    <EmailDetail
                        email={detail}
                        loading={detailLoading}
                        onDelete={(id) => void handleDelete(id)}
                    />
                </main>
            </div>
        </div>
    )
}
