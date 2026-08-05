<script setup lang="ts">
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Application, CareerOpsStats } from '@/types/career-ops';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Activity, FileText, Radar, Target } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    applications: Application[];
    stats: CareerOpsStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/' }];

const statusVariant: Record<string, BadgeVariants['variant']> = {
    Hired: 'success',
    Offer: 'success',
    Interview: 'info',
    Responded: 'info',
    Applied: 'secondary',
    Evaluated: 'outline',
    Rejected: 'destructive',
    Discarded: 'outline',
    SKIP: 'outline',
};

function variantForStatus(status: string | null): BadgeVariants['variant'] {
    if (!status) return 'outline';
    return statusVariant[status] ?? 'outline';
}

function formatScore(score: number | null): string {
    return score === null ? '—' : `${score.toFixed(1)}/5`;
}

function formatDate(date: string | null): string {
    if (!date) return '—';
    return new Date(date).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatPercent(value: number | undefined | null): string {
    return value === undefined || value === null ? '—' : `${Math.round(value)}%`;
}

const tracker = computed(() => props.stats?.tracker);
const funnel = computed(() => props.stats?.funnel);
const scan = computed(() => props.stats?.scan);
const portals = computed(() => props.stats?.portals);
const runs = computed(() => props.stats?.runs);

const funnelStages = computed(() => [
    { label: 'Applied', value: funnel.value?.everApplied },
    { label: 'Responded', value: funnel.value?.everResponded, rate: funnel.value?.responseRate },
    { label: 'Interview', value: funnel.value?.everInterview, rate: funnel.value?.interviewRate },
    { label: 'Offer', value: funnel.value?.everOffer, rate: funnel.value?.offerRate },
]);

const hasStats = computed(() => Boolean(props.stats && Object.keys(props.stats).length > 0));

function staggerDelay(index: number, stepMs: number, maxSteps: number): string {
    return `${Math.min(index, maxSteps) * stepMs}ms`;
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div v-if="hasStats" class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 fill-mode-both duration-300" :style="{ animationDelay: staggerDelay(0, 60, 3) }">
                    <CardHeader class="flex flex-row items-center justify-between gap-2 space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Applications</CardTitle>
                        <FileText class="size-4 text-muted-foreground" aria-hidden="true" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-semibold tabular-nums">{{ tracker?.total ?? 0 }}</div>
                        <CardDescription>avg score {{ formatScore(tracker?.avgScore ?? null) }}, top {{ formatScore(tracker?.topScore ?? null) }}</CardDescription>
                    </CardContent>
                </Card>

                <Card class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 fill-mode-both duration-300" :style="{ animationDelay: staggerDelay(1, 60, 3) }">
                    <CardHeader class="flex flex-row items-center justify-between gap-2 space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Funnel</CardTitle>
                        <Target class="size-4 text-muted-foreground" aria-hidden="true" />
                    </CardHeader>
                    <CardContent class="space-y-1.5">
                        <div v-for="stage in funnelStages" :key="stage.label" class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">{{ stage.label }}</span>
                            <span class="tabular-nums">
                                {{ stage.value ?? 0 }}
                                <span v-if="stage.rate !== undefined" class="text-muted-foreground">({{ formatPercent(stage.rate) }})</span>
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <Card class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 fill-mode-both duration-300" :style="{ animationDelay: staggerDelay(2, 60, 3) }">
                    <CardHeader class="flex flex-row items-center justify-between gap-2 space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Pipeline health</CardTitle>
                        <Activity class="size-4 text-muted-foreground" aria-hidden="true" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-semibold tabular-nums">{{ tracker?.activeApps ?? 0 }}</div>
                        <CardDescription>
                            {{ tracker?.activeAppsLive ?? 0 }} live, {{ tracker?.activeAppsCold ?? 0 }} cold ·
                            {{ portals?.activePortals ?? 0 }}/{{ portals?.configuredCompanies ?? 0 }} portals producing ({{ formatPercent(portals?.producingPct) }})
                        </CardDescription>
                    </CardContent>
                </Card>

                <Card class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 fill-mode-both duration-300" :style="{ animationDelay: staggerDelay(3, 60, 3) }">
                    <CardHeader class="flex flex-row items-center justify-between gap-2 space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Scan activity</CardTitle>
                        <Radar class="size-4 text-muted-foreground" aria-hidden="true" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-semibold tabular-nums">{{ scan?.totalRecorded ?? 0 }}</div>
                        <CardDescription>
                            {{ scan?.distinctCompanies ?? 0 }} companies · last run {{ formatDate(runs?.lastRunDate ?? null) }}
                            ({{ runs?.avgNewPerRun ?? 0 }} new/run)
                        </CardDescription>
                    </CardContent>
                </Card>
            </div>

            <Card class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 fill-mode-both duration-300" style="animation-delay: 180ms">
                <CardHeader>
                    <CardTitle>Applications</CardTitle>
                    <CardDescription>Mirrors your local career-ops tracker. Read-only — updates happen locally.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="applications.length === 0" class="flex flex-col items-center gap-2 py-12 text-center text-muted-foreground">
                        <FileText class="size-8" aria-hidden="true" />
                        <p class="text-sm">No applications synced yet. Run career-ops locally and the sync-watcher will populate this table.</p>
                    </div>
                    <Table v-else>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Company</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Score</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Report</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(application, index) in applications"
                                :key="application.num"
                                class="motion-safe:animate-in motion-safe:fade-in fill-mode-both duration-200"
                                :style="{ animationDelay: staggerDelay(index, 30, 10) }"
                            >
                                <TableCell class="font-medium">{{ application.company }}</TableCell>
                                <TableCell>{{ application.role }}</TableCell>
                                <TableCell class="tabular-nums">{{ formatScore(application.score) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="variantForStatus(application.status)">{{ application.status ?? 'Unknown' }}</Badge>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{ formatDate(application.date) }}</TableCell>
                                <TableCell>
                                    <a
                                        v-if="application.report_link"
                                        :href="application.report_link"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-primary underline-offset-4 hover:underline"
                                    >
                                        View
                                    </a>
                                    <span v-else class="text-muted-foreground">—</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
