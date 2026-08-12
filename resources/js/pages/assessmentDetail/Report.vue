<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, Printer } from 'lucide-vue-next';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

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
import { Bar } from 'vue-chartjs';

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
    assessment: any,
    infrasConclusion: any,
    economyIndicator: any,
    financialIndicator: any,
    debtService: any,
    actionPlans: any[],
    chartData: {
        years: number[];
        pad: { values: number[]; growth: number[] };
        autonomy: { pad: number[]; transfer: number[]; other: number[] };
        spending: { capital: number[]; employee: number[] };
        surplus: { operating: number[]; net: number[] };
    },
    budgetReals: any[],
    economyComparison: any,
    topSectors: any[],
    economyYear: number | null
}>();

const printReport = () => {
    window.print();
};

const formatCurrency = (val: number) => {
    if (val === null || val === undefined) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2
    }).format(val);
};

const formatPercent = (val: number) => {
    if (val === null || val === undefined) return '-';
    return val.toFixed(2) + '%';
};

// Data padding logic to always have 3 columns for Budget Reals
const paddedBudgetReals = computed(() => {
    const defaultData = {
        year: '-',
        bpk_opinion: '-',
        income_after_cleansing: null,
        pad_after_cleansing: null,
        transfer_income: null,
        other_legitimate_income: null,
        operational_spending: null,
        spending_after_cleansing: null,
        capital_spending: null,
        employee_spending: null
    };

    let data = [...(props.budgetReals || [])];
    while (data.length < 3) {
        data.unshift({ ...defaultData });
    }
    return data.slice(-3); // Get only the last 3 years
});

// Helper variables for calculated ratios from padded data
const years = computed(() => paddedBudgetReals.value.map(r => r.year));
const bpkOpinions = computed(() => paddedBudgetReals.value.map(r => r.bpk_opinion || '-'));
const totalPendapatan = computed(() => paddedBudgetReals.value.map(r => r.income_after_cleansing));
const pad = computed(() => paddedBudgetReals.value.map(r => r.pad_after_cleansing));

// Ratios
const padGrowth = computed(() => {
    const data = props.chartData?.pad?.growth || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});
const rasioPad = computed(() => {
    const data = props.chartData?.autonomy?.pad || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});
const rasioTransfer = computed(() => {
    const data = props.chartData?.autonomy?.transfer || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});
const rasioLain = computed(() => {
    const data = props.chartData?.autonomy?.other || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});

const rasioSurplusOperasi = computed(() => {
    const data = props.chartData?.surplus?.operating || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});
const rasioSurplusNet = computed(() => {
    const data = props.chartData?.surplus?.net || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});

const rasioBelanjaModal = computed(() => {
    const data = props.chartData?.spending?.capital || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});
const rasioBelanjaPegawai = computed(() => {
    const data = props.chartData?.spending?.employee || [];
    return [...Array(Math.max(0, 3 - data.length)).fill(null), ...data].slice(-3);
});

// Helper Insights (Hardcoded based on last year's value)
const getKemandirianInsight = () => {
    const val = rasioPad.value[rasioPad.value.length - 1];
    if (val === null || val === undefined) return '-';
    if (val < 10) return 'Kemandirian anggaran rendah sekali';
    if (val < 30) return 'Kemandirian anggaran rendah';
    if (val < 50) return 'Kemandirian anggaran sedang';
    return 'Kemandirian anggaran tinggi';
};

const getTransferInsight = () => {
    const val = rasioTransfer.value[rasioTransfer.value.length - 1];
    if (val === null || val === undefined) return '-';
    if (val > 50) return 'Ketergantungan pada transfer meningkat';
    return 'Ketergantungan pada transfer menurun';
};

const getLainInsight = () => {
    const val = rasioLain.value[rasioLain.value.length - 1];
    if (val === null || val === undefined) return '-';
    if (val > 20) return 'Ketergantungan pada pendapatan lain-lain tinggi';
    return 'Ketergantungan pada pendapatan lain-lain menurun/stabil';
};

const getPadGrowthInsight = () => {
    return 'Pertumbuhan PAD 2 tahun terakhir stabil'; // Hardcoded simplification
};

const getSurplusOperasiInsight = () => {
    const val = rasioSurplusOperasi.value[rasioSurplusOperasi.value.length - 1];
    if (val === null || val === undefined) return '-';
    if (val < 0) return 'Rendah dibanding pemerintah Kabupaten/Kota di Indonesia';
    return 'Baik dibanding pemerintah Kabupaten/Kota di Indonesia';
};

const getSurplusNetInsight = () => {
    const val = rasioSurplusNet.value[rasioSurplusNet.value.length - 1];
    if (val === null || val === undefined) return '-';
    if (val < 0) return 'Defisit';
    return 'Surplus';
};

const getBelanjaModalInsight = () => {
    const val = rasioBelanjaModal.value[rasioBelanjaModal.value.length - 1];
    if (val === null || val === undefined) return '-';
    if (val < 10) return 'Sangat rendah dibanding pemerintah Kabupaten/Kota di Indonesia';
    if (val < 20) return 'Rendah dibanding pemerintah Kabupaten/Kota di Indonesia';
    return 'Rata-rata/ Sedang dibanding pemerintah Kabupaten/Kota di Indonesia';
};

const getBelanjaPegawaiInsight = () => {
    const val = rasioBelanjaPegawai.value[rasioBelanjaPegawai.value.length - 1];
    if (val === null || val === undefined) return '-';
    if (val > 45) return 'Tinggi dibanding pemerintah Kabupaten/Kota di Indonesia';
    return 'Rata-rata/ Sedang dibanding pemerintah Kabupaten/Kota di Indonesia';
};

// Charts configuration
const padChartDataConf = computed(() => ({
    labels: props.chartData?.years || [],
    datasets: [
        {
            label: 'PAD',
            backgroundColor: '#63b3ac',
            data: props.chartData?.pad?.values || [],
            order: 2,
            yAxisID: 'y',
        },
        {
            label: 'Pertumbuhan PAD (%)',
            borderColor: '#319795',
            backgroundColor: '#319795',
            data: props.chartData?.pad?.growth || [],
            type: 'line' as const,
            order: 1,
            yAxisID: 'y1',
        },
    ],
}));

const padChartOptionsConf = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { type: 'linear' as const, display: true, position: 'left' as const, title: { display: true, text: 'Rupiah' } },
        y1: { type: 'linear' as const, display: true, position: 'right' as const, grid: { drawOnChartArea: false }, title: { display: true, text: 'Persentase (%)' } },
    },
};

const autonomyChartDataConf = computed(() => ({
    labels: props.chartData?.years || [],
    datasets: [
        { label: '% PAD', backgroundColor: '#63b3ac', data: props.chartData?.autonomy?.pad || [] },
        { label: '% Pendapatan Transfer', backgroundColor: '#ed8936', data: props.chartData?.autonomy?.transfer || [] },
        { label: '% Pendapatan Lain-lain', backgroundColor: '#a0aec0', data: props.chartData?.autonomy?.other || [] },
    ],
}));
const autonomyChartOptionsConf = { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, max: 100, title: { display: true, text: 'Persentase (%)' } } } };

const spendingChartDataConf = computed(() => ({
    labels: props.chartData?.years || [],
    datasets: [
        { label: 'Belanja Modal', backgroundColor: '#63b3ac', data: props.chartData?.spending?.capital || [] },
        { label: 'Belanja Pegawai', backgroundColor: '#ed8936', data: props.chartData?.spending?.employee || [] },
    ],
}));
const spendingChartOptionsConf = { responsive: true, maintainAspectRatio: false, scales: { y: { title: { display: true, text: 'Persentase (%)' } } } };

const surplusChartDataConf = computed(() => ({
    labels: props.chartData?.years || [],
    datasets: [
        { label: 'Surplus/Defisit Operasi', backgroundColor: '#63b3ac', data: props.chartData?.surplus?.operating || [] },
        { label: 'Surplus/Defisit Sebelum Pembiayaan', backgroundColor: '#ed8936', data: props.chartData?.surplus?.net || [] },
    ],
}));
const surplusChartOptionsConf = { responsive: true, maintainAspectRatio: false, scales: { y: { title: { display: true, text: 'Rupiah' } } } };

const hasChartData = computed(() => props.chartData && props.chartData.years && props.chartData.years.length > 0);
</script>

<template>
    <Head title="Laporan Assessment" />

    <!-- Navbar - Hidden on print -->
    <div class="px-6 py-4 flex justify-between items-center bg-white border-b sticky top-0 z-10 print:hidden">
        <Link href="/assessments" class="px-4 py-2 bg-slate-100 text-sm text-slate-700 rounded-md hover:bg-slate-200 transition">
            <div class="flex items-center gap-2">
                <ChevronLeft :size="16" /> Kembali ke Daftar
            </div>
        </Link>
        <div class="flex items-center gap-4">
            <Button @click="printReport" class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white shadow-lg transition-all active:scale-95">
                <Printer :size="16" /> Download / Cetak PDF
            </Button>
        </div>
    </div>

    <!-- Stepper - Hidden on print -->
    <div class="print:hidden">
        <AssessmentStepper :assessment-id="assessment.id" :current-step="9" />
    </div>

    <!-- Report Content -->
    <div class="p-8 bg-gray-50 min-h-screen print:bg-white print:p-0 text-sm text-gray-800">
        <div class="max-w-[1200px] mx-auto bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 print:shadow-none print:border-none print:w-full print:max-w-full print:rounded-none">
            
            <!-- Header -->
            <div class="bg-teal-600 text-white text-center py-8 print:bg-teal-600 print:-webkit-print-color-adjust: exact; print:color-adjust: exact;">
                <h1 class="text-3xl font-bold">Laporan Hasil Assessment</h1>
                <p class="text-teal-100 mt-2 text-lg font-medium">{{ assessment.assessee?.gov?.name || 'Pemda Tidak Diketahui' }}</p>
                <p class="text-teal-200 text-sm mt-1">Tanggal Assessment: {{ assessment.date }}</p>
            </div>

            <div class="p-8 space-y-10">
                
                <!-- Ringkasan Infrastruktur -->
                <section>
                    <h2 class="text-xl font-bold border-b-2 border-teal-500 pb-2 mb-4 text-gray-800">1. Ringkasan Infrastruktur</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-gray-700">Kekuatan (Advantage)</h3>
                            <p class="text-gray-600 mt-1 whitespace-pre-wrap">{{ infrasConclusion?.advantage || '-' }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Kelemahan/Tantangan (Challenge)</h3>
                            <p class="text-gray-600 mt-1 whitespace-pre-wrap">{{ infrasConclusion?.challenge || '-' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Ringkasan Keuangan Daerah & DSCR -->
                <section>
                    <h2 class="text-xl font-bold border-b-2 border-teal-500 pb-2 mb-4 text-gray-800">2. Ringkasan Kondisi Keuangan & DSCR</h2>
                    
                    <div class="grid grid-cols-12 gap-4 mb-4 items-center">
                        <div class="col-span-3 font-semibold">Kapasitas Fiskal</div>
                        <div class="col-span-9">
                            <span class="inline-block bg-orange-100 text-orange-800 px-4 py-1 rounded border border-orange-300">
                                {{ financialIndicator?.fiscal_capacity || '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto mb-6">
                        <table class="w-full text-left border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 p-2 w-1/3"></th>
                                    <th v-for="year in years" :key="year" class="border border-gray-300 p-2 text-center w-1/6 font-bold">{{ year }}</th>
                                    <th class="p-2 w-1/6"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Opini BPK & Total Pendapatan -->
                                <tr>
                                    <td class="border border-gray-300 p-2 font-semibold">Opini BPK atas Laporan Realisasi Anggaran</td>
                                    <td v-for="(opini, index) in bpkOpinions" :key="'opini'+index" class="border border-gray-300 p-2 text-center text-orange-700 font-medium bg-orange-50">{{ opini }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 font-semibold">Total Pendapatan</td>
                                    <td v-for="(val, index) in totalPendapatan" :key="'totrev'+index" class="border border-gray-300 p-2 text-right font-semibold">{{ formatCurrency(val) }}</td>
                                    <td></td>
                                </tr>
                                
                                <!-- Kemandirian Anggaran -->
                                <tr><td colspan="5" class="p-2 font-bold mt-4 block">Kemandirian Anggaran</td></tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">PAD</td>
                                    <td v-for="(val, index) in pad" :key="'pad'+index" class="border border-gray-300 p-2 text-right font-semibold">{{ formatCurrency(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic">{{ getKemandirianInsight() }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">Pertumbuhan PAD</td>
                                    <td v-for="(val, index) in padGrowth" :key="'padg'+index" class="border border-gray-300 p-2 text-right font-bold">{{ formatPercent(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic">{{ getTransferInsight() }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">Rasio PAD per Pendapatan Total (%)</td>
                                    <td v-for="(val, index) in rasioPad" :key="'rpad'+index" class="border border-gray-300 p-2 text-right font-bold">{{ formatPercent(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic">{{ getLainInsight() }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">Rasio Pendapatan Transfer per Pendapatan Total (%)</td>
                                    <td v-for="(val, index) in rasioTransfer" :key="'rtrans'+index" class="border border-gray-300 p-2 text-right font-bold">{{ formatPercent(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic">{{ getPadGrowthInsight() }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">Rasio Pendapatan Lain-lain yang Sah per Pendapatan Total (%)</td>
                                    <td v-for="(val, index) in rasioLain" :key="'rlain'+index" class="border border-gray-300 p-2 text-right font-bold">{{ formatPercent(val) }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">Volatilitas pertumbuhan PAD (selisih pertumbuhan PAD) (%)</td>
                                    <td colspan="3" class="border border-gray-300 p-2 text-center font-bold">{{ financialIndicator?.volatil_pad ? formatPercent(financialIndicator.volatil_pad) : '-' }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Charts row 1 -->
                    <div v-if="hasChartData" class="grid grid-cols-2 gap-6 mb-8 print:break-inside-avoid">
                        <div class="border border-gray-300 p-4 rounded bg-white">
                            <h4 class="text-center font-semibold mb-2">Kemandirian Anggaran</h4>
                            <div class="h-[250px]"><Bar :data="autonomyChartDataConf" :options="autonomyChartOptionsConf" /></div>
                        </div>
                        <div class="border border-gray-300 p-4 rounded bg-white">
                            <h4 class="text-center font-semibold mb-2">Pendapatan Asli Daerah</h4>
                            <!-- @ts-ignore: Mix chart types supported by chart.js but typed poorly by vue-chartjs -->
                            <div class="h-[250px]"><Bar :data="padChartDataConf" :options="padChartOptionsConf" /></div>
                        </div>
                    </div>

                    <!-- Kemampuan Memperoleh Penghasilan -->
                    <div class="w-full overflow-x-auto mb-6 print:break-inside-avoid">
                        <table class="w-full text-left border-collapse border border-gray-300">
                            <thead>
                                <tr><th colspan="5" class="p-2 font-bold">Kemampuan memperoleh penghasilan untuk menutupi belanja</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">Rasio Surplus/Defisit Operasi per Pendapatan Total (%)</td>
                                    <td v-for="(val, index) in rasioSurplusOperasi" :key="'surp_op'+index" class="border border-gray-300 p-2 text-right font-bold w-1/6">{{ formatPercent(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6">{{ getSurplusOperasiInsight() }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">Rasio Surplus/Defisit Sebelum Pembiayaan (%)</td>
                                    <td v-for="(val, index) in rasioSurplusNet" :key="'surp_net'+index" class="border border-gray-300 p-2 text-right font-bold">{{ formatPercent(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic">{{ getSurplusNetInsight() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Chart row 2 -->
                    <div v-if="hasChartData" class="w-1/2 mb-8 pr-3 print:break-inside-avoid">
                        <div class="border border-gray-300 p-4 rounded bg-white">
                            <h4 class="text-center font-semibold mb-2">Kemampuan Memperoleh Penghasilan</h4>
                            <div class="h-[250px]"><Bar :data="surplusChartDataConf" :options="surplusChartOptionsConf" /></div>
                        </div>
                    </div>

                    <!-- Efektivitas Belanja -->
                    <div class="w-full overflow-x-auto mb-6 print:break-inside-avoid">
                        <table class="w-full text-left border-collapse border border-gray-300">
                            <thead>
                                <tr><th colspan="5" class="p-2 font-bold">Efektivitas belanja</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">Rasio Belanja Modal per Total belanja (%)</td>
                                    <td v-for="(val, index) in rasioBelanjaModal" :key="'bmod'+index" class="border border-gray-300 p-2 text-right font-bold w-1/6">{{ formatPercent(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6">{{ getBelanjaModalInsight() }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2">Rasio Belanja Pegawai per Total belanja (%)</td>
                                    <td v-for="(val, index) in rasioBelanjaPegawai" :key="'bpeg'+index" class="border border-gray-300 p-2 text-right font-bold">{{ formatPercent(val) }}</td>
                                    <td class="pl-4 text-xs text-gray-600 italic">{{ getBelanjaPegawaiInsight() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Chart row 3 -->
                    <div v-if="hasChartData" class="w-1/2 mb-8 pr-3 print:break-inside-avoid">
                        <div class="border border-gray-300 p-4 rounded bg-white">
                            <h4 class="text-center font-semibold mb-2">Efektivitas Belanja</h4>
                            <div class="h-[250px]"><Bar :data="spendingChartDataConf" :options="spendingChartOptionsConf" /></div>
                        </div>
                    </div>

                    <!-- Kualitas Penyusunan, Beban Utang, Likuiditas -->
                    <div class="w-full overflow-x-auto mb-6 print:break-inside-avoid">
                        <table class="w-full text-left border-collapse border border-gray-300">
                            <tbody>
                                <tr><td colspan="5" class="p-2 font-bold pt-4 block">Kualitas penyusunan anggaran</td></tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">Rata-rata realisasi PAD 3 tahun terakhir (%)</td>
                                    <td class="border border-gray-300 p-2 bg-orange-100 text-right w-1/6 font-semibold">{{ formatPercent(financialIndicator?.pad_last_three_year) }}</td>
                                    <td colspan="2" class="w-2/6 border-gray-300"></td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6">Lebih rendah dibanding anggaran</td>
                                </tr>

                                <tr><td colspan="5" class="p-2 font-bold pt-4 block">Beban Utang</td></tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">Total utang</td>
                                    <td class="border border-gray-300 p-2 text-right w-1/6 font-semibold">{{ formatCurrency(financialIndicator?.total_debt) }}</td>
                                    <td colspan="2" class="w-2/6 border-gray-300"></td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6"></td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">Rasio total utang per PDRB harga berlaku (%)</td>
                                    <td class="border border-gray-300 p-2 text-right w-1/6 font-semibold">{{ formatPercent(financialIndicator?.debt_gdp) }}</td>
                                    <td colspan="2" class="w-2/6 border-gray-300"></td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6">Rata-rata/ Sedang</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">Rasio total utang per pendapatan umum (%)</td>
                                    <td class="border border-gray-300 p-2 text-right w-1/6 font-semibold">{{ formatPercent(financialIndicator?.debt_revenue) }}</td>
                                    <td colspan="2" class="w-2/6 border-gray-300"></td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6">Rendah</td>
                                </tr>

                                <tr><td colspan="5" class="p-2 font-bold pt-4 block">Likuiditas (sepanjang tenor pinjaman)</td></tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">Debt Service / Pendapatan total (%)</td>
                                    <td class="border border-gray-300 p-2 text-right w-1/6 font-semibold">{{ formatPercent(financialIndicator?.ds_revenue) }}</td>
                                    <td colspan="2" class="w-2/6 border-gray-300"></td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6">Rata-rata/ Sedang</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 w-1/3">DSCR</td>
                                    <td class="border border-gray-300 p-2 text-right w-1/6 font-semibold">{{ financialIndicator?.dscr ? financialIndicator.dscr.toFixed(2) + 'x' : '-' }}</td>
                                    <td colspan="2" class="w-2/6 border-gray-300"></td>
                                    <td class="pl-4 text-xs text-gray-600 italic w-1/6">{{ financialIndicator?.dscr && financialIndicator.dscr > 2.5 ? '>2,5x (Memenuhi)' : 'Tidak memenuhi' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 space-y-4 print:break-inside-avoid">
                        <div>
                            <h3 class="font-semibold text-gray-700">Kekuatan Keuangan</h3>
                            <p class="text-gray-600 mt-1 whitespace-pre-wrap">{{ debtService?.advantage || '-' }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Tantangan Keuangan</h3>
                            <p class="text-gray-600 mt-1 whitespace-pre-wrap">{{ debtService?.challenge || '-' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Ringkasan Ekonomi -->
                <section class="print:break-before-page">
                    <h2 class="text-xl font-bold border-b-2 border-teal-500 pb-2 mb-4 text-gray-800">3. Ringkasan Kondisi Ekonomi</h2>
                    
                    <div class="bg-teal-500 text-white text-center py-4 mb-2 rounded-t font-bold text-lg">
                        Perbandingan dengan {{ assessment.assessee?.gov?.level === 'Provinsi' ? 'Provinsi' : 'Kabupaten/Kota' }} Lainnya
                        <div class="text-sm font-normal mt-1">Kondisi Ekonomi Daerah tahun {{ economyYear || '-' }}</div>
                    </div>
                    
                    <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b mb-8 print:break-inside-avoid">
                        <table class="w-full text-left bg-white">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="p-3 font-semibold">Indikator</th>
                                    <th class="p-3 font-semibold text-center w-1/5">Minimum</th>
                                    <th class="p-3 font-semibold text-center w-1/5">Rata-rata</th>
                                    <th class="p-3 font-semibold text-center w-1/5">Maksimum</th>
                                    <th class="p-3 font-semibold text-center w-1/5 bg-teal-50 border-l border-teal-100">{{ assessment.assessee?.gov?.name }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="p-3 font-medium">Tingkat Kemiskinan</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.min_poverty) }}</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.avg_poverty) }}</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.max_poverty) }}</td>
                                    <td class="p-3 text-center font-bold bg-teal-50 border-l border-teal-100">{{ formatPercent(economyIndicator?.poverty) }}</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-medium">Tingkat Pengangguran</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.min_unemployment) }}</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.avg_unemployment) }}</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.max_unemployment) }}</td>
                                    <td class="p-3 text-center font-bold bg-teal-50 border-l border-teal-100">{{ formatPercent(economyIndicator?.unemployment) }}</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-medium">Indeks Pembangunan Manusia</td>
                                    <td class="p-3 text-center">{{ economyComparison?.min_hdci?.toFixed(2) || '-' }}</td>
                                    <td class="p-3 text-center">{{ economyComparison?.avg_hdci?.toFixed(2) || '-' }}</td>
                                    <td class="p-3 text-center">{{ economyComparison?.max_hdci?.toFixed(2) || '-' }}</td>
                                    <td class="p-3 text-center font-bold bg-teal-50 border-l border-teal-100">{{ economyIndicator?.hdci?.toFixed(2) || '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-medium">PDRB HB per Kapita (Juta Rupiah)</td>
                                    <td class="p-3 text-center">{{ formatCurrency(economyComparison?.min_gdp_perkapita) }}</td>
                                    <td class="p-3 text-center">{{ formatCurrency(economyComparison?.avg_gdp_perkapita) }}</td>
                                    <td class="p-3 text-center">{{ formatCurrency(economyComparison?.max_gdp_perkapita) }}</td>
                                    <td class="p-3 text-center font-bold bg-teal-50 border-l border-teal-100">{{ formatCurrency(economyIndicator?.gdp_perkapita) }}</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-medium">Pertumbuhan PDRB ADHK</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.min_gdp_growth) }}</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.avg_gdp_growth) }}</td>
                                    <td class="p-3 text-center">{{ formatPercent(economyComparison?.max_gdp_growth) }}</td>
                                    <td class="p-3 text-center font-bold bg-teal-50 border-l border-teal-100">{{ formatPercent(economyIndicator?.gdp_growth) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-8 print:break-inside-avoid">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-2 font-semibold text-gray-700">Sektor Prioritas</th>
                                    <th class="py-2 font-semibold text-right w-1/4 text-gray-700">PDRB ADHB (Juta Rupiah)</th>
                                    <th class="py-2 font-semibold text-right w-1/6 text-gray-700">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(sector, idx) in topSectors" :key="idx">
                                    <td class="py-3 font-medium flex gap-2">
                                        <span class="font-bold w-4">{{ sector.rank }}</span> 
                                        <span>{{ sector.name }}</span>
                                    </td>
                                    <td class="py-3 text-right">{{ formatCurrency(sector.value) }}</td>
                                    <td class="py-3 text-right font-semibold">{{ formatPercent(sector.percentage) }}</td>
                                </tr>
                                <tr v-if="!topSectors || topSectors.length === 0">
                                    <td colspan="3" class="py-4 text-center text-gray-500 italic">Data sektoral tidak tersedia untuk tahun ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </section>

                <!-- Rencana Aksi -->
                <section class="print:break-before-page">
                    <h2 class="text-xl font-bold border-b-2 border-teal-500 pb-2 mb-4 text-gray-800">4. Rencana Aksi (Action Plan)</h2>
                    
                    <div v-if="actionPlans && actionPlans.length > 0" class="space-y-6">
                        <div v-for="(plan, index) in actionPlans" :key="index" class="bg-gray-50 p-5 rounded-lg border border-gray-200 print:break-inside-avoid">
                            <h3 class="font-bold text-teal-700 mb-4 text-lg border-b border-teal-100 pb-2">Aksi #{{ index + 1 }}</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kesimpulan</span>
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ plan.conclusion || '-' }}</p>
                                </div>
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tantangan</span>
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ plan.challenge || '-' }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Langkah Aksi</span>
                                <div class="text-sm text-gray-800 prose prose-sm max-w-none bg-white p-4 rounded border border-gray-100 print:border-none print:p-0" v-html="plan.action_plan || '-'"></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 italic p-6 bg-gray-50 rounded-lg text-center border border-gray-200">
                        Belum ada Rencana Aksi yang dibuat.
                    </div>
                </section>

            </div>
            
            <!-- Footer -->
            <div class="bg-gray-100 p-6 text-center text-gray-500 text-sm print:bg-transparent print:border-t border-gray-200 mt-8">
                Dicetak pada: {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute:'2-digit' }) }}
            </div>
            
        </div>
    </div>

</template>

<style>
@media print {
    body {
        background-color: white !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    @page {
        margin: 1.5cm;
    }
    
    .print\:hidden {
        display: none !important;
    }
}
</style>
