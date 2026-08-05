export interface Application {
    num: number;
    company: string;
    role: string;
    score: number | null;
    status: string | null;
    date: string | null;
    report_link: string | null;
    pdf_link: string | null;
    notes: string | null;
}

export interface CareerOpsStats {
    metadata?: {
        generatedAt: string;
    };
    tracker?: {
        total: number;
        byStatus: Record<string, number>;
        avgScore: number | null;
        topScore: number | null;
        activeApps: number;
        activeAppsLive: number;
        activeAppsCold: number;
    };
    funnel?: {
        everApplied: number;
        everResponded: number;
        everInterview: number;
        everOffer: number;
        responseRate: number;
        interviewRate: number;
        offerRate: number;
        smallSample: boolean;
    };
    scan?: {
        totalRecorded: number;
        distinctCompanies: number;
        lastSeen: string | null;
    };
    portals?: {
        configuredCompanies: number;
        activePortals: number;
        producingPct: number;
    };
    runs?: {
        totalRuns: number;
        lastRunDate: string | null;
        avgNewPerRun: number;
    };
}
