import { Paperclip, Inbox } from 'lucide-react'

import { Badge } from '@/components/ui/badge'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Skeleton } from '@/components/ui/skeleton'
import { timeAgo } from '@/lib/format'
import { cn } from '@/lib/utils'
import type { EmailSummary } from '@/types'

interface EmailListProps {
    emails: EmailSummary[]
    selectedId: string | null
    loading: boolean
    onSelect: (id: string) => void
}

export function EmailList({ emails, selectedId, loading, onSelect }: EmailListProps) {
    if (loading) {
        return (
            <div className="flex flex-col gap-3 p-4">
                {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="flex flex-col gap-2">
                        <Skeleton className="h-4 w-3/4" />
                        <Skeleton className="h-3 w-1/2" />
                    </div>
                ))}
            </div>
        )
    }

    if (emails.length === 0) {
        return (
            <div className="text-muted-foreground flex h-full flex-col items-center justify-center gap-3 p-8 text-center">
                <Inbox className="size-10 opacity-40" />
                <div>
                    <p className="text-foreground text-sm font-medium">No emails yet</p>
                    <p className="mt-1 text-xs">
                        Set <code className="bg-muted rounded px-1 py-0.5">MAIL_MAILER=log</code> and
                        send an email — it will show up here.
                    </p>
                </div>
            </div>
        )
    }

    return (
        <ScrollArea className="h-full">
            <div className="flex flex-col">
                {emails.map((email) => (
                    <button
                        key={email.id}
                        type="button"
                        onClick={() => onSelect(email.id)}
                        className={cn(
                            'hover:bg-accent/60 flex flex-col gap-1 border-b px-4 py-3 text-left transition-colors',
                            selectedId === email.id && 'bg-accent',
                        )}
                    >
                        <div className="flex items-center justify-between gap-2">
                            <span className="truncate text-sm font-medium">{email.subject}</span>
                            <span className="text-muted-foreground shrink-0 text-xs">
                                {timeAgo(email.date)}
                            </span>
                        </div>
                        <div className="text-muted-foreground flex items-center gap-1.5 truncate text-xs">
                            <span className="truncate">{email.to.join(', ') || '—'}</span>
                            {email.attachments_count > 0 && (
                                <Badge variant="outline" className="shrink-0 gap-1 px-1.5 py-0">
                                    <Paperclip className="size-3" />
                                    {email.attachments_count}
                                </Badge>
                            )}
                        </div>
                        {email.preview && (
                            <p className="text-muted-foreground/80 truncate text-xs">{email.preview}</p>
                        )}
                    </button>
                ))}
            </div>
        </ScrollArea>
    )
}
