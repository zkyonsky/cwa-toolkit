<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { ref, watch, computed } from 'vue';
import { TrendingUp, DollarSign, Star, Award, Building2, CalendarDays, CheckCircle, FileBarChart } from '@lucide/vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    LineController,
    BarController,
} from 'chart.js';
import { Bar, Line } from 'vue-chartjs';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    LineController,
    BarController
);

const props = defineProps<{
    govs: Array<{ code: number; name: string }>;
    selectedGovCode: number;
    chartData: {
        years: number[];
        pad: { values: number[]; growth: number[] };
        autonomy: { pad: number[]; transfer: number[]; other: number[] };
        spending: { capital: number[]; employee: number[] };
        surplus: { operating: number[]; net: number[] };
    };
    userAssessments?: Array<{
        id: number;
        date: string;
        economy_condition: number | null;
        financial_condition: number | null;
        indicative_rating: string | null;
        final_rating: string | null;
        assessee?: { id: number; gov?: { code: number; name: string } };
    }>;
    adminStats?: {
        totalAssessedPemda: number;
        totalMemadaiPemda: number;
        latestBudgetRealYear: number | null;
        latestBudgetPlanYear: number | null;
    } | null;
}>();

const isUser = computed(() => {
    return (window as any).__page?.props?.auth?.roles?.includes('User') ?? false;
});

const latestAssessment = computed(() => {
    if (!props.userAssessments || props.userAssessments.length === 0) return null;
    return props.userAssessments[0];
});

const selectedGov = ref(props.selectedGovCode.toString());

watch(() => props.selectedGovCode, (newVal) => {
    selectedGov.value = newVal.toString();
});

watch(selectedGov, (newVal) => {
    router.get(dashboard(), { gov_code: newVal }, { preserveState: true, preserveScroll: true });
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// Chart 1: PAD & Pertumbuhan PAD
const padChartData = computed(() => ({
    labels: props.chartData.years,
    datasets: [
        {
            label: 'PAD',
            backgroundColor: '#63b3ac',
            data: props.chartData.pad.values,
            order: 2,
            yAxisID: 'y',
        },
        {
            label: 'Pertumbuhan PAD (%)',
            borderColor: '#319795',
            backgroundColor: '#319795',
            data: props.chartData.pad.growth,
            type: 'line' as const,
            order: 1,
            yAxisID: 'y1',
        },
    ],
}));

const padChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            type: 'linear' as const,
            display: true,
            position: 'left' as const,
            title: { display: true, text: 'Rupiah' },
        },
        y1: {
            type: 'linear' as const,
            display: true,
            position: 'right' as const,
            grid: { drawOnChartArea: false },
            title: { display: true, text: 'Persentase (%)' },
        },
    },
};

// Chart 2: Kemandirian Anggaran
const autonomyChartData = computed(() => ({
    labels: props.chartData.years,
    datasets: [
        {
            label: '% PAD',
            backgroundColor: '#63b3ac',
            data: props.chartData.autonomy.pad,
        },
        {
            label: '% Pendapatan Transfer',
            backgroundColor: '#ed8936',
            data: props.chartData.autonomy.transfer,
        },
        {
            label: '% Pendapatan Lain-lain',
            backgroundColor: '#a0aec0',
            data: props.chartData.autonomy.other,
        },
    ],
}));

const autonomyChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: { stacked: true },
        y: { stacked: true, max: 100, title: { display: true, text: 'Persentase (%)' } },
    },
};

// Chart 3: Kemampuan Memperoleh Penghasilan (Spending)
const spendingChartData = computed(() => ({
    labels: props.chartData.years,
    datasets: [
        {
            label: 'Belanja Modal',
            backgroundColor: '#63b3ac',
            data: props.chartData.spending.capital,
        },
        {
            label: 'Belanja Pegawai',
            backgroundColor: '#ed8936',
            data: props.chartData.spending.employee,
        },
    ],
}));

const spendingChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { title: { display: true, text: 'Persentase (%)' } },
    },
};

// Chart 4: Surplus/Defisit
const surplusChartData = computed(() => ({
    labels: props.chartData.years,
    datasets: [
        {
            label: 'Surplus/Defisit Operasi',
            backgroundColor: '#63b3ac',
            data: props.chartData.surplus.operating,
        },
        {
            label: 'Surplus/Defisit Sebelum Pembiayaan',
            backgroundColor: '#ed8936',
            data: props.chartData.surplus.net,
        },
    ],
}));

const surplusChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { title: { display: true, text: 'Rupiah' } },
    },
};
</script>

<template>

    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div v-if="!$page.props.auth.roles.includes('User')" class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground text-nowrap">Pilih Pemerintahan:</span>
                <Select v-model="selectedGov">
                    <SelectTrigger class="w-[280px]">
                        <SelectValue placeholder="Pilih Pemerintahan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Pemerintahan</SelectLabel>
                            <SelectItem v-for="gov in govs" :key="gov.code" :value="gov.code.toString()">
                                {{ gov.name }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Admin Summary Cards -->
        <div v-if="!$page.props.auth.roles.includes('User') && adminStats" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 to-teal-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Pemda Ter-Assessment</CardTitle>
                    <div class="rounded-lg bg-teal-100 p-2 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400">
                        <Building2 class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">{{ adminStats.totalAssessedPemda }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Jumlah pemda yang sudah di-assess</p>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-green-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Rating Memadai</CardTitle>
                    <div class="rounded-lg bg-green-100 p-2 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                        <CheckCircle class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">{{ adminStats.totalMemadaiPemda }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Pemda dengan Final Rating Memadai</p>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-blue-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Realisasi APBD Terakhir</CardTitle>
                    <div class="rounded-lg bg-blue-100 p-2 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <FileBarChart class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">{{ adminStats.latestBudgetRealYear ?? '-' }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Tahun terakhir data realisasi</p>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-indigo-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">APBD Terakhir</CardTitle>
                    <div class="rounded-lg bg-indigo-100 p-2 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        <CalendarDays class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">{{ adminStats.latestBudgetPlanYear ?? '-' }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Tahun terakhir data APBD</p>
                </CardContent>
            </Card>
        </div>

        <!-- User Assessment Summary Cards -->
        <div v-if="$page.props.auth.roles.includes('User') && latestAssessment" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-blue-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Kondisi Ekonomi Daerah</CardTitle>
                    <div class="rounded-lg bg-blue-100 p-2 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <TrendingUp class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">
                        {{ latestAssessment.economy_condition != null ? latestAssessment.economy_condition.toFixed(2) : '-' }}
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">Skor kondisi ekonomi</p>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-emerald-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Kondisi Keuangan Daerah</CardTitle>
                    <div class="rounded-lg bg-emerald-100 p-2 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <DollarSign class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">
                        {{ latestAssessment.financial_condition != null ? latestAssessment.financial_condition.toFixed(2) : '-' }}
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">Skor kondisi keuangan</p>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-amber-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Indicative Rating</CardTitle>
                    <div class="rounded-lg bg-amber-100 p-2 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        <Star class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">
                        {{ latestAssessment.indicative_rating ?? '-' }}
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">Rating indikatif pemda</p>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden shadow-sm border-sidebar-border/70 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-purple-600/10 pointer-events-none"></div>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Final Rating</CardTitle>
                    <div class="rounded-lg bg-purple-100 p-2 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                        <Award class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold tracking-tight">
                        {{ latestAssessment.final_rating ?? '-' }}
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">Rating final pemda</p>
                </CardContent>
            </Card>
        </div>

        <div v-if="$page.props.auth.roles.includes('User') && !latestAssessment"
            class="flex h-[120px] items-center justify-center rounded-xl border border-dashed text-muted-foreground">
            Belum ada data assessment.
        </div>

        <div v-if="chartData.years.length > 0" class="grid gap-6 md:grid-cols-2">
            <Card class="shadow-sm border-sidebar-border/70">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold text-center">Pendapatan Asli Daerah</CardTitle>
                </CardHeader>
                <CardContent class="h-[350px]">
                    <Bar :data="padChartData" :options="padChartOptions" />
                </CardContent>
            </Card>

            <Card class="shadow-sm border-sidebar-border/70">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold text-center">Kemandirian Anggaran</CardTitle>
                </CardHeader>
                <CardContent class="h-[350px]">
                    <Bar :data="autonomyChartData" :options="autonomyChartOptions" />
                </CardContent>
            </Card>

            <Card class="shadow-sm border-sidebar-border/70">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold text-center">Kemampuan Memperoleh Penghasilan (Belanja)
                    </CardTitle>
                </CardHeader>
                <CardContent class="h-[350px]">
                    <Bar :data="spendingChartData" :options="spendingChartOptions" />
                </CardContent>
            </Card>

            <Card class="shadow-sm border-sidebar-border/70">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold text-center">Kemampuan Memperoleh Penghasilan
                        (Surplus/Defisit)</CardTitle>
                </CardHeader>
                <CardContent class="h-[350px]">
                    <Bar :data="surplusChartData" :options="surplusChartOptions" />
                </CardContent>
            </Card>
        </div>

        <div v-else
            class="flex h-[400px] items-center justify-center rounded-xl border border-dashed text-muted-foreground">
            Tidak ada data untuk pemerintahan yang dipilih.
        </div>
    </div>
</template>
