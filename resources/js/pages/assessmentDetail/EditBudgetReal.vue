<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Save, ArrowLeft, Plus, HelpCircle, Trash } from '@lucide/vue';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card"
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { can } from '@/lib/can';

const props = defineProps<{
    assessment: any;
    budgetReals: any;
    years: number[];
    latestYear: any;
}>();

const form = useForm({
    budgetReals: { ...props.budgetReals },
});

const yearsList = ref([...props.years].sort((a, b) => a - b));
const isModalOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const yearToDelete = ref<number | null>(null);

const displayedYears = computed(() => {
    const sorted = [...yearsList.value].sort((a, b) => b - a);
    return sorted.slice(0, 3).sort((a, b) => a - b);
});

const getDefaultBudgetData = (year: number) => ({
    bpk_opinion: '',
    input_status: 'Draft',
    income_after_cleansing: 0,
    pad_after_cleansing: 0,
    tax_income: 0,
    retribution_income: 0,
    asset_income: 0,
    other_pad: 0,
    transfer_income: 0,
    other_legitimate_income: 0,
    other_income: 0,
    spending_after_cleansing: 0,
    operational_spending: 0,
    employee_spending: 0,
    good_service_spending: 0,
    interest_spending: 0,
    subsidy_spending: 0,
    grant_spending: 0,
    social_spending: 0,
    capital_spending: 0,
    land_spending: 0,
    machine_spending: 0,
    building_spending: 0,
    infrastructure_spending: 0,
    other_fix_asset_spending: 0,
    other_asset_spending: 0,
    unexpected_spending: 0,
    total_transfer: 0,
});

const newYearForm = ref({
    year: new Date().getFullYear(),
    data: getDefaultBudgetData(new Date().getFullYear())
});

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    const range = [];
    for (let i = currentYear - 8; i <= currentYear + 2; i++) {
        if (!yearsList.value.includes(i)) {
            range.push(i);
        }
    }
    return range.sort((a, b) => b - a);
});

const addYearData = () => {
    const year = Number(newYearForm.value.year);
    if (!year) return;

    // Use object spread to ensure reactivity is triggered for the form object
    form.budgetReals = {
        ...form.budgetReals,
        [year]: { ...newYearForm.value.data, year: year }
    };

    if (!yearsList.value.includes(year)) {
        yearsList.value.push(year);
        yearsList.value.sort((a, b) => a - b);
    }

    isModalOpen.value = false;

    newYearForm.value = {
        year: availableYears.value[0] || new Date().getFullYear(),
        data: {
            ...getDefaultBudgetData(0),
            input_status: 'Draft',
        }
    };
};

const submit = () => {
    form.put(`/assessment-details/${props.assessment.id}/budget-real`);
};

const confirmDeleteYear = (year: number) => {
    yearToDelete.value = year;
    isDeleteDialogOpen.value = true;
};

const deleteYearData = () => {
    if (!yearToDelete.value) return;
    const year = yearToDelete.value;
    router.delete(`/assessment-details/${props.assessment.id}/budget-real`, {
        data: { year },
        preserveScroll: true,
        onSuccess: () => {
            // Remove from local state
            const { [year]: _, ...rest } = form.budgetReals;
            form.budgetReals = rest;
            yearsList.value = yearsList.value.filter(y => y !== year);
            isDeleteDialogOpen.value = false;
            yearToDelete.value = null;
        },
    });
};

const rows = [
    { label: 'Pendapatan', isHeader: true },
    { key: 'tax_income', label: 'Pendapatan Pajak Daerah', indent: true },
    { key: 'retribution_income', label: 'Pendapatan Retribusi Daerah', indent: true },
    { key: 'asset_income', label: 'Pendapatan Hasil Pengelolaan Kekayaan Daerah yang Dipisah', indent: true },
    { key: 'other_pad', label: 'Lain-lain PAD yang sah', indent: true },
    { key: 'pad_after_cleansing', label: 'Jumlah Pendapatan Asli Daerah (PAD)', bold: true },
    { key: 'transfer_income', label: 'Jumlah Pendapatan transfer', bold: true },
    { key: 'other_legitimate_income', label: 'Jumlah Lain-lain Pendapatan Daerah yang Sah', bold: true },
    { key: 'income_after_cleansing', label: 'Jumlah Pendapatan', bold: true },
    { label: 'Belanja', isHeader: true },
    { label: 'Belanja Operasi', indent: true, isHeader: true },
    { key: 'employee_spending', label: 'Belanja Pegawai', indentLevel: 2 },
    { key: 'good_service_spending', label: 'Belanja Barang dan Jasa', indentLevel: 2 },
    { key: 'interest_spending', label: 'Belanja Bunga', indentLevel: 2 },
    { key: 'subsidy_spending', label: 'Belanja Subsidi', indentLevel: 2 },
    { key: 'grant_spending', label: 'Belanja Hibah', indentLevel: 2 },
    { key: 'social_spending', label: 'Belanja Bantuan Sosial', indentLevel: 2 },
    { key: 'operational_spending', label: 'Jumlah Belanja Operasi', bold: true, indent: true },
    { label: 'Belanja Modal', indent: true, isHeader: true },
    { key: 'land_spending', label: 'Belanja Tanah', indentLevel: 2 },
    { key: 'machine_spending', label: 'Belanja Peralatan dan Mesin', indentLevel: 2 },
    { key: 'building_spending', label: 'Belanja Gedung dan Bangunan', indentLevel: 2 },
    { key: 'infrastructure_spending', label: 'Belanja Jalan Irigasi dan Jaringan', indentLevel: 2 },
    { key: 'other_fix_asset_spending', label: 'Belanja Aset Tetap Lainnya', indentLevel: 2 },
    { key: 'capital_spending', label: 'Jumlah Belanja Modal', bold: true, indent: true },
    { key: 'unexpected_spending', label: 'Jumlah Belanja Tak Terduga', bold: true, indent: true },
    { key: 'spending_after_cleansing', label: 'Jumlah Belanja', bold: true },
    { key: 'total_transfer', label: 'Jumlah Transfer', bold: true },
    { key: 'total_spending_and_transfer', label: 'Jumlah Belanja dan Transfer', bold: true },
    { key: 'surplus_deficit', label: 'Surplus/Defisit', bold: true },
];

const computedKeys = [
    'pad_after_cleansing',
    'income_after_cleansing',
    'operational_spending',
    'capital_spending',
    'spending_after_cleansing',
    'total_spending_and_transfer',
    'surplus_deficit'
];

const isCalculated = (key: string) => computedKeys.includes(key);

const formatNumber = (val: any) => {
    if (val === undefined || val === null) return '0';
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(val) || 0);
};

const parseNumber = (val: string): number => {
    // Remove dots (thousands separator) and replace comma with dot for decimals
    const cleaned = val.replace(/\./g, '').replace(',', '.');
    return Number(cleaned) || 0;
};

const handleCurrencyFocus = (e: FocusEvent) => {
    const input = e.target as HTMLInputElement;
    const raw = parseNumber(input.value);
    input.value = raw === 0 ? '' : String(raw);
};

const handleCurrencyInput = (e: Event, year: number, key: string) => {
    const input = e.target as HTMLInputElement;
    const num = parseNumber(input.value);
    form.budgetReals[year][key] = num;
};

const handleCurrencyBlur = (e: FocusEvent, year: number, key: string) => {
    const input = e.target as HTMLInputElement;
    const num = parseNumber(input.value);
    form.budgetReals[year][key] = num;
    input.value = formatNumber(num);
};

const recalculateBudgetReals = () => {
    Object.keys(form.budgetReals).forEach(year => {
        const item = form.budgetReals[year];

        // PAD: Pendapatan Pajak Daerah + Pendapatan Retribusi Daerah + Pendapatan Hasil Pengelolaan Kekayaan Daerah yang Dipisah + Lain-lain PAD yang sah
        item.pad_after_cleansing = (Number(item.tax_income) || 0) +
            (Number(item.retribution_income) || 0) +
            (Number(item.asset_income) || 0) +
            (Number(item.other_pad) || 0);

        // Total Income: Jumlah PAD + Jumlah Pendapatan transfer + Jumlah Lain-lain Pendapatan Daerah yang Sah
        item.income_after_cleansing = (Number(item.pad_after_cleansing) || 0) +
            (Number(item.transfer_income) || 0) +
            (Number(item.other_legitimate_income) || 0);

        // Belanja Operasi: Belanja Pegawai + Belanja Barang dan Jasa + Belanja Bunga + Belanja Subsidi + Belanja Hibah + Belanja Bantuan Sosial
        item.operational_spending = (Number(item.employee_spending) || 0) +
            (Number(item.good_service_spending) || 0) +
            (Number(item.interest_spending) || 0) +
            (Number(item.subsidy_spending) || 0) +
            (Number(item.grant_spending) || 0) +
            (Number(item.social_spending) || 0);

        // Belanja Modal: Belanja Tanah + Belanja Peralatan dan Mesin + Belanja Gedung dan Bangunan + Belanja Jalan Irigasi dan Jaringan + Belanja Aset Tetap Lainnya
        item.capital_spending = (Number(item.land_spending) || 0) +
            (Number(item.machine_spending) || 0) +
            (Number(item.building_spending) || 0) +
            (Number(item.infrastructure_spending) || 0) +
            (Number(item.other_fix_asset_spending) || 0);

        // Jumlah Belanja: Jumlah Belanja Operasi + Jumlah Belanja Modal + Jumlah Belanja Tak Terduga
        item.spending_after_cleansing = (Number(item.operational_spending) || 0) +
            (Number(item.capital_spending) || 0) +
            (Number(item.unexpected_spending) || 0);

        // Jumlah Belanja dan Transfer: Jumlah Belanja + Jumlah Transfer
        item.total_spending_and_transfer = (Number(item.spending_after_cleansing) || 0) +
            (Number(item.total_transfer) || 0);

        // Surplus/Defisit: Jumlah Pendapatan - Jumlah Belanja dan Transfer
        item.surplus_deficit = (Number(item.income_after_cleansing) || 0) -
            (Number(item.total_spending_and_transfer) || 0);
    });
};

// Kalkulasi awal saat data sudah ada
recalculateBudgetReals();
nextTick(recalculateBudgetReals);

// Recalculate saat data berubah
watch(() => form.budgetReals, recalculateBudgetReals, { deep: true });

</script>

<template>

    <Head title="Edit Budget Real" />

    <AssessmentStepper :assessment-id="assessment.id" :current-step="4" />

    <div class="container mx-auto p-4 space-y-6">
        <div class="flex items-center justify-between mb-6">
            <Dialog v-model:open="isModalOpen">
                <DialogTrigger as-child v-if="can('create-budget_reals')">
                    <Button class="bg-teal-600 hover:bg-teal-700 text-white">
                        <Plus class="w-4 h-4 mr-2" /> Tambah Data
                    </Button>
                </DialogTrigger>
                <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col p-0">
                    <DialogHeader class="p-6 pb-0">
                        <DialogTitle>Tambah Data Anggaran</DialogTitle>
                        <DialogDescription>
                            Masukkan rincian anggaran untuk tahun yang dipilih.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <div class="grid grid-cols-3 gap-4 items-end">
                            <div class="space-y-2">
                                <Label>Tahun Anggaran</Label>
                                <Select v-model="newYearForm.year">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih Tahun" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="y in availableYears" :key="y" :value="y.toString()">
                                            {{ y }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>Opini BPK</Label>
                                <Input v-model="newYearForm.data.bpk_opinion" placeholder="WTP/WDP/dsb." />
                            </div>
                            <div class="space-y-2">
                                <Label>Status Input</Label>
                                <Select v-model="newYearForm.data.input_status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Draft">Draft</SelectItem>
                                        <SelectItem value="Final">Final</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="font-bold border-b pb-2 text-teal-700">Rincian Pendapatan</h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                                <template v-for="row in rows.filter(r => !r.isHeader && !isCalculated(r.key as string) && [
                                    'tax_income', 'retribution_income', 'asset_income', 'other_pad', 'transfer_income', 'other_legitimate_income'
                                ].includes(r.key as string))" :key="row.key">
                                    <div class="space-y-1">
                                        <Label class="text-xs">{{ row.label }}</Label>
                                        <Input type="number" step="0.01"
                                            v-model="(newYearForm.data as any)[row.key as string]" class="h-8" />
                                    </div>
                                </template>
                            </div>

                            <h3 class="font-bold border-b pb-2 text-teal-700 pt-4">Rincian Belanja & Lainnya</h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                                <template v-for="row in rows.filter(r => !r.isHeader && !isCalculated(r.key as string) && [
                                    'employee_spending', 'good_service_spending', 'interest_spending', 'subsidy_spending', 'grant_spending', 'social_spending',
                                    'land_spending', 'machine_spending', 'building_spending', 'infrastructure_spending', 'other_fix_asset_spending',
                                    'unexpected_spending', 'total_transfer'
                                ].includes(r.key as string))" :key="row.key">
                                    <div class="space-y-1">
                                        <Label class="text-xs">{{ row.label }}</Label>
                                        <Input type="number" step="0.01"
                                            v-model="(newYearForm.data as any)[row.key as string]" class="h-8" />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="p-6 pt-2 bg-gray-50 border-t">
                        <Button variant="outline" @click="isModalOpen = false">Batal</Button>
                        <Button @click="addYearData" class="bg-teal-600 hover:bg-teal-700 text-white">Tambah ke
                            Tabel</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Delete Confirmation Dialog -->
            <Dialog v-model:open="isDeleteDialogOpen">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Konfirmasi Hapus</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus data anggaran tahun <strong>{{ yearToDelete }}</strong>? Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="isDeleteDialogOpen = false">Batal</Button>
                        <Button variant="destructive" @click="deleteYearData">Hapus</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <Alert class="mb-6 bg-green-50 text-green-800 border-green-200" v-if="form.recentlySuccessful">
            <AlertTitle>Success</AlertTitle>
            <AlertDescription>
                Budget Real data updated successfully.
            </AlertDescription>
        </Alert>

        <form @submit.prevent="submit"
            class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 font-sans">

            <div class="bg-teal-600 text-white py-4 text-center shadow-inner">

                <div class="flex items-center justify-center gap-2">
                    <h2 class="text-xl font-bold">Data Keuangan</h2>
                    <HoverCard>
                        <HoverCardTrigger>
                            <HelpCircle :size="20" class="text-white" />
                        </HoverCardTrigger>
                        <HoverCardContent>
                            Pada Asessment Data Keuangan, Saudara diminta untuk memasukan data keuangan
                            Pemerintah Daerah(Pemda) berdasarkan laporan keuangan terakhir (audited).
                        </HoverCardContent>
                    </HoverCard>
                </div>
                <p class="text-sm opacity-90">Penilaian Mandiri</p>
            </div>

            <div class="p-6 overflow-x-auto">
                <div class="mb-4 grid grid-cols-2 gap-4 text-sm max-w-lg">
                    <div class="font-semibold text-gray-600">Nama Pemda</div>
                    <div class="text-gray-800">{{ assessment.assessee?.gov?.name }}</div>
                    <div class="font-semibold text-gray-600">Tahun Keuangan yang Terakhir Tersedia</div>
                    <div class="text-gray-800">{{ latestYear }}</div>
                </div>

                <table class="w-full text-sm text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-teal-500 text-white">
                            <th class="p-3 border border-teal-600 w-1/3">Uraian</th>
                            <th v-for="year in displayedYears" :key="year"
                                class="p-3 border border-teal-600 text-center w-32">
                                <div class="flex items-center justify-center gap-1">
                                    {{ year }}
                                    <Button v-if="can('delete-budget_reals')" type="button" variant="ghost" size="sm"
                                        class="h-5 w-5 p-0 text-white/70 hover:text-red-200 hover:bg-transparent"
                                        @click.prevent="confirmDeleteYear(year)">
                                        <Trash :size="13" />
                                    </Button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in rows" :key="index"
                            :class="{ 'bg-gray-50 font-semibold': row.isHeader || row.bold, 'bg-teal-100/50': row.bold }">

                            <td class="p-2 border border-gray-200 align-middle" :class="{
                                'pl-2': !row.indent && !row.indentLevel,
                                'pl-8': row.indent,
                                'pl-14': row.indentLevel === 2,
                                'font-bold': row.bold
                            }">
                                {{ row.label }}
                            </td>

                            <template v-if="row.isHeader">
                                <td v-for="year in displayedYears" :key="year" class="p-2 border border-gray-200 bg-gray-50">
                                </td>
                            </template>

                            <template v-else>
                                <td v-for="year in displayedYears" :key="year" class="p-1 border border-gray-200">
                                    <template v-if="isCalculated(row.key as string)">
                                        <div
                                            class="px-3 py-1.5 text-right font-bold bg-teal-50/50 rounded border border-teal-100 min-h-[32px]">
                                            {{ formatNumber(form.budgetReals[year] ? form.budgetReals[year][row.key as
                                                string] : 0) }}
                                        </div>
                                    </template>
                                    <input v-else type="text"
                                        :value="formatNumber(form.budgetReals[year][row.key as string])"
                                        @focus="handleCurrencyFocus"
                                        @input="handleCurrencyInput($event, year, row.key as string)"
                                        @blur="handleCurrencyBlur($event, year, row.key as string)"
                                        class="flex rounded-md border border-input px-3 py-1 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring w-full text-right h-8 text-sm focus:ring-teal-500 focus:border-teal-500 bg-transparent"
                                        :class="{ 'font-semibold bg-teal-50/50': row.bold }" />
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-4">
                <Link href="/assessments">
                    <Button type="button" variant="outline" class="flex items-center gap-2">
                        <ChevronLeft :size="16" /> Back
                    </Button>
                </Link>
                <div v-if="can('create-assessments')">
                    <Button type="submit" :disabled="form.processing"
                        class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
                        <Save class="w-4 h-4 mr-2" />
                        Simpan dan Lanjutkan
                    </Button>
                </div>
            </div>
        </form>

    </div>
</template>
