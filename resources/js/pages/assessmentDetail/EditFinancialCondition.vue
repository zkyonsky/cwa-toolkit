<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Rocket, Filter, RefreshCw, HelpCircle } from '@lucide/vue';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { can } from '@/lib/can';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";

const props = defineProps({
  assessment: Object as () => any,
  govName: String,
  govLevel: String,
  years: Array as () => number[],
  financialData: Object as () => any,
  financialIndicator: Object as () => any,
  debtService: Object as () => any,
  financing: Object as () => any,
});

const form = useForm({
  volatil_pad: props.financialIndicator?.volatil_pad || 0,
  pad_last_three_year: props.financialIndicator?.pad_last_three_year || 0,
  total_debt: props.debtService?.loan_withdrawal_plus_os || 0,
  fiscal_capacity: props.financialIndicator?.fiscal_capacity || '',
  bpk_opinions: {} as Record<string, string>,
});

watch(() => props.financialData, (newData) => {
  if (newData) {
    for (const year in newData) {
      if (!form.bpk_opinions[year]) {
        form.bpk_opinions[year] = newData[year].bpk_opinion || '';
      }
    }
  }
}, { immediate: true, deep: true });

const startYear = ref(props.years ? props.years[0] : (new Date().getFullYear() - 3));
const endYear = ref(props.years ? props.years[props.years.length - 1] : (new Date().getFullYear() - 1));

const availableYears = Array.from({ length: 15 }, (_, i) => new Date().getFullYear() - i);

const handleYearChange = () => {
  router.get(`/assessment-details/${props.assessment.id}/financial-condition`, {
    start_year: startYear.value,
    end_year: endYear.value
  }, {
    preserveState: true,
    preserveScroll: true
  });
}

// Calculate the latest year data for pushing to DB
const latestYear = computed(() => {
  return props.years ? props.years[props.years.length - 1] : new Date().getFullYear();
});

const latestData = computed(() => {
  return props.financialData?.[latestYear.value] || {};
});

const latestPDRB = computed(() => {
  return latestData.value.total_gdp || 0;
});

const latestTotalRev = computed(() => {
  return latestData.value.total_revenue || 0;
});

const computedVolatilPad = computed(() => {
  if (!props.years || props.years.length < 2) return 0;
  // ensure sorted
  const sortedYears = [...props.years].sort((a, b) => a - b);
  const latestYearVal = sortedYears[sortedYears.length - 1];
  const prevYearVal = sortedYears[sortedYears.length - 2];

  const latestGrowth = Number(props.financialData?.[latestYearVal]?.pad_growth) || 0;
  const prevGrowth = Number(props.financialData?.[prevYearVal]?.pad_growth) || 0;

  // Round to 2 decimal places to avoid floating point issues
  return Number(Math.abs(latestGrowth - prevGrowth).toFixed(2));
});

watch(computedVolatilPad, (newVal) => {
  form.volatil_pad = newVal;
}, { immediate: true });

const dsRevenue = computed(() => {
  if (!latestTotalRev.value || !props.debtService) return 0;
  const ds = props.debtService;
  const annualDs = (Number(ds.avg_annual_return) || 0) + (Number(ds.avg_annual_interest) || 0) + (Number(ds.avg_annual_cost) || 0) + (Number(ds.avg_exis_payment) || 0) + (Number(props.financing?.ds_exist) || 0);
  return (annualDs / latestTotalRev.value) * 100;
});

const submit = () => {
  form.transform((data) => ({
    ...data,
    total_revenue: latestData.value.total_revenue,
    total_pad: latestData.value.total_pad,
    pad_growth: latestData.value.pad_growth,
    pad_revenue: latestData.value.pad_ratio,
    transfer_revenue: latestData.value.transfer_ratio,
    other_total_revenue: latestData.value.other_legit_ratio,
    operation_revenue: latestData.value.op_surplus_deficit_ratio,
    surplus_deficit_before_financing: latestData.value.surplus_deficit_before_fin,
    capital_spending: latestData.value.cap_spending_ratio,
    employee_spending: latestData.value.emp_spending_ratio,
    debt_gdp: latestPDRB.value ? (data.total_debt / latestPDRB.value) * 0.0001 : 0,
    debt_revenue: props.debtService?.unappropiated_revenue ? (data.total_debt / props.debtService.unappropiated_revenue) * 100 : 0,
    ds_revenue: dsRevenue.value,
    dscr: props.debtService?.dscr || 0,
  })).put(`/assessment-details/${props.assessment.id}/financial-condition`, {
    preserveScroll: true,
  });
};

const formatCurrency = (value: number) => {
  if (!value) return "0";
  return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
};

const formatPercent = (value: number) => {
  if (!value) return "0.00%";
  return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + "%";
};
</script>

<template>

  <Head title="Edit Kondisi Ekonomi Daerah" />

  <Alert class="m-2 bg-gray-100" v-if="$page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ $page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <AssessmentStepper :assessment-id="assessment.id" :current-step="4" />

  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div class="max-w-[1400px] mx-auto">
      <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">

        <div class="bg-teal-600 text-white py-4 text-center shadow-inner">
          <div class="flex items-center justify-center gap-2">
            <h1 class="text-2xl font-bold text-center">Kondisi Keuangan Daerah</h1>
            <HoverCard>
              <HoverCardTrigger>
                <HelpCircle :size="20" class="text-white" />
              </HoverCardTrigger>
              <HoverCardContent>
                Pada Asessment Kondisi Keuangan, Saudara diminta untuk menentukan kategori Kapasitas Fiskal Daerah,
                Menyesuaikan Volatilitas pertumbuhan PAD, dan Rata-rata realisasi PAD 3 tahun terakhir
              </HoverCardContent>
            </HoverCard>
          </div>
          <p class="text-center text-sm font-medium text-teal-100 mt-1">Kondisi dan Manajemen Keuangan Daerah</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6 p-6 text-sm">
          <div class="grid grid-cols-12 gap-y-1 mb-8">
            <div class="col-span-3 font-bold">Pemda</div>
            <div class="col-span-4 border px-2 py-1 bg-orange-100/50 text-orange-800">{{ govName }}</div>
            <div class="col-span-5"></div>

            <div class="col-span-3 font-bold">Tingkat Pemerintahan</div>
            <div class="col-span-4 border px-2 py-1 bg-orange-100/50 text-orange-800">{{ govLevel }}</div>
            <div class="col-span-5"></div>

            <div class="col-span-3 font-bold flex items-center">Kapasitas Fiskal</div>
            <div class="col-span-4 border bg-orange-100/50">
              <Select v-model="form.fiscal_capacity">
                <SelectTrigger class="w-full border-none bg-transparent text-orange-800 shadow-none focus:ring-0 h-8">
                  <SelectValue placeholder="Pilih Kapasitas Fiskal" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Sangat Rendah">Sangat Rendah</SelectItem>
                  <SelectItem value="Rendah">Rendah</SelectItem>
                  <SelectItem value="Sedang">Sedang</SelectItem>
                  <SelectItem value="Tinggi">Tinggi</SelectItem>
                  <SelectItem value="Sangat Tinggi">Sangat Tinggi</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="col-span-5"></div>

            <!-- <div class="col-span-12 mt-4 flex items-center gap-4 bg-gray-50 p-2 border rounded">
                  <div class="flex items-center gap-2">
                    <Filter class="size-4 text-gray-500" />
                    <span class="font-bold">Rentang Tahun</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <Select v-model="startYear" @update:model-value="handleYearChange">
                      <SelectTrigger class="w-32 h-8">
                        <SelectValue placeholder="Dari" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="y in availableYears" :key="'start' + y" :value="y">{{ y }}</SelectItem>
                      </SelectContent>
                    </Select>
                    <span>s/d</span>
                    <Select v-model="endYear" @update:model-value="handleYearChange">
                      <SelectTrigger class="w-32 h-8">
                        <SelectValue placeholder="Sampai" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="y in availableYears" :key="'end' + y" :value="y">{{ y }}</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <p class="text-xs text-gray-500">Data akan diperbarui otomatis saat tahun dipilih.</p>
                </div> -->
          </div>

          <!-- Main Table Grid -->
          <div class="border divide-y">

            <!-- Headers -->
            <div class="grid grid-cols-12 divide-x bg-gray-100 font-bold items-center sticky top-0">
              <div class="col-span-4 p-2 text-right shadow-sm">Tahun</div>
              <div class="col-span-2 p-2 text-center" v-for="year in years" :key="year">{{ year }}</div>
              <div class="col-span-2 p-2">Keterangan / Status</div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2 font-bold">Opini BPK atas Laporan Realisasi Anggaran</div>
              <div class="col-span-2 p-1 border-r bg-orange-50" v-for="year in years" :key="'bpk' + year">
                <Select v-model="form.bpk_opinions[year]">
                  <SelectTrigger class="h-8 border-orange-300 text-orange-700 bg-white font-medium text-xs">
                    <SelectValue placeholder="-" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="WTP">WTP</SelectItem>
                    <SelectItem value="WDP">WDP</SelectItem>
                    <SelectItem value="TW">TW</SelectItem>
                    <SelectItem value="TMP">TMP</SelectItem>
                    <SelectItem value="Unaudited">Unaudited</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500 bg-gray-50"></div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center bg-gray-50">
              <div class="col-span-4 p-2 font-bold bg-white">Total Pendapatan</div>
              <div class="col-span-2 p-2 text-right border-r font-bold bg-white" v-for="year in years"
                :key="'rev' + year">
                {{
                  formatCurrency(financialData[year]?.total_revenue) }}</div>
              <div class="col-span-2 p-2 bg-white"></div>
            </div>

            <!-- Kemandirian Anggaran Section -->
            <div class="p-2 font-bold bg-gray-100/50 text-teal-800 border-b">Kemandirian Anggaran</div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">PAD</div>
              <div class="col-span-2 p-2 text-right bg-gray-100 border-r" v-for="year in years" :key="'pad' + year">{{
                formatCurrency(financialData[year]?.total_pad) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Kemandirian anggaran rendah sekali</div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Pertumbuhan PAD</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'padgr' + year">{{
                formatPercent(financialData[year]?.pad_growth) }}</div>
              <div class="col-span-2 p-2"></div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio PAD per Pendapatan Total (%)</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'padr' + year">{{
                formatPercent(financialData[year]?.pad_ratio) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Rendah dibanding pemerintah daerah di Indonesia
              </div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio Pendapatan Transfer per Pendapatan Total (%)</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'trr' + year">{{
                formatPercent(financialData[year]?.transfer_ratio) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Ketergantungan transfer meningkat</div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio Pendapatan Lain-lain yang Sah per Pendapatan Total (%)</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'otherr' + year">{{
                formatPercent(financialData[year]?.other_legit_ratio) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Ketergantungan pada transfer menurun</div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Volatilitas pertumbuhan PAD (selisih pertumbuhan PAD) (%)</div>
              <div class="col-span-2 p-1 border-r">
                <Input type="number" step="0.01" v-model="form.volatil_pad"
                  class="h-8 border-green-600 bg-green-50 focus-visible:ring-green-600 text-right font-bold" />
              </div>
              <div class="col-span-4 bg-gray-50"></div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Pertumbuhan PAD 2 tahun terakhir stabil</div>
            </div>

            <!-- Kemampuan memperoleh penghasilan -->
            <div class="p-2 font-bold bg-gray-100/50 text-teal-800 border-b">Kemampuan memperoleh penghasilan untuk
              menutupi
              belanja</div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio Surplus/Defisit Operasi per Pendapatan Total (%)</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'opsur' + year">{{
                formatPercent(financialData[year]?.op_surplus_deficit_ratio) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Sedang dibanding pemerintah di Indonesia</div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio Surplus/Defisit Sebelum Pembiayaan (%)</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'sur' + year">{{
                formatPercent(financialData[year]?.surplus_deficit_before_fin) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Defisit</div>
            </div>

            <!-- Efektivitas belanja -->
            <div class="p-2 font-bold bg-gray-100/50 text-teal-800 border-b">Efektivitas belanja</div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio Belanja Modal per Total belanja (%)</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'caps' + year">{{
                formatPercent(financialData[year]?.cap_spending_ratio) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Rata-rata/ Sedang dibanding pemerintah di
                Indonesia
              </div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio Belanja Pegawai per Total Belanja (%)</div>
              <div class="col-span-2 p-2 text-right border-r" v-for="year in years" :key="'emps' + year">{{
                formatPercent(financialData[year]?.emp_spending_ratio) }}</div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Rendah dibanding pemerintah di Indonesia</div>
            </div>

            <!-- Kualitas penyusunan anggaran -->
            <div class="p-2 font-bold bg-gray-100/50 text-teal-800 border-b">Kualitas penyusunan anggaran</div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rata-rata realisasi PAD 3 tahun terakhir (%)</div>
              <div class="col-span-2 p-1 border-r">
                <Input type="number" step="0.01" v-model="form.pad_last_three_year"
                  class="h-8 border-orange-300 bg-orange-100 text-right font-bold" />
              </div>
              <div class="col-span-4"></div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Sesuai anggaran</div>
            </div>

            <!-- Beban Utang -->
            <div class="p-2 font-bold bg-gray-100/50 text-teal-800 border-b">Beban Utang</div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Total utang</div>
              <div class="col-span-2 p-1 border-r bg-gray-200/50">
                <div class="col-span-2 p-2 text-right border-r bg-gray-50 font-bold">{{ form.total_debt ?
                  formatCurrency(form.total_debt) : '0' }}</div>
              </div>
              <div class="col-span-4 bg-gray-50"></div>
              <div class="col-span-2 p-2 bg-gray-50"></div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio total utang per PDRB harga berlaku (%)</div>
              <div class="col-span-2 p-2 text-right border-r bg-gray-50 font-bold">{{ latestPDRB ?
                formatPercent((form.total_debt / latestPDRB) * 0.0001) : '0.00%' }}</div>
              <div class="col-span-4 bg-gray-50"></div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Rata-rata/ Sedang</div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Rasio total utang per pendapatan umum (%)</div>
              <div class="col-span-2 p-2 text-right border-r bg-gray-50 font-bold">{{
                props.debtService?.unappropiated_revenue ?
                  formatPercent((form.total_debt / props.debtService.unappropiated_revenue) * 100) : '0.00%' }}</div>
              <div class="col-span-4 bg-gray-50"></div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Rata-rata/ Sedang</div>
            </div>

            <!-- Likuiditas -->
            <div class="p-2 font-bold bg-gray-100/50 text-teal-800 border-b">Likuiditas (sepanjang tenor pinjaman)</div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">Debt Service / Pendapatan total (%)</div>
              <div class="col-span-2 p-2 text-right border-r bg-gray-50 font-bold">{{ formatPercent(dsRevenue) }}</div>
              <div class="col-span-4 bg-gray-50"></div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">Rendah</div>
            </div>

            <div class="grid grid-cols-12 divide-x items-center">
              <div class="col-span-4 p-2">DSCR</div>
              <div class="col-span-2 p-2 text-right border-r bg-gray-50 font-bold">{{ debtService?.dscr ?
                debtService.dscr.toFixed(2) + 'x' : '0.00x' }}</div>
              <div class="col-span-4 bg-gray-50"></div>
              <div class="col-span-2 p-2 text-xs italic text-gray-500">>2,5x (Memenuhi)</div>
            </div>

          </div>
        </form>
      </div>

      <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-6 -mx-6 -mb-6">
        <Link href="/assessments">
          <Button type="button" variant="outline" class="flex items-center gap-2">
            <ChevronLeft :size="16" /> Back
          </Button>
        </Link>
        <div v-if="can('create-assessments')">
          <Button @click="submit" :disabled="form.processing"
            class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
            <Save :size="16" /> Simpan dan Lanjutkan
          </Button>
        </div>
      </div>

    </div>
  </div>
</template>
