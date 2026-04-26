<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Rocket, Plus, ChevronLeft, Save } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { Textarea } from '@/components/ui/textarea';
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
import { Input } from '@/components/ui/input';
import {can} from '@/lib/can';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { HelpCircle } from "@lucide/vue";

const props = defineProps({
  assessment: Object as () => any,
  govName: String,
  year: Number,
  budgetPlan: Object as () => any,
  financing: Object as () => any,
  debtService: Object as () => any,
});

const bp = props.budgetPlan || {};
const ds = props.debtService || {};
const fn = props.financing || {};

const form = useForm({
  advantage: ds.advantage || '',
  challenge: ds.challenge || '',
  dscr: null as number | null,
  limit: null as number | null,
  max_loan: null as number | null,
  loan_withdrawal_plus_os: null as number | null,
  unappropiated_revenue: null as number | null,
});

const formatCurrency = (value: number) => {
  if (!value) return "0";
  return new Intl.NumberFormat('id-ID').format(value);
};

// Calculations as Computed Properties
const bpVal = (key: string) => bp[key] || 0;

// PAD
const p_air_tanah_10 = computed(() => bpVal('underground_water_tax'));
const p_pbjt_tl_10 = computed(() => bpVal('street_lighting_tax') + bpVal('electricity_tax'));
const p_opsen_pkb_10 = computed(() => bpVal('opsen_vehicle_tax'));
const p_blud = computed(() => bpVal('blud_revenue'));
const p_rokok_50 = computed(() => (bpVal('cigarette_tax') - bpVal('shared_cigarette_tax')) * 0.5);
const p_pkb_10 = computed(() => (bpVal('vehicle_tax') - bpVal('shared_vehicle_tax')) * 0.1);

const padEarmarked = computed(() => {
  return p_air_tanah_10.value + p_pbjt_tl_10.value + p_opsen_pkb_10.value + p_blud.value + p_rokok_50.value + p_pkb_10.value;
});

const padNonEarmarked = computed(() => bpVal('self_revenue') - padEarmarked.value);

// DAU
const dau_total = 603765164000; // Constant value from template
const dau_pendidikan = computed(() => bpVal('general_allocation_fund_education'));
const dau_kesehatan = computed(() => bpVal('general_allocation_fund_health'));
const dau_pu = computed(() => bpVal('general_allocation_fund_public_work'));
const dau_p3k = computed(() => bpVal('p3k_allowance'));
const dau_kelurahan = computed(() => bpVal('general_allocation_fund_district'));

const dauEarmarked = computed(() => {
  return dau_pendidikan.value + dau_kesehatan.value + dau_pu.value + dau_p3k.value + dau_kelurahan.value;
});

const dauNonEarmarked = computed(() => dau_total - dauEarmarked.value);

// DBH
const dbh_cht = computed(() => bpVal('profit_sharing_fund_cigarette'));
const dbh_sawit = computed(() => bpVal('profit_sharing_fund_sawit'));
const dbh_reboisasi = computed(() => bpVal('profit_sharing_fund_reboisation'));
const dbh_otsus = computed(() => bpVal('add_profit_sharing_fund_oli_gas_otsus'));

const dbhEarmarked = computed(() => {
  return dbh_cht.value + dbh_sawit.value + dbh_reboisasi.value + dbh_otsus.value;
});

const dbhNonEarmarked = computed(() => bpVal('profit_sharing_fund') - dbhEarmarked.value);

// OTSUS
const otsus = computed(() => bpVal('special_autonomy'));

// Transfer Antar Daerah
const tad_pkb_10 = computed(() => bpVal('10_percent_shared_vehicle_tax'));
const tad_rokok_50 = computed(() => bpVal('50_percent_shared_cigarette_tax'));

const tadEarmarked = computed(() => tad_pkb_10.value + tad_rokok_50.value);
const tadNonEarmarked = computed(() => bpVal('inter_regional_transfer_revenue') - tadEarmarked.value);

// Lain-lain
const ll_hibah = computed(() => bpVal('central_gov_grant'));
const ll_jkn = computed(() => bpVal('national_health_revenue'));

const llEarmarked = computed(() => ll_hibah.value + ll_jkn.value);
const llNonEarmarked = computed(() => bpVal('other_revenue') - llEarmarked.value);

// Pengurangs
const pengurang_bagi_hasil = computed(() => bpVal('sharing_fund_spending'));
const pengurang_jasa_layanan = computed(() => bpVal('availability_payment') || 0);
const pengurang_dana_desa = computed(() => bpVal('village_fund_allocation'));

// Net Belanja Pegawai
const bg_p3k = computed(() => bpVal('p3k_allowance'));
const bg_tamsil = computed(() => bpVal('teacher_non_certification_allowance'));
const bg_tpg = computed(() => bpVal('teacher_certification_allowance'));
const bg_tkg = computed(() => bpVal('regional_teacher_additional_allowance'));

const bgEarmarked = computed(() => {
  return bg_p3k.value + bg_tamsil.value + bg_tpg.value + bg_tkg.value;
});

const netBelanjaPegawai = computed(() => bpVal('employee_spending') - bgEarmarked.value);

// PEMBILANG DSCR
const pembilangDscr = computed(() => {
  return padNonEarmarked.value + dauNonEarmarked.value + dbhNonEarmarked.value + otsus.value + 
         tadNonEarmarked.value + llNonEarmarked.value - 
         pengurang_bagi_hasil.value - pengurang_jasa_layanan.value - pengurang_dana_desa.value - 
         netBelanjaPegawai.value;
});

// PENYEBUT DSCR
const pb_pokok = computed(() => ds.avg_annual_return || 0);
const pb_bunga = computed(() => ds.avg_annual_interest || 0);
const pb_biaya = computed(() => ds.avg_annual_cost || 0);
const pe_smi = computed(() => fn.ds_exist || 0);

const penyebutDscr = computed(() => {
  return pb_pokok.value + pb_bunga.value + pb_biaya.value + pe_smi.value;
});

const dscrRatio = computed(() => penyebutDscr.value > 0 ? (pembilangDscr.value / penyebutDscr.value) : 0);

// Summary Calculation
const pendapatanTidakDitentukanPenggunaan = computed(() => netBelanjaPegawai.value + pembilangDscr.value);
const maxPinjaman75 = computed(() => pendapatanTidakDitentukanPenggunaan.value * 0.75);
const pinjamanDitarik = computed(() => ds.plafond || 0);
const outstandingSmi = computed(() => fn.lender === 'SMI' ? (fn.os_debt || 0) : 0);
const outstandingLainnya = computed(() => fn.lender !== 'SMI' ? (fn.os_debt || 0) : 0);
const totalPinjaman = computed(() => pinjamanDitarik.value + outstandingSmi.value + outstandingLainnya.value);
const pctTotalTerhadapPendapatan = computed(() => pendapatanTidakDitentukanPenggunaan.value > 0 ? (totalPinjaman.value / pendapatanTidakDitentukanPenggunaan.value) * 100 : 0);

const submit = () => {
  form.dscr = dscrRatio.value;
  form.limit = pctTotalTerhadapPendapatan.value;
  form.max_loan = maxPinjaman75.value;
  form.loan_withdrawal_plus_os = totalPinjaman.value;
  form.unappropiated_revenue = pendapatanTidakDitentukanPenggunaan.value;
  console.log('Submitting form with data:', form.data());
  form.put(`/assessment-details/${props.assessment.id}/dscr`, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Update successful');
    },
    onError: (errors) => {
      console.error('Update failed with errors:', errors);
    }
  });
};

const isModalOpen = ref(false);

const newYearForm = ref({
    year: (props.year || new Date().getFullYear()) + 1,
    self_revenue: 0,
    underground_water_tax: 0,
    street_lighting_tax: 0,
    electricity_tax: 0,
    opsen_vehicle_tax: 0,
    blud_revenue: 0,
    cigarette_tax: 0,
    shared_cigarette_tax: 0,
    vehicle_tax: 0,
    shared_vehicle_tax: 0,
    general_allocation_fund: 0,
    general_allocation_fund_education: 0,
    general_allocation_fund_health: 0,
    general_allocation_fund_public_work: 0,
    p3k_allowance: 0,
    general_allocation_fund_district: 0,
    profit_sharing_fund: 0,
    profit_sharing_fund_cigarette: 0,
    profit_sharing_fund_sawit: 0,
    profit_sharing_fund_reboisation: 0,
    add_profit_sharing_fund_oli_gas_otsus: 0,
    special_autonomy: 0,
    inter_regional_transfer_revenue: 0,
    '10_percent_shared_vehicle_tax': 0,
    '50_percent_shared_cigarette_tax': 0,
    other_revenue: 0,
    central_gov_grant: 0,
    national_health_revenue: 0,
    sharing_fund_spending: 0,
    village_fund_allocation: 0,
    employee_spending: 0,
    teacher_non_certification_allowance: 0,
    teacher_certification_allowance: 0,
    regional_teacher_additional_allowance: 0,
});

const modalGroups = [
  {
    title: 'Tahun / Informasi Umum',
    items: [
      { key: 'year', label: 'Tahun Target' },
    ]
  },
  {
    title: 'Pendapatan Asli Daerah (PAD)',
    items: [
      { key: 'self_revenue', label: 'PAD Total' },
      { key: 'underground_water_tax', label: 'Pajak Air Tanah' },
      { key: 'street_lighting_tax', label: 'Pajak Penerangan Jalan (PBJT TL)' },
      { key: 'electricity_tax', label: 'Pajak Listrik (jika dipisah)' },
      { key: 'opsen_vehicle_tax', label: 'Opsen PKB' },
      { key: 'blud_revenue', label: 'Pendapatan BLUD' },
      { key: 'cigarette_tax', label: 'Pajak Rokok' },
      { key: 'shared_cigarette_tax', label: 'Bagi Hasil Pajak Rokok' },
      { key: 'vehicle_tax', label: 'PKB' },
      { key: 'shared_vehicle_tax', label: 'Bagi Hasil PKB' },
    ]
  },
  {
    title: 'Dana Alokasi Umum (DAU)',
    items: [
      { key: 'general_allocation_fund', label: 'DAU Total' },
      { key: 'general_allocation_fund_education', label: 'DAU - Pendidikan' },
      { key: 'general_allocation_fund_health', label: 'DAU - Kesehatan' },
      { key: 'general_allocation_fund_public_work', label: 'DAU - Pekerjaan Umum' },
      { key: 'general_allocation_fund_district', label: 'DAU - Kelurahan' },
    ]
  },
  {
    title: 'Dana Bagi Hasil (DBH)',
    items: [
      { key: 'profit_sharing_fund', label: 'DBH Total' },
      { key: 'profit_sharing_fund_cigarette', label: 'DBH CHT' },
      { key: 'profit_sharing_fund_sawit', label: 'DBH Sawit' },
      { key: 'profit_sharing_fund_reboisation', label: 'DBH Dana Reboisasi' },
      { key: 'add_profit_sharing_fund_oli_gas_otsus', label: 'Tambahan DBH Migas Otsus' },
    ]
  },
  {
    title: 'Otsus, Transfer Antar Daerah & Lain-Lain',
    items: [
      { key: 'special_autonomy', label: 'Otsus Total' },
      { key: 'inter_regional_transfer_revenue', label: 'Pendapatan TAD Total' },
      { key: '10_percent_shared_vehicle_tax', label: '10% dari Pend. Bagi Hasil PKB' },
      { key: '50_percent_shared_cigarette_tax', label: '50% dari Pend. Bagi Hasil Rokok' },
      { key: 'other_revenue', label: 'Lain-Lain Pendapatan Sah Total' },
      { key: 'central_gov_grant', label: 'Hibah Pemerintah Pusat' },
      { key: 'national_health_revenue', label: 'Pendapatan Dana Kapitasi JKN' },
    ]
  },
  {
    title: 'Pengurang & Belanja Pegawai',
    items: [
      { key: 'sharing_fund_spending', label: 'Belanja Bagi Hasil' },
      { key: 'village_fund_allocation', label: 'Alokasi Dana Desa' },
      { key: 'employee_spending', label: 'Belanja Pegawai Total' },
      { key: 'p3k_allowance', label: 'DAU - P3K / Tunjangan P3K' },
      { key: 'teacher_non_certification_allowance', label: 'DAK Tamsil' },
      { key: 'teacher_certification_allowance', label: 'DAK TPG' },
      { key: 'regional_teacher_additional_allowance', label: 'DAK TKG' },
    ]
  }
];

const saveNewBudgetPlan = () => {
    router.put(`/assessment-details/${props.assessment.id}/dscr`, {
        budgetPlan: newYearForm.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

</script>

<template>

  <Head title="Edit DSCR" />

  <Alert class="m-2 bg-gray-100" v-if="$page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ $page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <AssessmentStepper :assessment-id="assessment.id" :current-step="7" />

  <div class="p-6 bg-gray-50 min-h-screen font-sans text-sm">
    <div class="max-w-6xl mx-auto">
      <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
        
        <div class="bg-teal-600 text-white py-4 text-center shadow-inner">
          <div class="flex items-center justify-center gap-2">
            <h1 class="text-2xl font-bold">4b_Debt Service Coverage Ratio (DSCR)</h1>
            <HoverCard>
              <HoverCardTrigger>
                <HelpCircle :size="20" class="text-white" />
              </HoverCardTrigger>
              <HoverCardContent>
                Pada DSCR (Debt Service Coverage Ratio), Saudara diminta untuk memberikan data perencanaan pinjaman mulai
                dari plafon pinjaman, tenor pinjaman, tanggal pencairan pertama serta rate atas pinjaman, diakhiri dengan
                kesimpulan atas perhitungan Debt Service Coverage Ratio (DSCR), tantangan dan rencana aksi sebagai hasil
                akhir tahap ini.
              </HoverCardContent>
            </HoverCard>
          </div>
          <p class="font-medium inline-block text-teal-100 mt-1">DSCR</p>
        </div>

        <div class="p-6">
          <div class="flex justify-between items-center mb-4 px-1 font-bold text-base border-b pb-2">
      <div>Perhitungan DSCR APBD {{ year }}</div>
      <div class="flex items-center gap-4">
        <div>{{ govName }}</div>
        
        <Dialog v-model:open="isModalOpen">
            <DialogTrigger as-child v-if="can('create-assessments')">
                <Button class="bg-teal-600 hover:bg-teal-700 text-white flex items-center gap-2 h-8 text-xs">
                    <Plus :size="14" /> Tambah Data Budget Plan
                </Button>
            </DialogTrigger>
            <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col p-0">
                <DialogHeader class="p-6 pb-0">
                    <DialogTitle>Tambah Data Budget Plan Terbaru</DialogTitle>
                    <DialogDescription>
                        Masukkan data APBD terbaru untuk digunakan pada kalkulasi DSCR saat ini.
                    </DialogDescription>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <div v-for="group in modalGroups" :key="group.title" class="space-y-3">
                        <h3 class="font-bold border-b pb-2 text-teal-700">{{ group.title }}</h3>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                            <div v-for="item in group.items" :key="item.key" class="space-y-1">
                                <Label class="text-xs">{{ item.label }}</Label>
                                <Input type="number" step="0.01" v-model="newYearForm[item.key as keyof typeof newYearForm]" class="h-8 shadow-sm" />
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="p-6 pt-2 bg-gray-50 border-t">
                    <Button variant="outline" @click="isModalOpen = false">Batal</Button>
                    <Button @click="saveNewBudgetPlan" class="bg-teal-600 hover:bg-teal-700 text-white">Simpan Data Baru</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
      </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto shadow border text-sm mb-6 pb-2">
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="border p-2 text-left w-1/2">Nama Akun</th>
            <th class="border p-2 text-right">APBD {{ year }}</th>
            <th class="border p-2 text-left">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <!-- PAD -->
          <tr class="font-semibold"><td class="border p-2">PAD</td><td class="border p-2 text-right">{{ formatCurrency(bpVal('self_revenue')) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Unsur Earmarked sebagai Pengurang:</td><td class="border p-2 text-right">{{ formatCurrency(padEarmarked) }}</td><td class="border p-2"></td></tr>
          <tr><td class="border p-2 pl-6">10% dari Pajak Air Tanah</td><td class="border p-2 text-right">{{ formatCurrency(p_air_tanah_10) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota; Minimal 10% dari Pajak Air Tanah</td></tr>
          <tr><td class="border p-2 pl-6">10% dari PBJT TL atau Pajak Penerangan Jalan</td><td class="border p-2 text-right">{{ formatCurrency(p_pbjt_tl_10) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota; Minimal 10% dari PBJT TL atau PPJ</td></tr>
          <tr><td class="border p-2 pl-6">10% dari Opsen PKB</td><td class="border p-2 text-right">{{ formatCurrency(p_opsen_pkb_10) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota; Minimal 10% dari Opsen PKB</td></tr>
          <tr><td class="border p-2 pl-6">Pendapatan BLUD</td><td class="border p-2 text-right">{{ formatCurrency(p_blud) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">50% dari (Pajak Rokok - Bagi Hasil Pajak Rokok)</td><td class="border p-2 text-right">{{ formatCurrency(p_rokok_50) }}</td><td class="border p-2 text-xs italic text-gray-500">Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">10% dari (PKB - Bagi Hasil PKB)</td><td class="border p-2 text-right">{{ formatCurrency(p_pkb_10) }}</td><td class="border p-2 text-xs italic text-gray-500">Provinsi</td></tr>
          <tr class="font-bold bg-blue-50"><td class="border p-2 pl-4 text-blue-900">+ PAD Non Earmarked</td><td class="border p-2 text-right">{{ formatCurrency(padNonEarmarked) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>

          <!-- DAU -->
          <tr class="font-semibold"><td class="border p-2 mt-2">DAU</td><td class="border p-2 text-right">{{ formatCurrency(dau_total) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Unsur Earmarked sebagai Pengurang:</td><td class="border p-2 text-right">{{ formatCurrency(dauEarmarked) }}</td><td class="border p-2"></td></tr>
          <tr><td class="border p-2 pl-6">DAU - Pendidikan</td><td class="border p-2 text-right">{{ formatCurrency(dau_pendidikan) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAU - Kesehatan</td><td class="border p-2 text-right">{{ formatCurrency(dau_kesehatan) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAU - Pekerjaan Umum</td><td class="border p-2 text-right">{{ formatCurrency(dau_pu) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAU - P3K</td><td class="border p-2 text-right">{{ formatCurrency(dau_p3k) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAU - Kelurahan</td><td class="border p-2 text-right">{{ formatCurrency(dau_kelurahan) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr class="font-bold bg-blue-50"><td class="border p-2 pl-4 text-blue-900">+ DAU Non Earmarked</td><td class="border p-2 text-right">{{ formatCurrency(dauNonEarmarked) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>

          <!-- DBH -->
          <tr class="font-semibold"><td class="border p-2 mt-2">DBH</td><td class="border p-2 text-right">{{ formatCurrency(bpVal('profit_sharing_fund')) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Unsur Earmarked sebagai Pengurang:</td><td class="border p-2 text-right">{{ formatCurrency(dbhEarmarked) }}</td><td class="border p-2"></td></tr>
          <tr><td class="border p-2 pl-6">DBH CHT</td><td class="border p-2 text-right">{{ formatCurrency(dbh_cht) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DBH Sawit</td><td class="border p-2 text-right">{{ formatCurrency(dbh_sawit) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DBH Dana Reboisasi</td><td class="border p-2 text-right">{{ formatCurrency(dbh_reboisasi) }}</td><td class="border p-2 text-xs italic text-gray-500">Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Tambahan DBH Migas dalam rangka Otsus</td><td class="border p-2 text-right">{{ formatCurrency(dbh_otsus) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr class="font-bold bg-blue-50"><td class="border p-2 pl-4 text-blue-900">+ DBH Non Earmarked</td><td class="border p-2 text-right">{{ formatCurrency(dbhNonEarmarked) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>

          <!-- OTSUS -->
          <tr class="font-bold bg-blue-50"><td class="border p-2 pl-4 text-blue-900">+ OTSUS</td><td class="border p-2 text-right">{{ formatCurrency(otsus) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>

          <!-- Transfer Antar Daerah -->
          <tr class="font-semibold"><td class="border p-2 mt-2">Pendapatan Transfer Antar Daerah</td><td class="border p-2 text-right">{{ formatCurrency(bpVal('inter_regional_transfer_revenue')) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota</td></tr>
          <tr><td class="border p-2 pl-6">Unsur Earmarked sebagai Pengurang:</td><td class="border p-2 text-right">{{ formatCurrency(tadEarmarked) }} </td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota</td></tr>
          <tr><td class="border p-2 pl-6">10% dari Pendapatan Bagi Hasil PKB</td><td class="border p-2 text-right">{{ formatCurrency(tad_pkb_10) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota; Minimal 10% dari Pendapatan Bagi Hasil PKB</td></tr>
          <tr><td class="border p-2 pl-6">50% dari Pendapatan Bagi Hasil Pajak Rokok</td><td class="border p-2 text-right">{{ formatCurrency(tad_rokok_50) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota; Minimal 50% dari Pendapatan Bagi Hasil Pajak Rokok</td></tr>
          <tr class="font-bold bg-blue-50"><td class="border p-2 pl-4 text-blue-900">+ Pendapatan Transfer Antar Daerah Non Earmarked</td><td class="border p-2 text-right">{{ formatCurrency(tadNonEarmarked) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota</td></tr>

          <!-- Lain-lain -->
          <tr class="font-semibold"><td class="border p-2 mt-2">Lain-Lain Pendapatan Daerah yang Sah</td><td class="border p-2 text-right">{{ formatCurrency(bpVal('other_revenue')) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Unsur Earmarked sebagai Pengurang:</td><td class="border p-2 text-right">{{ formatCurrency(llEarmarked) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Hibah Pemerintah Pusat</td><td class="border p-2 text-right">{{ formatCurrency(ll_hibah) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Pendapatan Dana Kapitasi JKN</td><td class="border p-2 text-right">{{ formatCurrency(ll_jkn) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr class="font-bold bg-blue-50"><td class="border p-2 pl-4 text-blue-900">+ Lain-Lain Pendapatan yang Sah Non Earmarked</td><td class="border p-2 text-right">{{ formatCurrency(llNonEarmarked) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>

          <!-- Pengurangs -->
          <tr class="font-bold bg-red-50"><td class="border p-2 pl-4 text-red-900">- Belanja Bagi Hasil</td><td class="border p-2 text-right">{{ formatCurrency(pengurang_bagi_hasil) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr class="font-bold bg-red-50"><td class="border p-2 pl-4 text-red-900">- Belanja Jasa Ketersediaan Layanan (Availability Payment)</td><td class="border p-2 text-right">{{ formatCurrency(pengurang_jasa_layanan) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr class="font-bold bg-red-50"><td class="border p-2 pl-4 text-red-900">- Alokasi Dana Desa</td><td class="border p-2 text-right">{{ formatCurrency(pengurang_dana_desa) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota</td></tr>

          <!-- Net Belanja Pegawai -->
          <tr class="font-semibold"><td class="border p-2 mt-2">Belanja Pegawai</td><td class="border p-2 text-right">{{ formatCurrency(bpVal('employee_spending')) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">Belanja Pegawai dari APBN sebagai Pengurang:</td><td class="border p-2 text-right">{{ formatCurrency(bgEarmarked) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAU - P3K</td><td class="border p-2 text-right">{{ formatCurrency(bg_p3k) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAK Tamsil</td><td class="border p-2 text-right">{{ formatCurrency(bg_tamsil) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAK TPG</td><td class="border p-2 text-right">{{ formatCurrency(bg_tpg) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr><td class="border p-2 pl-6">DAK TKG</td><td class="border p-2 text-right">{{ formatCurrency(bg_tkg) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>
          <tr class="font-bold bg-red-50"><td class="border p-2 pl-4 text-red-900">- Net Belanja Pegawai</td><td class="border p-2 text-right">{{ formatCurrency(netBelanjaPegawai) }}</td><td class="border p-2 text-xs italic text-gray-500">Kab/Kota/Provinsi</td></tr>

          <!-- Total Pembilang -->
          <tr class="bg-gray-200 font-bold text-lg"><td class="border p-2 pl-2">Pembilang DSCR</td><td class="border p-2 text-right">{{ formatCurrency(pembilangDscr) }}</td><td class="border p-2 text-xs italic text-gray-500 font-normal">Kab/Kota/Provinsi</td></tr>

          <!-- Penyebut DSCR Calculation -->
          <tr><td colspan="3" class="border p-2 border-b-0 h-4"></td></tr>
          <tr class="font-semibold"><td class="border p-2 italic text-gray-600">Pinjaman Baru:</td><td class="border p-2"></td><td class="border p-2"></td></tr>
          <tr><td class="border p-2 pl-6">Rata-rata Pengembalian Pokok Pinjaman</td><td class="border p-2 text-right">{{ formatCurrency(pb_pokok) }}</td><td class="border p-2"></td></tr>
          <tr><td class="border p-2 pl-6">Rata-rata Pembayaran Bunga</td><td class="border p-2 text-right">{{ formatCurrency(pb_bunga) }}</td><td class="border p-2"></td></tr>
          <tr><td class="border p-2 pl-6">Rata-rata Pembayaran Biaya</td><td class="border p-2 text-right">{{ formatCurrency(pb_biaya) }}</td><td class="border p-2"></td></tr>
          
          <tr class="font-semibold"><td class="border p-2 italic text-gray-600">Pinjaman Eksisting:</td><td class="border p-2"></td><td class="border p-2"></td></tr>
          <tr><td class="border p-2 pl-6">Rata-rata Pokok+Bunga ke PT SMI</td><td class="border p-2 text-right">{{ formatCurrency(pe_smi) }}</td><td class="border p-2"></td></tr>
          <tr class="bg-gray-200 font-bold text-lg"><td class="border p-2 pl-2">Penyebut DSCR</td><td class="border p-2 text-right">{{ formatCurrency(penyebutDscr) }}</td><td class="border p-2"></td></tr>
          
          <!-- Final DSCR -->
          <tr><td colspan="3" class="border p-2 border-b-0 h-4"></td></tr>
          <tr class="bg-teal-100 font-bold text-lg"><td class="border p-2 pl-2">DSCR</td><td class="border p-2 text-right">{{ dscrRatio.toFixed(2) }}</td><td class="border p-2"></td></tr>
        </tbody>
      </table>
    </div>

    <!-- Summary Box -->
    <div class="border shadow p-0 mb-6 bg-white overflow-hidden rounded">
      <table class="w-full text-sm">
        <tbody>
          <tr><td class="border-b p-2 font-medium w-[60%]">Pendapatan yang Tidak ditentukan Penggunaannya</td><td class="border-b p-2 border-l text-right">{{ formatCurrency(pendapatanTidakDitentukanPenggunaan) }}</td><td class="border-b p-2 border-l w-1/4"></td></tr>
          <tr><td class="border-b p-2 font-medium">Jumlah Maksimal Pinjaman (75%)</td><td class="border-b p-2 border-l text-right">{{ formatCurrency(maxPinjaman75) }}</td><td class="border-b p-2 border-l"></td></tr>
          <tr><td class="border-b p-2 font-medium">Pinjaman yang akan ditarik</td><td class="border-b p-2 border-l text-right">{{ formatCurrency(pinjamanDitarik) }}</td><td class="border-b p-2 border-l"></td></tr>
          <tr><td class="border-b p-2 font-medium">Outstanding Pinjaman Eksisting dari LKB dan LKBB lainnya</td><td class="border-b p-2 border-l text-right">{{ formatCurrency(outstandingLainnya) }}</td><td class="border-b p-2 border-l text-xs italic">Jika ada</td></tr>
          <tr><td class="border-b p-2 font-medium">Outstanding Pinjaman Eksisting ke PT SMI</td><td class="border-b p-2 border-l text-right">{{ formatCurrency(outstandingSmi) }}</td><td class="border-b p-2 border-l"></td></tr>
          <tr><td class="border-b p-2 font-medium">Jumlah pinjaman yang akan ditarik + Sisa Pinjaman</td><td class="border-b p-2 border-l text-right">{{ formatCurrency(totalPinjaman) }}</td><td class="border-b p-2 border-l"></td></tr>
          <tr class="bg-gray-100 font-bold"><td class="border-b p-2">% Jumlah Pinjaman yang akan ditarik + Sisa Pinjaman thd Pendapatan yang tidak ditentukan Penggunaannya</td><td class="border-b p-2 border-l text-right">{{ pctTotalTerhadapPendapatan.toFixed(0) }}%</td><td class="border-b p-2 border-l bg-green-200"></td></tr>
        </tbody>
      </table>
    </div>

    <!-- Kesimpulan Input Section -->
    <form @submit.prevent="submit" class="mt-8 border-t pt-6">
      <h2 class="text-lg font-bold mb-4 bg-teal-600 text-white px-3 py-1.5 inline-block rounded">Kesimpulan:</h2>
      
      <div class="space-y-4">
        <div>
          <label class="block font-bold mb-2">Apa saja yang sudah baik dalam perhitungan DSCR dan kemampuan pemda untuk melakukan pinjaman?</label>
          <Textarea v-model="form.advantage" rows="4" class="bg-orange-100 border-orange-300 w-full" placeholder="Masukkan poin-poin yang sudah baik..." />
        </div>

        <div>
          <label class="block font-bold mb-2">Apa saja yang masih menjadi tantangan serta membutuhkan perbaikan?</label>
          <Textarea v-model="form.challenge" rows="4" class="bg-orange-100 border-orange-300 w-full" placeholder="Masukkan tantangan yang dihadapi..." />
        </div>
      </div>

      <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-8 -mx-6 -mb-6">
        <Link href="/assessments">
          <Button type="button" variant="outline" class="flex items-center gap-2">
            <ChevronLeft :size="16" /> Back
          </Button>
        </Link>
        <div v-if="can('create-assessments')">
          <Button type="submit" :disabled="form.processing" class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
            <Save :size="16" /> Simpan Kesimpulan
          </Button>
        </div>
      </div>
    </form>

    </div>
  </div>
</div>
</template>
