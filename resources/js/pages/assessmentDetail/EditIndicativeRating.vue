<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Rocket, Save, ArrowLeft } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { can } from '@/lib/can';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { HelpCircle } from "@lucide/vue";

const props = defineProps({
  assessment: Object as () => any,
  selfAssessment: Object as () => any,
  ratingResult: Object as () => any, // From QuantitativeRatingService
  indicativeRating: String, // Pre-calculated label from backend
  calculationDetails: Object as () => any,
});

const form = useForm({
  economy_condition: props.assessment?.economy_condition?.toFixed(2) || props.ratingResult?.skor_ekonomi?.toFixed(2) || 0,
  financial_condition: props.assessment?.financial_condition?.toFixed(2) || props.ratingResult?.skor_keuangan?.toFixed(2) || 0,
  indicative_rating: props.assessment?.indicative_rating || props.indicativeRating || '',
  final_rating: props.assessment?.final_rating || props.indicativeRating || '',
  selfAssessment: {
    social_politics_disturbance: props.selfAssessment?.social_politics_disturbance || '0',
    social_politics_disturbance_detail: props.selfAssessment?.social_politics_disturbance_detail || '',
    arrears_restructuring: props.selfAssessment?.arrears_restructuring || '0',
    arrears_restructuring_detail: props.selfAssessment?.arrears_restructuring_detail || '',
    cashflow_availability: props.selfAssessment?.cashflow_availability || '0',
    cashflow_availability_detail: props.selfAssessment?.cashflow_availability_detail || '',
    advantage: props.selfAssessment?.advantage || '',
    challenge: props.selfAssessment?.challenge || '',
  }
});

// Automatic calculation for ratings (client side simplified or just use backend result)
// Since the rating logic is complex (in PHP services), we'll stick to the backend's initial value
// but update final_rating if self assessment changes?
// For now, let's keep it manual or synchronized.

watch(() => form.selfAssessment, () => {
  // If any self assessment is 'Ya', maybe downgrade?
  // User didn't specify, so we allow manual adjustment of final_rating for now
  // but default it to indicative_rating if it's the first time.

  // Parse values to numbers to avoid string concatenation
  const soc_dist = Number(form.selfAssessment.social_politics_disturbance || 0) + (props.ratingResult?.peringkat || 0);
  const arr_res = Number(form.selfAssessment.arrears_restructuring || 0) + (props.ratingResult?.peringkat || 0);
  const cash_ava = Number(form.selfAssessment.cashflow_availability || 0) + (props.ratingResult?.peringkat || 0);

  // Use Math.max instead of max
  const basePeringkat = Number(props.ratingResult?.peringkat || 0);
  const qual_score = Math.max(soc_dist, arr_res, cash_ava);
  const final_score = Math.max(qual_score, basePeringkat);

  // Apply downgrade logic using ranges to ensure reactivity for all values
  if (final_score <= 2) {
    form.final_rating = 'Sangat Memadai';
  } else if (final_score <= 4) {
    form.final_rating = 'Memadai';
  } else if (final_score <= 6) {
    form.final_rating = 'Kurang Memadai';
  } else {
    form.final_rating = 'Sangat Kurang Memadai';
  }

}, { deep: true });

const submit = () => {
  form.put(`/assessment-details/${props.assessment.id}/indicative-rating`, {
    preserveScroll: true,
  });
};

const formatNumber = (val: number) => {
  return Number(val).toFixed(2);
}

</script>

<template>

  <Head title="Edit Indikasi Rating Daerah" />

  <Alert class="m-2 bg-gray-100" v-if="$page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ $page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <AssessmentStepper :assessment-id="assessment.id" :current-step="7" />

  <div class="p-6 bg-gray-50 min-h-screen font-sans text-sm">
    <div class="max-w-[1000px] mx-auto">
      <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
        
        <div class="bg-teal-600 text-white py-4 text-center shadow-inner">
          <div class="flex items-center justify-center gap-2">
            <h1 class="text-2xl font-bold">Indikasi Rating Pemda</h1>
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
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-6">

      <!-- Calculation Details Section -->
      <div v-if="calculationDetails" class="space-y-6">
        <!-- Aspek Ekonomi -->
        <div>
          <h2 class="text-lg font-bold bg-[#d4e4f7] px-3 py-1 mb-2">Aspek Ekonomi</h2>
          <table class="w-full border-collapse text-xs">
            <tbody>
              <tr><td colspan="4" class="font-bold py-1">PDRB</td></tr>
              <tr>
                <td class="w-1/2 py-1 pl-4">PDRB HB per Kapita (Ribu Rupiah)</td>
                <td class="w-32"><Input :model-value="formatNumber(calculationDetails.ekonomi.pdrb_per_kapita.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="w-48 px-2 text-orange-600">{{ calculationDetails.ekonomi.pdrb_per_kapita.label }}</td>
              </tr>
              <tr>
                <td class="py-1 pl-4">Tingkat konsentrasi PDRB</td>
                <td><Input :model-value="formatNumber(calculationDetails.ekonomi.konsentrasi_pdrb.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.ekonomi.konsentrasi_pdrb.label }}</td>
              </tr>
              <tr>
                <td class="py-1 pl-4">Pertumbuhan PDRB harga konstan (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.ekonomi.pertumbuhan_pdrb.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2"></td>
              </tr>
              <!-- <tr>
                <td class="py-1 pl-4">PDB Indonesia</td>
                <td><Input :model-value="formatNumber(calculationDetails.ekonomi.pdb_indonesia.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.ekonomi.pdb_indonesia.label }}</td>
              </tr> -->
              <tr><td colspan="4" class="font-bold py-1 pt-4">Tingkat Pengangguran</td></tr>
              <tr>
                <td class="py-1 pl-4">% Tingkat pengangguran</td>
                <td><Input :model-value="formatNumber(calculationDetails.ekonomi.pengangguran.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.ekonomi.pengangguran.label }}</td>
              </tr>
              <tr><td colspan="4" class="font-bold py-1 pt-4">Kualitas Pembangunan Daerah</td></tr>
              <tr>
                <td class="py-1 pl-4">Indeks Pembangunan Manusia</td>
                <td><Input :model-value="formatNumber(calculationDetails.ekonomi.ipm.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.ekonomi.ipm.label }}</td>
              </tr>
              <!-- <tr>
                <td colspan="4" class="font-bold py-1 pt-4">Rata-rata skor terbobot</td>
              </tr> -->
              <!-- <tr>
                <td colspan="4" class="font-bold py-1 pt-4">Kondisi keuangan lain yang tidak terwakili pada parameter rating namun berpengaruh signifikan pada Pemda</td>
              </tr>
              <tr>
                <td class="py-1">
                  <div class="h-6 bg-orange-200 border border-gray-400"></div>
                </td>
                <td class="text-right pr-2">Notching skor</td>
                <td class="px-2"><Input model-value="Tanpa Pengurangan" class="h-6 text-orange-600" readonly /></td>
              </tr> -->
            </tbody>
          </table>
        </div>

        <!-- Aspek Keuangan -->
        <div>
          <h2 class="text-lg font-bold bg-[#d4e4f7] px-3 py-1 mb-2">Aspek Keuangan</h2>
          <table class="w-full border-collapse text-xs">
            <tbody>
              <tr><td colspan="4" class="font-bold py-1">Kemandirian Anggaran</td></tr>
              <tr>
                <td class="w-1/2 py-1 pl-4">Rasio PAD per Pendapatan Total (%)</td>
                <td class="w-32"><Input :model-value="formatNumber(calculationDetails.keuangan.pad_pendapatan.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="w-48 px-2 text-orange-600">{{ calculationDetails.keuangan.pad_pendapatan.label }}</td>
              </tr>
              <tr>
                <td class="py-1 pl-4">Volatilitas pertumbuhan PAD (selisih pertumbuhan PAD) (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.volatilitas_pad.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.volatilitas_pad.label }}</td>
              </tr>
              
              <tr><td colspan="4" class="font-bold py-1 pt-4">Kemampuan memperoleh penghasilan untuk menutupi belanja</td></tr>
              <tr>
                <td class="py-1 pl-4">Rasio Surplus/Defisit Operasi per Pendapatan Total (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.operasi_pendapatan.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.operasi_pendapatan.label }}</td>
              </tr>

              <tr><td colspan="4" class="font-bold py-1 pt-4">Efektivitas belanja</td></tr>
              <tr>
                <td class="py-1 pl-4">Rasio Belanja Modal per Total belanja (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.belanja_modal.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.belanja_modal.label }}</td>
              </tr>
              <tr>
                <td class="py-1 pl-4">Rasio Belanja Pegawai per Total Belanja (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.belanja_pegawai.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.belanja_pegawai.label }}</td>
              </tr>

              <tr><td colspan="4" class="font-bold py-1 pt-4">Kualitas penyusunan anggaran</td></tr>
              <tr>
                <td class="py-1 pl-4">Rata-rata realisasi PAD 3 tahun terakhir (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.pad_tiga_tahun.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.pad_tiga_tahun.label }}</td>
              </tr>

              <tr><td colspan="4" class="font-bold py-1 pt-4">Beban Utang</td></tr>
              <tr><td colspan="4" class="font-bold py-1 pl-4 text-gray-700">Total utang kepada Pemerintah; Lembaga Keuangan (bank dan non-bank); serta obligasi</td></tr>
              <tr>
                <td class="py-1 pl-4">Rasio total utang per PDRB harga berlaku (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.utang_pdrb.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.utang_pdrb.label }}</td>
              </tr>
              <tr>
                <td class="py-1 pl-4">Rasio total utang per pendapatan umum (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.utang_pendapatan.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.utang_pendapatan.label }}</td>
              </tr>

              <tr><td colspan="4" class="font-bold py-1 pt-4">Likuiditas (sepanjang tenor pinjaman)</td></tr>
              <tr>
                <td class="py-1 pl-4">Debt Service / Pendapatan total (%)</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.ds_pendapatan.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.ds_pendapatan.label }}</td>
              </tr>
              <tr>
                <td class="py-1 pl-4">DSCR</td>
                <td><Input :model-value="formatNumber(calculationDetails.keuangan.dscr.value)" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td class="px-2 text-orange-600">{{ calculationDetails.keuangan.dscr.label }}</td>
              </tr>

              <tr><td colspan="4" class="font-bold py-1 pt-4">Kapasitas fiskal</td></tr>
              <tr>
                <td class="py-1 pl-4">Kapasitas Fiskal</td>
                <td colspan="2"><Input :model-value="calculationDetails.keuangan.kapasitas_fiskal.label" class="h-6 text-orange-600" readonly /></td>
              </tr>
              <tr>
                <td class="py-1 pl-4">Tingkat Pemerintahan</td>
                <td colspan="2"><Input :model-value="calculationDetails.keuangan.tingkat_pemerintahan.label" class="h-6 text-orange-600" readonly /></td>
              </tr>

              <tr><td colspan="4" class="font-bold py-1 pt-4">Kualitas pencatatan keuangan</td></tr>
              <tr>
                <td class="py-1 pl-4 text-right pr-4">Syarat minimum 3x WDP atau WTP</td>
                <td colspan="2"><Input :model-value="calculationDetails.keuangan.syarat_minimum_wtp.label" class="h-6 text-orange-600" readonly /></td>
              </tr>
              <!-- <tr>
                <td class="py-1 pl-4 text-right pr-4">Frekuensi WTP</td>
                <td><Input :model-value="calculationDetails.keuangan.frekuensi_wtp.value" class="h-6 text-right text-red-600 font-bold" readonly /></td>
                <td></td>
              </tr> -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Scores Section -->
      <div class="bg-gray-50/50 p-6 border border-gray-200 rounded-lg grid grid-cols-12 gap-4 items-center">
        <label class="col-span-6 font-medium">Kondisi Ekonomi</label>
        <div class="col-span-6">
          <Input type="number" step="0.01" v-model="form.economy_condition" class="bg-gray-50 text-right" readonly />
        </div>

        <label class="col-span-6 font-medium">Kondisi Keuangan dan Manajemen Keuangan</label>
        <div class="col-span-6">
          <Input type="number" step="0.01" v-model="form.financial_condition" class="bg-gray-50 text-right" readonly />
        </div>

        <label class="col-span-6 font-medium">Indikasi Rating</label>
        
        <div class="col-span-6">
          <div class="border px-3 py-2 bg-yellow-100 text-yellow-800 font-bold text-center rounded-sm">
            {{ form.indicative_rating }}
          </div>
        </div>
      </div>

      <!-- Self Assessment Section -->
      <div class="bg-gray-50/50 p-6 border border-gray-200 rounded-lg space-y-4">
        <h2 class="font-bold italic text-teal-800">Self Assessment</h2>

        <!-- Question 1 -->
        <div class="space-y-2">
          <div class="flex justify-between items-start gap-4">
            <span class="flex-1">Gangguan sosio politik yang dapat berdampak buruk pada pemenuhan kewajiban jatuh
              tempo</span>
            <Select v-model="form.selfAssessment.social_politics_disturbance">
              <SelectTrigger class="w-[120px] bg-orange-100 border-orange-200">
                <SelectValue placeholder="Pilih" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Tidak</SelectItem>
                <SelectItem value="1">Berpotensi terjadi dengan dampak tidak signifikan</SelectItem>
                <SelectItem value="2">Terjadi dengan dampak signifikan</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex gap-2 items-center">
            <span class="text-xs text-gray-500 w-32">Penjelasan kondisi</span>
            <Input v-model="form.selfAssessment.social_politics_disturbance_detail"
              class="bg-orange-50 border-orange-200 h-8 text-xs" />
          </div>
        </div>

        <!-- Question 2 -->
        <div class="space-y-2 pt-2 border-t border-dashed">
          <div class="flex justify-between items-start gap-4">
            <span class="flex-1">Riwayat tunggakan dan restrukturisasi pembiayaan di Pemerintah Pusat, PT SMI, dan
              Lembaga Keuangan lainnya</span>
            <Select v-model="form.selfAssessment.arrears_restructuring">
              <SelectTrigger class="w-[120px] bg-orange-100 border-orange-200">
                <SelectValue placeholder="Pilih" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Tidak</SelectItem>
                <SelectItem value="2">Pernah memiliki tunggakan dalam 1 tahun terakhir</SelectItem>
                <SelectItem value="7">Sedang memiliki tunggakan <90 hari</SelectItem>
                    <SelectItem value="8">Sedang memiliki tunggakan >90 hari</SelectItem>
                    <SelectItem value="1">Pernah atau sedang mengalami restrukturisasi</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex gap-2 items-center">
            <span class="text-xs text-gray-500 w-32">Penjelasan kondisi</span>
            <Input v-model="form.selfAssessment.arrears_restructuring_detail"
              class="bg-orange-50 border-orange-200 h-8 text-xs" />
          </div>
        </div>

        <!-- Question 3 -->
        <div class="space-y-2 pt-2 border-t border-dashed">
          <div class="flex justify-between items-start gap-4">
            <span class="flex-1">Keterbatasan kesediaan arus kas untuk pemenuhan kewajiban yang akan jatuh tempo</span>
            <Select v-model="form.selfAssessment.cashflow_availability">
              <SelectTrigger class="w-[120px] bg-orange-100 border-orange-200">
                <SelectValue placeholder="Pilih" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Tidak</SelectItem>
                <SelectItem value="1">Berpotensi terjadi dengan dampak tidak signifikan
                </SelectItem>
                <SelectItem value="2">Terjadi dengan dampak signifikan</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex gap-2 items-center">
            <span class="text-xs text-gray-500 w-32">Penjelasan kondisi</span>
            <Input v-model="form.selfAssessment.cashflow_availability_detail"
              class="bg-orange-50 border-orange-200 h-8 text-xs" />
          </div>
        </div>

        <!-- Final Rating Row -->
        <div class="flex items-center gap-4 pt-6">
          <label class="font-bold">Indikasi Rating Final</label>
          <div class="flex-1 max-w-[200px]">
            <div class="border px-3 py-2 bg-yellow-100 text-yellow-800 font-bold text-center rounded-sm">
              {{ form.final_rating }}
            </div>
          </div>
        </div>
      </div>

      <!-- Conclusion Section -->
      <div class="bg-gray-50/50 p-6 border border-gray-200 rounded-lg space-y-4">
        <div class="bg-teal-600/10 border-l-4 border-teal-600 p-2 font-bold text-teal-800">
          Kesimpulan:
        </div>

        <div class="space-y-1">
          <label class="font-medium">Apa saja yang sudah baik dari aspek ekonomi dan fiskal?</label>
          <Textarea v-model="form.selfAssessment.advantage" class="bg-orange-100/50 border-orange-200 min-h-[80px]" />
        </div>

        <div class="space-y-1">
          <label class="font-medium">Apa saja yang masih menjadi tantangan serta membutuhkan perbaikan dari aspek
            ekonomi dan fiskal?</label>
          <Textarea v-model="form.selfAssessment.challenge" class="bg-orange-100/50 border-orange-200 min-h-[80px]" />
        </div>
      </div>

      <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-8 -mx-6 -mb-6">
        <Link href="/assessments">
          <Button type="button" variant="outline" class="flex items-center gap-2">
            <ArrowLeft :size="16" /> Back
          </Button>
        </Link>
        <div v-if="can('create-assessments')">
          <Button type="submit" :disabled="form.processing" class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
            <Save :size="16" /> Simpan dan Lanjutkan
          </Button>
        </div>
      </div>
    </form>
    </div>
  </div>
</div>
</template>
