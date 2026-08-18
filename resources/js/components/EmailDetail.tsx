import { Download, MailOpen, Paperclip, Trash2 } from 'lucide-react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Separator } from '@/components/ui/separator'
import { Skeleton } from '@/components/ui/skeleton'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { attachmentUrl } from '@/lib/api'
import { formatBytes, formatDate } from '@/lib/format'
import type { EmailDetail as EmailDetailType } from '@/types'

interface EmailDetailProps {
    email: EmailDetailType | null
    loading: boolean
    onDelete: (id: string) => void
}

function AddressRow({ label, addresses }: { label: string; addresses: string[] }) {
    if (addresses.length === 0) return null

    return (
        <div className="flex flex-wrap items-baseline gap-1.5">
            <span className="text-muted-foreground w-12 shrink-0 text-xs font-medium uppercase">
                {label}
            </span>
            {addresses.map((address) => (
                <Badge key={address} variant="secondary" className="font-normal">
                    {address}
                </Badge>
            ))}
        </div>
    )
}

export function EmailDetail({ email, loading, onDelete }: EmailDetailProps) {
    if (loading) {
        return (
            <div className="flex flex-col gap-4 p-6">
                <Skeleton className="h-7 w-2/3" />
                <Skeleton className="h-4 w-1/2" />
                <Skeleton className="h-64 w-full" />
            </div>
        )
    }

    if (!email) {
        return (
            <div className="text-muted-foreground flex h-full flex-col items-center justify-center gap-3">
                <MailOpen className="size-12 opacity-30" />
                <p className="text-sm">Select an email to preview it</p>
            </div>
        )
    }

    const defaultTab = email.html_body ? 'html' : email.text_body ? 'text' : 'raw'

    return (
        <div className="flex h-full flex-col">
            <div className="flex flex-col gap-3 border-b p-6 pb-4">
                <div className="flex items-start justify-between gap-4">
                    <h2 className="text-lg font-semibold">{email.subject}</h2>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="text-muted-foreground hover:text-destructive shrink-0"
                        title="Delete this email from the log"
                        onClick={() => onDelete(email.id)}
                    >
                        <Trash2 />
                    </Button>
                </div>

                <div className="flex flex-col gap-1.5">
                    <AddressRow label="From" addresses={email.from ? [email.from] : []} />
                    <AddressRow label="To" addresses={email.to} />
                    <AddressRow label="Cc" addresses={email.cc} />
                    <AddressRow label="Bcc" addresses={email.bcc} />
                    <AddressRow label="Reply" addresses={email.reply_to} />
                </div>

                <div className="text-muted-foreground flex items-center gap-2 text-xs">
                    <span>{formatDate(email.date)}</span>
                    <Separator orientation="vertical" className="h-3" />
                    <span>{formatBytes(email.size)}</span>
                    {email.source_class && (
                        <>
                            <Separator orientation="vertical" className="h-3" />
                            <span className="truncate font-mono" title={email.source_class}>
                                {email.source_class.split('\\').pop()}
                            </span>
                        </>
                    )}
                </div>

                {email.attachments.length > 0 && (
                    <div className="flex flex-wrap gap-2">
                        {email.attachments.map((attachment, index) => (
                            <Button key={index} variant="outline" size="sm" asChild>
                                <a href={attachmentUrl(email.id, index)} download={attachment.filename}>
                                    <Paperclip />
                                    <span className="max-w-48 truncate">{attachment.filename}</span>
                                    <span className="text-muted-foreground">
                                        {formatBytes(attachment.size)}
                                    </span>
                                    <Download />
                                </a>
                            </Button>
                        ))}
                    </div>
                )}
            </div>

            <Tabs defaultValue={defaultTab} className="flex min-h-0 flex-1 flex-col gap-0" key={email.id}>
                <div className="border-b px-6 py-2">
                    <TabsList>
                        <TabsTrigger value="html" disabled={!email.html_body}>
                            HTML
                        </TabsTrigger>
                        <TabsTrigger value="text" disabled={!email.text_body}>
                            Text
                        </TabsTrigger>
                        <TabsTrigger value="raw">Source</TabsTrigger>
                        <TabsTrigger value="headers">Headers</TabsTrigger>
                    </TabsList>
                </div>

                <TabsContent value="html" className="min-h-0">
                    <iframe
                        title="Email HTML preview"
                        sandbox=""
                        srcDoc={email.html_body ?? ''}
                        className="h-full w-full border-0 bg-white"
                    />
                </TabsContent>

                <TabsContent value="text" className="min-h-0">
                    <ScrollArea className="h-full">
                        <pre className="p-6 font-mono text-sm whitespace-pre-wrap">
                            {email.text_body}
                        </pre>
                    </ScrollArea>
                </TabsContent>

                <TabsContent value="raw" className="min-h-0">
                    <ScrollArea className="h-full">
                        <pre className="text-muted-foreground p-6 font-mono text-xs whitespace-pre-wrap">
                            {email.raw}
                        </pre>
                    </ScrollArea>
                </TabsContent>

                <TabsContent value="headers" className="min-h-0">
                    <ScrollArea className="h-full">
                        <table className="w-full text-sm">
                            <tbody>
                                {email.headers.map((header, index) => (
                                    <tr key={index} className="border-b last:border-0">
                                        <td className="text-muted-foreground w-48 px-6 py-2 align-top font-medium whitespace-nowrap">
                                            {header.name}
                                        </td>
                                        <td className="py-2 pr-6 font-mono text-xs break-all">
                                            {header.value}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </ScrollArea>
                </TabsContent>
            </Tabs>
        </div>
    )
}
